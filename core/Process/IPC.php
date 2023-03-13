<?php
/*
 * @Author: cclilshy jingnigg@gmail.com
 * @Date: 2023-02-19 20:58:16
 * @LastEditors: cclilshy jingnigg@gmail.com
 * @Description: My house
 * Copyright (c) 2023 by user email: cclilshy, All Rights Reserved.
 */

namespace core\Process;

use core\File\Fifo;
use core\File\Pipe;

class IPC
{
    public $space;  // 允许在初始化时用户自定义的对象
    private $observer; // 监控函数

    private int $observerProcessId; //监控进程ID
    private string $name;   // IPC名称

    private Fifo $me; // 本进程
    private Fifo $to; // 目标进程
    private Fifo $common; // 公共管道
    private Pipe $lock;

    private function __construct(string $name)
    {
        $this->name = $name;
        // $errHandler = function () {
        //     self::$TreeIPC->call('exit', ['pid' => posix_getpid()]);
        //     exit;
        // };
        // set_error_handler($errHandler, E_ERROR);
        // set_exception_handler($errHandler);
        // pcntl_signal(SIGUSR2, $errHandler);
    }

    /**
     * @param callable    $observer 监视者方法
     * @param             $space    // 自定义暂存空间
     * @param string|null $name     自定义名称
     * @return IPC|false  // 返回IPC信息
     */
    public static function create(callable $observer, $space = null, string $name = null): IPC|false
    {
        $name = $name ?? posix_getpid() . '_' . substr(md5(microtime(true)), 0, 6);
        if (1 && (Fifo::link($name . '_p') || Fifo::link($name . '_s') || Fifo::link($name . '_c')))
            return false;

        $ipc = new self($name);
        $ipc->space = $space;
        $ipc->observer = $observer;
        $ipc->me = Fifo::create($name . '_p');
        $ipc->to = Fifo::create($name . '_s');
        $ipc->common = Fifo::create($name . '_c');
        $ipc->lock = Pipe::create($name);
        if ($ipc->ob()) {
            return $ipc;
        } else {
            $ipc->release();
            return false;
        }
    }

    /**
     * 根据IPC名称连接到监视者
     *
     * @param string $name
     * @param ?int   $timeout
     * @return IPC|false
     */
    public static function link(string $name, ?int $timeout = 0): IPC|false
    {
        $name = $name ?? posix_getpid() . '_' . substr(md5(microtime(true)), 0, 6);
        if (!Fifo::link($name . '_p') || !Fifo::link($name . '_s') || !Fifo::link($name . '_c'))
            return false;

        $ipc = new self($name);

        $ipc->me = Fifo::link($name . '_p', $timeout);
        $ipc->to = Fifo::link($name . '_s', $timeout);
        $ipc->common = Fifo::link($name . '_c', $timeout);
        $ipc->lock = Pipe::link($name);
        return $ipc;
    }

    /**
     * 关闭连接
     */
    public function close(): void
    {
        $this->me->close();
        $this->to->close();
        $this->common->close();
        $this->lock->close();
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->$name;
    }

    /**
     * 通知监视者销毁并自释放空间
     *
     * @return void
     */
    public function stop(): void
    {
        if ($this->call('quit') === 'quit') {
            $this->close();
        }
    }

    // 事实上管道的安全,应该由监视者自己维护,而不应该由调用者维护
    // 消费者是服务态,调用者只需要考虑调用,不应考虑其他问题
    // 但是,由于管道的特殊性,调用者需要考虑管道的安全性

    /**
     * 通过此方法可以调用监视者
     * 该进程会堵塞直到监视者返回结果,并返回结果
     * 该进程如果等不到结果会被强制杀死
     *
     * @return mixed
     */
    public function call(): mixed
    {

        // 克隆管道
        $lock = $this->lock->clone();
        // 锁定管道
        $lock->lock();

        var_dump(func_get_args());

        // 序列化请求参数
        $context = serialize(func_get_args());
        $contextLen = strlen($context);
        // 将校验长度加入报文头
        $context = pack('L', strlen($context)) . $context;

        // 发送报文
        $this->common->write($context);

        // 发送报文长度
        $this->to->write($contextLen . PHP_EOL);

        // 读取返回结果长度
        $length = intval($this->me->fgets());
        if ($length === '') {
            $lock->unlock();
            return false;
        }

        // full context
        if (!$fullContext = $this->fullContext($length)) {
            $result = false;
        } else {
            $result = unserialize($fullContext);
        }
        var_dump($result);
        $lock->unlock();
        return $result;
    }

    /**
     * 开始监视进程
     *
     * @return int
     */
    private function ob(): int
    {
        switch ($pid = pcntl_fork()) {
            case 0:
                set_error_handler(function ($errno, $errstr, $errfile, $errline) {
                    echo 'Err(' . $errno . ')File ' . $errfile . ' (' . $errline . ') :' . $errstr . PHP_EOL;
                    $this->common->write(serialize(false));
                    $this->to->write(strlen(serialize(false)) . PHP_EOL);
                    $this->listenr();
                    return;
                }, E_ALL);
                $this->listenr();
                break;
        }
        $this->observerProcessId = $pid;
        return $pid;
    }

    /**
     * 开始监听
     *
     * @return void
     */
    private function listenr(): void
    {
        $this->me = Fifo::link($this->name . '_s');
        $this->to = Fifo::link($this->name . '_p');
        $this->common = Fifo::link($this->name . '_c');
        while ($length = $this->me->fgets()) {
            if (!$fullContext = $this->fullContext($length)) {
                $result = false;
            } else {
                $arguments = unserialize($fullContext);
                $arguments[] = $this;

                if (isset($arguments[0]) && $arguments[0] === 'quit') {
                    $result = 'quit';
                } else {
                    $result = call_user_func_array($this->observer, $arguments);
                }
                $context = serialize($result);
                // 将校验长度加入报文头
                $context = pack('L', strlen($context)) . $context;
                $contextLen = strlen($context);
                // 发送报文
                $this->common->write($context);
                // 发送报文长度
                $this->to->write($contextLen . PHP_EOL);
                if ((isset($arguments[0]) && $arguments[0] === 'quit') || $result === 'quit') {
                    usleep(1000);
                    $this->release();
                    exit;
                }
            }
        }
    }

    /**
     * 获取完整上下文
     *
     * @param int $length  寻找数据长度
     * @param int $residue 渣数据长度
     * @return string | false
     * @throws \Exception
     */
    private function fullContext(int $length, ?int $residue = 0): string|false
    {
        var_dump($length, $residue);
        $this->common->setBlocking(false);
        if ($residue > 0) {
            $this->common->read($residue);
        }

        $_residue = $this->common->read(4);
        if ($_residue === '') {
            $this->common->setBlocking(true);
            return false;
        }
        if (!$_residue = unpack('L', $_residue)[1]) {
            throw new \Exception("报文发生了不可预知的错误!", 1);
        } else {
            // $_residue = hex2bin($_residue);
            if ($_residue !== $length) {
                return $this->fullContext($length, $_residue);
            } else {
                $this->common->setBlocking(true);
                return $this->common->read($_residue);
            }
        }
    }

    /**
     * 关闭连接并删除管道
     *
     * @return void
     */
    private function release(): void
    {
        $this->close();
        $this->me->release();
        $this->to->release();
        $this->common->release();
        $this->lock->release();
    }
}

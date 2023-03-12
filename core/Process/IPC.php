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
     * @return IPC|false
     */
    public static function link(string $name): IPC|false
    {
        $name = $name ?? posix_getpid() . '_' . substr(md5(microtime(true)), 0, 6);
        if (!Fifo::link($name . '_p') || !Fifo::link($name . '_s') || !Fifo::link($name . '_c'))
            return false;

        $ipc = new self($name);
        $ipc->me = Fifo::link($name . '_p');
        $ipc->to = Fifo::link($name . '_s');
        $ipc->common = Fifo::link($name . '_c');
        $ipc->lock = Pipe::link($name);
        return $ipc;
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
            $arguments = unserialize($this->common->read($length));
            if (isset($arguments[0]) && $arguments[0] === 'quit') {
                $this->common->write(serialize('quit'));
                $this->to->write(strlen(serialize('quit')) . PHP_EOL);
                $this->close();
                exit;
            }
            $arguments[] = $this;
            $result = call_user_func_array($this->observer, $arguments);
            $context = serialize($result);
            $this->common->write($context);
            $this->to->write(strlen($context) . PHP_EOL);
            if ($result === 'quit') {
                sleep(1);
                $this->release();
                exit;
            }
        }
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
            $this->release();
        }
    }

    /**
     * 通过此方法可以调用监视者
     *
     * @return mixed
     */
    public function call(): mixed
    {
        $lock = $this->lock->clone();
        $lock->lock();
        $context = serialize(func_get_args());
        $this->common->write($context);
        $this->to->write(strlen($context) . PHP_EOL);
        $length = $this->me->fgets();
        $context = unserialize($this->common->read($length));
        $lock->unlock();
        return $context;
    }
}

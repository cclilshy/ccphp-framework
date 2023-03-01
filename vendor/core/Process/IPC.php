<?php
/*
 * @Author: cclilshy jingnigg@gmail.com
 * @Date: 2023-02-19 20:58:16
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: cclilshy, All Rights Reserved.
 */

namespace core\Process;

use core\Fifo;
use core\Pipe;

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
    }

    public static function create(callable $observer, $space = null, string $name = null): IPC|false
    {
        $name = $name ?? posix_getpid() . '_' . substr(md5(microtime(true)), 0, 6);
        if (1 && (Fifo::link($name . '_p')
                || Fifo::link($name . '_s')
                || Fifo::link($name . '_c')))
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

    public static function link(string $name): IPC|false
    {
        $name = $name ?? posix_getpid() . '_' . substr(md5(microtime(true)), 0, 6);
        if (
            !Fifo::link($name . '_p')
            || !Fifo::link($name . '_s')
            || !Fifo::link($name . '_c')
        )
            return false;

        $ipc = new self($name);
        $ipc->me = Fifo::link($name . '_p');
        $ipc->to = Fifo::link($name . '_s');
        $ipc->common = Fifo::link($name . '_c');
        $ipc->lock = Pipe::link($name);
        return $ipc;
    }

    private function ob(): int
    {
        switch ($pid = pcntl_fork()) {
            case 0:
                set_error_handler(function ($errno, $errstr, $errfile, $errline) {
                    echo 'Err(' . $errno . ')File ' . $errfile . ' (' . $errline . ') :' . $errstr . PHP_EOL;
                    $this->common->write(serialize(false));
                    $this->to->write(strlen(serialize(false)) . PHP_EOL);
                    $this->listenr();
                }, E_ALL);
                $this->listenr();
                break;
            default:
                $this->observerProcessId = $pid;
                return $pid;
        }
    }

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

    public function close(): void
    {
        $this->me->close();
        $this->to->close();
        $this->common->close();
        $this->lock->close();
    }

    private function release(): void
    {
        $this->close();
        $this->me->release();
        $this->to->release();
        $this->common->release();
        $this->lock->release();
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function stop(): void
    {
        if ($this->call('quit') === 'quit') {
            $this->release();
        }
    }

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

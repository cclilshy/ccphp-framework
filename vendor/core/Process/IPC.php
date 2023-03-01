<?php
/*
 * @Author: cclilshy jingnigg@gmail.com
 * @Date: 2023-02-19 20:58:16
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: cclilshy, All Rights Reserved.
 */

namespace core\Process;

use core\Pipe;

class IPC
{
    public $object;  // 允许在初始化时用户自定义的对象
    private int $observerProcessId; //监控进程ID
    private string $name;   // IPC名称
    private string $fifoFile;
    private string $lockFilePath; // 锁文件
    private $me; // 本进程
    private $to; // 目标进程
    private $common; // 公共管道
    private $observer; // 监控函数
    private Pipe $lock;

    private function __construct(string $name)
    {
        $this->name = $name;
        $this->fifoFile = CACHE_PATH . '/pipe/ipc_fifo_' . $name;
        $this->common = fopen($this->fifoFile . '_c.pipe', 'r+');
        $this->lock = Pipe::link($name);
    }

    public static function create(callable $observer,  $object = null, string $name = null): IPC|false
    {
        if (!$name) {
            $name = posix_getpid() . '_' . substr(md5(microtime(true)), 0, 6);
        }
        $path =  CACHE_PATH . '/pipe/ipc_fifo_' . $name;
        if ($pipe = Pipe::link($name)) {
            $pipe->close();
            return false;
        }
        if (file_exists($path . '_p.pipe')) return false;
        if (file_exists($path . '_s.pipe')) return false;
        if (file_exists($path . '_c.pipe')) return false;

        Pipe::create($name);
        posix_mkfifo($path . '_p.pipe', 0600);
        posix_mkfifo($path . '_s.pipe', 0600);
        posix_mkfifo($path . '_c.pipe', 0600);
        $o = new self($name);
        if ($object) $o->object = $object;
        $o->observer = $observer;
        if ($o->ob()) {
            return $o;
        } else {
            return false;
        }
    }

    private function ob(): int
    {
        switch ($pid = pcntl_fork()) {
            case 0:
                $this->me = fopen($this->fifoFile . '_s.pipe', 'r+');
                $this->to = fopen($this->fifoFile . '_p.pipe', 'r+');
                while ($arguments = $this->fullText()) {
                    $arguments = unserialize($arguments);
                    if (isset($arguments[0]) && $arguments[0] === 'quit') {
                        fwrite($this->common, serialize('quit'));
                        fwrite($this->to, strlen(serialize('quit')) . PHP_EOL);
                        break;
                    }
                    array_push($arguments, $this);
                    $result = call_user_func_array($this->observer, $arguments);
                    $context = serialize($result);
                    fwrite($this->common, $context);
                    fwrite($this->to, strlen($context) . PHP_EOL);
                    if ($result === 'quit') {
                        sleep(1);
                        $this->release();
                        exit;
                    }
                }
                $this->close();
                exit;
            default:
                $this->observerProcessId = $pid;
                $this->initStream();
                return $pid;
        }
    }

    private function initStream(): void
    {
        $this->me = fopen($this->fifoFile . '_p.pipe', 'r+');
        $this->to = fopen($this->fifoFile . '_s.pipe', 'r+');
    }

    public static function link(string $name): IPC|false
    {
        $path = CACHE_PATH . '/pipe/ipc_fifo_' . $name;
        if (!$pipe = Pipe::link($name)) return false;
        if (!file_exists($path . '_p.pipe')) return false;
        if (!file_exists($path . '_s.pipe')) return false;
        if (!file_exists($path . '_c.pipe')) return false;
        $pipe->close();
        $o = new self($name);
        $o->initStream();
        return $o;
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function call(): mixed
    {
        $lock = $this->lock->clone();
        $lock->lock();
        $context = serialize(func_get_args());
        $result = $this->send($context);
        $result = unserialize($result);
        $lock->unlock();
        return $result;
    }

    public function fullText(): string
    {
        $length = intval(fgets($this->me));
        $result = '';
        while ($length > 0) {
            if ($length > 8192) {
                $result .= fread($this->common, 8192);
                $length -= 8192;
            } else {
                $result .= fread($this->common, $length);
                $length = 0;
            }
        }
        return $result;
    }

    private function send(string $context): string
    {
        fwrite($this->common, $context);
        fwrite($this->to, strlen($context) . PHP_EOL);
        $result = $this->fullText();
        return $result;
    }

    private function release(): void
    {
        $this->close();
        unlink($this->fifoFile . '_p.pipe');
        unlink($this->fifoFile . '_s.pipe');
        unlink($this->fifoFile . '_c.pipe');
        $this->lock->release();
    }

    public function stop(): void
    {
        if ($this->call('quit') === 'quit') {
            $this->release();
        }
    }

    public function close(): void
    {
        fclose($this->me);
        fclose($this->to);
        fclose($this->common);
        $this->lock->close();
    }
}

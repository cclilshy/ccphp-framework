<?php
/*
 * @Author: cclilshy jingnigg@gmail.com
 * @Date: 2023-02-19 20:58:16
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: cclilshy, All Rights Reserved.
 */

namespace core\Process;

class IPC
{
    public object $object;  // 允许在初始化时用户自定义的对象
    private int $observerProcessId; //监控进程ID
    private string $name;   // IPC名称
    private string $lockFilePath; // 锁文件
    private $me; // 本进程
    private $to; // 目标进程
    private $common; // 公共管道
    private $observer; // 监控函数

    private function __construct(string $name)
    {
        $this->name = $name;
        $this->lockFilePath = $name . '_l.pipe';
        $this->common = fopen($this->name . '_c.pipe', 'r+');
    }

    public static function create(callable $observer, object $object = null, string $name = null): IPC|false
    {
        $name = $name ?? CACHE_PATH . '/pipe/ipc_fifo_' . posix_getpid() . '_' . substr(md5(microtime(true)), 0, 6);
        if (file_exists($name . '_l.pipe')) return false;
        if (file_exists($name . '_p.pipe')) return false;
        if (file_exists($name . '_s.pipe')) return false;
        if (file_exists($name . '_c.pipe')) return false;

        touch($name . '_l.pipe', 0600);
        posix_mkfifo($name . '_p.pipe', 0600);
        posix_mkfifo($name . '_s.pipe', 0600);
        posix_mkfifo($name . '_c.pipe', 0600);
        $o = new self($name);
        if ($object) $o->object = $object;
        $o->observer = $observer;;
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
                $this->me = fopen($this->name . '_s.pipe', 'r+');
                $this->to = fopen($this->name . '_p.pipe', 'r+');
                while (fread($this->me, 1)) {
                    $work = fgets($this->common);
                    if ($work === 'quit' . PHP_EOL) {
                        fwrite($this->common, 'quit' . PHP_EOL);
                        fwrite($this->to, 1);
                        fclose($this->me);
                        fclose($this->to);
                        fclose($this->common);
                        exit;
                    } else {
                        $work = unserialize($work);
                        array_unshift($work, $this);
                        $context = call_user_func_array($this->observer, $work);
                        fwrite($this->common, serialize($context) . PHP_EOL);
                        fwrite($this->to, 1);
                        if ($context === 'quit') {
                            exit;
                        }
                    }
                }
                exit;
            default:
                $this->observerProcessId = $pid;
                $this->initStream();
                return $pid;
        }
    }

    private function initStream(): void
    {
        $this->me = fopen($this->name . '_p.pipe', 'r+');
        $this->to = fopen($this->name . '_s.pipe', 'r+');
    }

    public static function link(string $name): IPC|false
    {
        if (!file_exists($name . '_l.pipe')) return false;
        if (!file_exists($name . '_p.pipe')) return false;
        if (!file_exists($name . '_s.pipe')) return false;
        if (!file_exists($name . '_c.pipe')) return false;
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
        $f = fopen($this->lockFilePath, 'r+');
        flock($f, LOCK_EX);
        fwrite($this->common, serialize(func_get_args()) . PHP_EOL);
        fwrite($this->to, 1);
        fread($this->me, 1);
        $result = unserialize(fgets($this->common));
        flock($f, LOCK_UN);
        fclose($f);
        return $result;
    }

    public function send(string $context): string
    {
        $f = fopen($this->lockFilePath, 'r+');
        flock($f, LOCK_EX);
        fwrite($this->common, $context . PHP_EOL);
        fwrite($this->to, 1);
        fread($this->me, 1);
        $result = fgets($this->common);
        flock($f, LOCK_UN);
        fclose($f);
        return $result;
    }

    public function release(): void
    {
        fwrite($this->common, 'quit' . PHP_EOL);
        fwrite($this->to, 1);
        $this->close();
        unlink($this->name . '_p.pipe');
        unlink($this->name . '_s.pipe');
        unlink($this->name . '_c.pipe');
        unlink($this->name . '_l.pipe');
    }

    public function close(): void
    {
        fclose($this->me);
        fclose($this->to);
        fclose($this->common);
    }

    public function stop()
    {
        fwrite($this->common, serialize(true));
        fwrite($this->to, 1);
        exit;
    }
}
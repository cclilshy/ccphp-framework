<?php
/*
 * @Author: cclilshy jingnigg@gmail.com
 * @Date: 2023-02-19 20:58:16
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: cclilshy, All Rights Reserved.
 */

namespace core\Process;

use JetBrains\PhpStorm\NoReturn;

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
    private $lock;

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

        posix_mkfifo($name . '_l.pipe', 0600);
        posix_mkfifo($name . '_p.pipe', 0600);
        posix_mkfifo($name . '_s.pipe', 0600);
        posix_mkfifo($name . '_c.pipe', 0600);
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
                $this->me = fopen($this->name . '_s.pipe', 'r+');
                $this->to = fopen($this->name . '_p.pipe', 'r+');
                while ($length = intval(fgets($this->me))) {
                    $work = '';
                    while ($length > 0) {
                        if ($length > 8192) {
                            $work .= fread($this->common, 8192);
                            $length -= 8192;
                        } else {
                            $work .= fread($this->common, $length);
                            $length = 0;
                        }
                    }
                    if ($work === 'quit') {
                        fwrite($this->common, 'quit');
                        fwrite($this->to, 4 . PHP_EOL);
                        fclose($this->me);
                        fclose($this->to);
                        fclose($this->common);
                        exit;
                    } else {
                        $work = unserialize($work);
                        array_unshift($work, $this);
                        $result = call_user_func_array($this->observer, $work);
                        $context = serialize($result);
                        fwrite($this->common, $context);
                        fwrite($this->to,  strlen($context) . PHP_EOL);
                        if ($result === 'quit') {
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
        $this->lock = fopen($this->lockFilePath, 'r+');
        fwrite($this->lock, 1);
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
        fread($this->lock,1);
        echo '';
        $context =  serialize(func_get_args());
        fwrite($this->common, $context);
        fwrite($this->to, strlen($context) . PHP_EOL);

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
        $result = unserialize($result);
        fwrite($this->lock, 1);
        return $result;
    }

    public function send(string $context): string
    {
        $f = fopen($this->lockFilePath, 'r+');
        flock($f, LOCK_EX);
        fwrite($this->common, $context);
        fwrite($this->to, strlen($context) . PHP_EOL);

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

    #[NoReturn] public function stop(): void
    {
        fwrite($this->common, serialize(true));
        fwrite($this->to, 1);
        exit;
    }
}

<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-06 16:13:41
 * @LastEditors: cclilshy jingnigg@163.com
 * @FilePath: /ccphp/vendor/core/Pipe.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace core;

// 管道类

class Pipe
{
    public string $fullFilePath;
    public $resource;
    public $name;

    public function __construct($name)
    {
        $this->name = $name;
        $this->fullFilePath = CACHE_PATH . FS . 'pipe' . FS . $name . '.pipe';
        if (!file_exists($this->fullFilePath)) {
            touch($this->fullFilePath);
        }
        $this->resource = fopen($this->fullFilePath, 'r+');
    }

    public static function register($name): Pipe | false
    {
        $fullFilePath = CACHE_PATH . FS . 'pipe' . FS . $name . '.pipe';
        if (file_exists($fullFilePath)) {
            return false;
        } else {
            return new self($name);
        }
    }

    public static function load($name): Pipe | false
    {
        $fullFilePath = CACHE_PATH . FS . 'pipe' . FS . $name . '.pipe';
        if (!file_exists($fullFilePath)) {
            return false;
        } else {
            return new self($name);
        }
    }

    public static function exists($name): bool
    {
        return file_exists(CACHE_PATH . FS . 'pipe' . FS . $name . '.pipe');
    }

    public function flush(): void
    {
        ftruncate($this->resource, 0);
    }

    public function insert($string, int $start = 0): false|int
    {
        $this->lock(true);
        fseek($this->resource, $start);
        $this->unlock();
        return fwrite($this->resource, $string);
    }

    public function lock($wait = false): bool
    {
        return flock($this->resource, $wait ? (LOCK_EX) : (LOCK_EX | LOCK_NB));
    }

    public function unlock(): bool
    {
        return flock($this->resource, LOCK_UN);
    }

    public function read(int $start = 0, int $length = 0): false|string
    {
        $this->lock(true);
        if ($length === 0) {
            return file_get_contents($this->fullFilePath);
        }

        fseek($this->resource, $start);
        $this->unlock();
        return fread($this->resource, $length);
    }

    public function release(): void
    {
        $this->unlock();
        fclose($this->resource);
        file_exists($this->fullFilePath) && unlink($this->fullFilePath);
    }

    public function listen(callable $handler): int
    {
        return -1;
    }
}

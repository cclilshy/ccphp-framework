<?php
/*
 * @Author: cclilshy cclilshy@163.com
 * @Date: 2023-03-02 00:17:47
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: cclilshy, All Rights Reserved.
 */

namespace core;

class Fifo
{
    private $stream;
    private string $name;
    private string $path;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->path = CACHE_PATH . '/pipe/fifo_' . $name . '.fifo';
        $this->stream = fopen($this->path, 'r+');
    }

    public static function create(string $name): Fifo|false
    {
        $path = CACHE_PATH . '/pipe/fifo_' . $name;
        if (file_exists($path . '.fifo')) {
            return false;
        } elseif (posix_mkfifo($path . '.fifo', 0666)) {
            return new self($name);
        } else {
            return false;
        }
    }

    public static function link(string $name): Fifo|false
    {
        $path = CACHE_PATH . '/pipe/fifo_' . $name;
        if (!!file_exists($path . '.fifo')) {
            return new self($name);
        } else {
            return false;
        }
    }

    public function write(string $context): int
    {
        return fwrite($this->stream, $context);
    }

    public function fgets(): string
    {
        return fgets($this->stream);
    }

    public function read(int $length): string
    {
        return fread($this->stream, $length);
    }

    public function full(): string
    {
        return stream_get_contents($this->stream);
    }

    public function release(): void
    {
        $this->close();
        if (file_exists($this->path)) {
            unlink($this->path);
        }
    }

    public function close(): void
    {
        if (get_resource_type($this->stream) !== 'Unknown') {
            fclose($this->stream);
        }
    }
}

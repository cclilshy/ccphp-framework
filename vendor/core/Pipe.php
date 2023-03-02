<?php

/**
 * @Author: cclilshy
 * @Date:   2023-02-28 16:51:36
 * @Last Modified by:   cclilshy
 * @Last Modified time: 2023-02-28 17:30:12
 */

namespace core;

class Pipe
{
    private $resource;
    private int $point;
    private int $eof;
    private string $name;
    private string $path;

    private function __construct($name, $eof = -1)
    {
        $this->name = $name;
        $this->path = CACHE_PATH . '/pipe/' . $name . '.pipe';
        $this->resource = fopen($this->path, 'r+');
        $this->point = 0;
        $this->eof = $eof;
    }

    public static function create(string $name): Pipe|false
    {
        if (!file_exists(CACHE_PATH . '/pipe/' . $name . '.pipe')) {
            touch(CACHE_PATH . '/pipe/' . $name . '.pipe', 0666);
            return new Pipe($name);
        }
        return false;
    }

    public static function link(string $name): Pipe|false
    {
        if (file_exists(CACHE_PATH . '/pipe/' . $name . '.pipe')) {
            return new Pipe($name, filesize(CACHE_PATH . '/pipe/' . $name . '.pipe'));
        }
        return false;
    }

    public function write(string $content, int $start = 0): int|false
    {
        if (strlen($content) < 1) {
            return false;
        }

        if ($start === 0) {
            $this->flush();
        }
        $this->adjustPoint($start);
        $this->eof += strlen($content) - $start;
        return fwrite($this->resource, $content);
    }

    private function flush(): void
    {
        ftruncate($this->resource, 0);
        $this->eof = -1;
        $this->adjustPoint(0);
    }

    private function adjustPoint(int $location): void
    {
        $this->point = $location;
        fseek($this->resource, $this->point);
    }

    public function push(string $content): int
    {
        $this->adjustPoint($this->eof);
        $this->eof += strlen($content);
        fwrite($this->resource, $content);
        return $this->eof;
    }

    public function read(): string|false
    {
        return $this->section(0);
    }

    public function section(int $start, int $end = 0): string|false
    {
        if ($end === 0) {
            $end = $this->eof - $start;
        }

        if ($end > $this->eof || $end < $start) {
            return false;
        }

        $this->adjustPoint($start);
        $length = $end - $start + 1;
        $context = '';

        while ($length > 0) {
            if ($length > 8192) {
                $context .= fread($this->resource, 8192);
                $length -= 8192;
            } else {
                $context .= fread($this->resource, $length);
                $length = 0;
            }
        }

        return $context;
    }

    public function lock($wait = true): bool
    {
        if ($wait) {
            return flock($this->resource, LOCK_EX);
        } else {
            return flock($this->resource, LOCK_EX | LOCK_NB);
        }
    }

    public function unlock(): bool
    {
        return flock($this->resource, LOCK_UN);
    }

    public function clone(): Pipe
    {
        return new self($this->name, $this->eof);
    }

    public function close(): void
    {
        fclose($this->resource);
    }

    public function release(): void
    {
        if (!get_resource_type($this->resource) == 'Unknown') {
            fclose($this->resource);
        }

        unlink($this->path);
    }
}
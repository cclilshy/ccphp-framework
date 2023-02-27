<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2023-02-17 22:07:05
 * @LastEditors: cclilshy jingnigg@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: cclilshy, All Rights Reserved.
 */

namespace core;

class Observer
{
    public string $name;
    public Pipe $pipe;
    public $handle;
    public int $count = 0;

    private function __construct(string $pipeName, callable $handle, bool $register = null)
    {
        $this->name = $pipeName;
        $this->handle = $handle;
        if ($register) {
            $this->pipe = Pipe::register($pipeName);
            $this->pipe->lock();
        } else {
            $this->pipe = Pipe::load($pipeName);
        }
    }

    public function lock(bool $wait = false): bool
    {
        return $this->pipe->lock($wait);
    }

    public static function notice(Pipe $pipe, string $info): void
    {
        $pipe->insert($info);
        $pipe->unlock();
    }

    public function insert(string $info): bool|int
    {
        return $this->pipe->insert($info);
    }

    public static function listener(string $pipeName, callable $handle): Observer
    {
        Console::pdebug('ob listener :' . $pipeName);
        return new self($pipeName, $handle);
    }

    public static function director(string $pipeName): Observer
    {
        return new self($pipeName, function () {
        }, true);
    }

    public function go(): bool
    {
        return $this->pipe->unlock();
    }

    public function changeName(string $name): void
    {
        $this->name = $name;
        $this->pipe = Pipe::register($name);
    }

    public function ob(): void
    {
        while (true) {
            Console::pdebug('ob wait command :' . $this->name);
            if ($pipe = Pipe::load($this->name)) {
                if (!$command = $pipe->read()) {
                    sleep(1);
                    continue;
                }
                $this->count++;
                $pipe->release();
                Console::pdebug('ob recv command :' . $this->name . ' ' . $command);
                call_user_func($this->handle, $command, $this);

            } else {
                sleep(10);
                Console::pdebug('ob not found :' . $this->name . '');
            }
        }
    }

    public function release(): void
    {
        $this->pipe->release();
    }
}
<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2023-02-14 10:29:00
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: cclilshy, All Rights Reserved.
 */

namespace core\Server;

use core\File\Pipe;
use core\Server\interface\Server as ServerInterface;

// 支持查询任意服务信息,包括未注册的服务信息

class Server implements ServerInterface
{
    public string     $name;
    public bool       $status;
    public int        $pid;
    public Pipe|false $pipe;
    public array      $data;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param string|null $name
     * @return \core\Server\Server|false
     */
    public static function load(string|null $name = ''): self|false
    {
        if (!$name) {
            $name = str_replace('/', '_', debug_backtrace()[0]['file']);
        }
        $server = new self($name);
        return $server->initLoad();
    }

    /**
     * @return false|$this
     */
    public function initLoad(): self|false
    {
        if ($this->pipe = Pipe::link($this->name)) {
            if ($server = $this->pipe->read()) {
                if ($server = unserialize($server)) {
                    $this->status = $server->status;
                    $this->pid    = $server->pid;
                    $this->data   = $server->data;
                    return $this;
                } else {
                    $this->pipe->release();
                    return false;
                }
            } else {
                $this->pipe->release();
                return false;
            }
        }
        return false;
    }

    /**
     * @return void
     */
    public function release(): void
    {
        $this->pipe->release();
    }

    /**
     * @param $data
     * @return mixed
     */
    public function info($data = null): mixed
    {
        if ($data === null) {
            return $this->data;
        }
        $this->data = $data;
        $this->record();
        return true;
    }

    // 设置与保存信息

    /**
     * @return void
     */
    public function record(): void
    {
        $this->pipe->write(serialize($this));
    }

    /**
     * @return false|$this
     */
    public function initCreate(): self|false
    {
        if ($this->pipe = Pipe::create($this->name)) {
            $this->status = false;
            $this->pid    = posix_getpid();
            $this->data   = [];
            $this->record();
            return $this;
        }
        return false;
    }

    /**
     * @param string|null $name
     * @return \core\Server\Server|false
     */
    public static function create(string|null $name): self|false
    {
        if (!$name) {
            $name = str_replace('/', '_', debug_backtrace()[0]['file']);
        }
        $server = new self($name);
        return $server->initCreate();
    }
}

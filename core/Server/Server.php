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

// 支持查询任意服务信息,包括未注册的服务信息
class Server
{
    public string $name;
    public bool   $status;
    public int    $pid;
    public Pipe   $pipe;
    public array  $data;

    /**
     * @param string $name
     * @param false  $new
     */
    private function __construct(string $name, bool $new = false)
    {
        if ($new) {
            $this->status = false;
            $this->name   = $name;
            $this->pid    = posix_getpid();
            $this->pipe   = Pipe::create($name);
            $this->data   = [];
            $this->record();
        } else {
            $this->pipe   = Pipe::link($name);
            $server       = $this->pipe->read();
            $server       = unserialize($server);
            $this->status = $server->status;
            $this->name   = $server->name;
            $this->pid    = $server->pid;
            $this->data   = $server->data;
        }
    }

    /**
     * @param ?string $name
     * @return \core\Server\Server|false
     */
    public static function create(?string $name): Server|false
    {
        $name = empty($name) ? str_replace('/', '_', debug_backtrace()[0]['file']) : $name;
        if (Pipe::link($name)) {
            return false;
        } else {
            return new self($name, true);
        }
    }

    // 设置与保存信息

    /**
     * @return void
     */
    private function record(): void
    {
        $this->pipe->write(serialize($this));
    }

    /**
     * @param string $name
     * @return \core\Server\Server|false
     */
    public static function load(string $name = ''): Server|false
    {
        $name = empty($name) ? str_replace('/', '_', debug_backtrace()[0]['file']) : $name;
        if (Pipe::link($name)) {
            return new self($name);
        } else {
            return false;
        }
    }

    //    /**
    //     * @return void
    //     */
    //    public function __destruct()
    //    {
    //        $this->record();
    //    }

    /**
     * @param $data
     * @return array|void
     */
    public function info($data = null)
    {
        if ($data === null) {
            return $this->data;
        }
        $this->data = $data;
        $this->record();
    }

    /**
     * @return void
     */
    public function release(): void
    {
        $this->pipe->release();
    }
}

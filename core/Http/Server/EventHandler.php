<?php
/*
 * @Author: cclilshy jingnigg@gmail.com
 * @Date: 2023-03-12 11:15:47
 * @LastEditors: cclilshy jingnigg@gmail.com
 * @Description: My house
 * Copyright (c) 2023 by user email: jingnigg@gmail.com, All Rights Reserved.
 */

namespace core\Http\Server;

use Exception;
use core\File\Fifo;
use core\Http\Http;
use core\Http\Request;
use core\Process\Process;


class EventHandler
{
    protected Fifo $fifo;
    protected int  $pid;

    /**
     * @throws \Exception
     */
    public function __construct(string $name, int $pid)
    {
        // 套接字
        if ($fifo = Fifo::link($name)) {
            $this->fifo = $fifo;
        } else {
            throw new Exception('管道未创建,无法访问');
        }
        $this->pid = $pid;
    }

    /**
     * @return \core\Http\Server\EventHandler|false
     */
    public static function create(): EventHandler|false
    {
        $name = uniqid('HTTP_EVENT_HANDLER' . mt_rand(1111, 9999) . microtime(true));
        $pid  = Process::fork(function () use ($name) {
            $fifo = Fifo::create($name);
            // 创建客户端套接字
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

            // 连接服务器
            socket_connect($socket, '127.0.0.1', 2787);

            $context = '';
            $info    = [];
            while (true) {
                if (!isset($info[1])) {
                    $symbol = $fifo->read(1);

                    if ($symbol === '#') {
                        if ($context === 'stop') {
                            socket_close($socket);
                            $fifo->release();
                            break;
                        }
                        $info[]  = $context;
                        $context = '';
                        continue;
                    }

                    $context .= $symbol;
                } else {
                    $info[] = $fifo->read(intval($info[1]));
                    //handler
                    $request = json_decode($info[2], true);
                    $result  = Http::build(new Request($request), true)->go('SERVER');

                    $len = strlen($result);
                    socket_write($socket, "@{$info[0]}#{$len}#{$result}");
                    $info    = [];
                    $context = '';
                }
            }
        });
        sleep(1);
        try {
            return new self($name, $pid);
        } catch (Exception $e) {
            echo $e->getMessage() . PHP_EOL;
            return false;
        }
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->$name;
    }

    /**
     * 向管道内推送请求消息
     *
     * @param string $name
     * @param array  $request
     * @return void
     */
    public function push(string $name, array $request): void
    {
        $context = json_encode($request);
        $len     = strlen($context);

        $this->fifo->write("{$name}#{$len}#{$context}");
    }

    /**
     * @return void
     */
    public function close(): void
    {
        $this->fifo->close();
    }
}

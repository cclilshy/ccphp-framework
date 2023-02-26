<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2023-01-05 00:08:14
 * @LastEditors: cclilshy jingnigg@163.com
 * @Description: My house
 * Copyright (c) 2023 by cclilshy email: cclilshy@163.com, All Rights Reserved.
 */

namespace core;

use core\Server\Server;
use core\Server\Socket as Sock;

// 启动socket服务, 拥有独立的进程调度区

class Socket extends Server
{
    public static function start()
    {
        if (Server::register()) {
            $onStart = Config::get('socket.onStart');
            $onConnect = Config::get('socket.onConnect');
            $onMessage = Config::get('socket.onMessage');
            $onClose = Config::get('socket.onClose');
            $c = new Sock($onStart, $onConnect, $onMessage, $onClose);
            $c->listen('tcp://0.0.0.0:9999');
        }else{
            echo " [Socket] start faild \n";
        }
    }

    public static function stop(): bool
    {
        return Server::release();
    }
}
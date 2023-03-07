<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2023-01-30 10:32:18
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by cclilshy email: cclilshy@163.com, All Rights Reserved.
 */

namespace console;

// Start The Service Class Perform Timing Tasks And Socket Services

use core\Config;
use core\Database\Pool;
use core\Process\Tree;

class Server
{
    public static function register(): string
    {
        return "As a service\n";
    }

    public function main($argv, $console): void
    {
        if (count($argv) < 2) {
            $console::printn("Please enter the command \n> master server [start|stop] [-d]\n");
            return;
        }
        if ($argv[1] == 'start') {
            if (Config::get('server.database_pool') === true) {
                Tree::launch();
            }
            Pool::launch();

            // 下面这个用不了
            // HttpServer::launch();
        } elseif ($argv[1] === 'stop') {
            Pool::stop();
            Tree::stop();
        }
    }
}
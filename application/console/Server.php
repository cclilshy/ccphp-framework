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
use core\Master;
use core\Console;
use core\Process\Tree;
use core\Process\Process;
use core\Http\Server\Server as HttpServer;

class Server
{
    public static function register(): string
    {
        return "As a service\n";
    }

    public function main(array $argv, Console $console): void
    {
        if (count($argv) < 2) {
            $console::printn("Please enter the command \n> master server [start|stop] [-d]\n");
            return;
        }
        if ($argv[1] == 'start') {
            Master::rouse('Process\Tree')->launch();
            if (Config::get('server.database_pool') === true) {
                //Pool::launch();//需要先初始化数据库
            }

            $func = function () {
                HttpServer::launch();
            };

            if (isset($argv[2]) && $argv[2] === '-d') {
                Process::initialization();
                Process::fork($func);
                sleep(1);
                Process::guard();
            } else {
                $func();
            }

        } elseif ($argv[1] === 'stop') {
            try {
                HttpServer::stop();
                Master::rouse('Process\Tree')->stop();
            } catch (\Exception $e) {
                echo $e->getMessage() . PHP_EOL;
            }
        }
    }
}
<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2023-02-02 10:48:45
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by cclilshy email: cclilshy@163.com, All Rights Reserved.
 */

namespace core\Server;

use core\Process\Process;
use core\Route;

class Cron
{
    private static $tasks;

    public static function start()
    {
        return Process::fork(function () {
            while (true) {
                foreach (self::$tasks as $name => $task) {
                    if (time() - $task['lasttime'] >= $task['interval']) {
                        self::$tasks[$name]['lasttime'] = time();
                        Process::fork(function () use ($name) {
                            Route::simulation($name, 'cron');
                        });
                    }
                }
                sleep(1);
            }
        });
    }

    public static function stop()
    {
        return Server::release();
    }

    public static function timer($name, $callback, $interval)
    {
        if (is_callable($callback)) {
            self::$tasks[$name] = array(
                'interval' => $interval,
                'lasttime' => 0,
            );
        }
        Route::cron($name, $callback);
    }
}

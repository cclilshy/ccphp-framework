<?php
/*
 * @Author: cclilshy cclilshy@163.com
 * @Date: 2023-03-01 20:17:12
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: cclilshy, All Rights Reserved.
 */

namespace core\Database;

use core\Config;
use core\Console;
use core\DB;
use core\Process\IPC;
use core\Process\ProcessMirroring;
use core\Server\Server;

class Pool
{
    private static IPC $dispatcher;
    private array $flow = array();

    public static function launch(): void
    {
        if ($server = Server::create('DatabasePool')) {
            // 运行调度服务
            self::dispatcher();

            // 一个进程分配一个连接
            for ($i = 0; $i < Config::get('server.database_pool_connect'); $i++) {
                $connectNames = array();
                try {
                    $ipc = IPC::create(function (array $flow, IPC $ipc) {
                        return ProcessMirroring::production($ipc->space, $flow);
                    }, DB::getConnect());

                    if ($ipc === false) throw new \Exception('IPC服务启动失败');

                    self::$dispatcher->call('new', $ipc->name);

                    $connectNames[] = $ipc->name;
                } catch (\Exception $e) {
                    foreach ($connectNames as $name) {
                        IPC::link($name)->stop();
                    }
                    echo $e->getMessage() . PHP_EOL;
                    exit;
                }
            }
            $server->info([
                'dispatcher_name' => self::$dispatcher->name,
                'connect_names' => $connectNames,
            ]);
            Console::pgreen('[Database-Pool-Server] started!');
        } else {
            Console::pred('[Database-Pool-Server] start failed : it\'s start');
        }
    }

    private static function dispatcher(): void
    {
        self::$dispatcher = IPC::create(function ($action, $name, $ipc) {
            Console::pdebug('[Pool][' . \microtime(true) . ']' . $name . '->' . $action);
            switch ($action) {
                case 'new':
                    for ($i = 0; $i < Config::get('server.database_pool_max'); $i++) {
                        $ipc->space[] = $name;
                    }
                    break;
                case 'get':
                    return array_shift($ipc->space);
                case 'back':
                    $ipc->space[] = $name;
                    break;
            }
        }, array());
    }

    public static function get(): ProcessMirroring|false
    {
        if ($server = Server::load('DatabasePool')) {
            $info = $server->info();
            $dispatcherName = $info['dispatcher_name'];
            $dispatcher = IPC::link($dispatcherName);
            if ($connectName = $dispatcher->call('get', null)) {
                $std = new \stdClass;
                $std->dispatcher = $dispatcher;
                $std->connect = IPC::link($connectName);

                return new ProcessMirroring(function ($p) {
                    if (isset($p->flow[0]) && $p->flow[0]['m'] === 'back') {

                        $p->space->dispatcher->call('back', $p->space->connect->name);
                        return;
                    }
                    return $p->space->connect->call($p->flow);
                }, $std);
            }
        }
        return false;
    }

    public static function stop(): void
    {
        if ($server = Server::load('DatabasePool')) {
            $info = $server->info();
            $dispatcherName = $info['dispatcher_name'];
            $connectNames = $info['connect_names'];
            foreach ($connectNames as $connectName) {
                IPC::link($connectName)->stop();
            }
            IPC::link($dispatcherName)->stop();
            $server->release();
            Console::pgreen('[Database-Pool-Server] stoped!');
        } else {
            Console::pred('[Database-Pool-Server] stop failed : it\'s stop');
        }
    }
}
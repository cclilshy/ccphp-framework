<?php
/*
 * @Author: cclilshy cclilshy@163.com
 * @Date: 2023-03-01 20:17:12
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: cclilshy, All Rights Reserved.
 */

namespace core\Database;

use stdClass;
use Exception;
use core\Config;
use core\Console;
use core\Process\IPC;
use core\Server\Server;
use core\Process\ProcessMirroring;
use function microtime;

// 数据库内存常驻的


class Pool
{
    // 调度服务的IPC
    private static IPC $dispatcher;

    /**
     * 启动调度服务器
     *
     * @return void
     * @throws \Exception
     */
    public static function launch(): void
    {
        // 注册服务成功
        if ($server = Server::create('DatabasePool')) {
            // 运行调度服务
            self::dispatcher();

            // 一个进程分配一个连接
            $connectNames = [];

            // 分配指定数量连接，并储存其IPC名称
            for ($i = 0; $i < Config::get('server.database_pool_connect'); $i++) {
                try {
                    // 创建连接处理服务
                    $ipc = IPC::create(function (array $flow, IPC $ipc) {
                        return ProcessMirroring::production($ipc->space, $flow);
                    }, DB::getConnect());

                    if ($ipc === false)
                        throw new Exception('IPC服务启动失败');

                    // 通知调度服务器
                    self::$dispatcher->call('new', $ipc->name);

                    // 主进程记录连接服务IPC名称
                    $connectNames[] = $ipc->name;
                } catch (Exception $e) {

                    // 注销所有连接
                    foreach ($connectNames as $name) {
                        IPC::link($name)->stop();
                    }
                    echo $e->getMessage() . PHP_EOL;
                    exit;
                }
            }

            // 保存所有连接IPC名称
            $server->info(['dispatcher_name' => self::$dispatcher->name, 'connect_names' => $connectNames,]);

            // 输出
            Console::pgreen('[Database-Pool-Server] started!');
        } else {
            Console::pred('[Database-Pool-Server] start failed : it\'s start');
        }
    }

    /**
     * @return void
     */
    private static function dispatcher(): void
    {
        // 创建调度服务
        self::$dispatcher = IPC::create(function ($action, $name, $ipc) {
            // 接收到的消息进行处理
            Console::pdebug('[Pool][' . microtime(true) . ']' . $name . '->' . $action);
            switch ($action) {
                // 新的数据库常驻服务
                case 'new':
                    // 分配指定数量
                    for ($i = 0; $i < Config::get('server.database_pool_max'); $i++) {
                        $ipc->space[] = $name;
                    }
                    break;

                // 获取一个连接数据库IPC，不堵塞但可能返回`null`(没有连接时)
                case 'get':
                    return array_shift($ipc->space);
                case 'back':
                    // 归还一个连接
                    $ipc->space[] = $name;
                    break;
            }
        }, []);
    }

    /**
     * 获取一个连接镜像
     *
     * @return ProcessMirroring|false
     * @throws \Exception
     */
    public static function get(): ProcessMirroring|null
    {
        // 加载数据库连接池服务信息
        if ($server = Server::load('DatabasePool')) {
            $info           = $server->info();
            $dispatcherName = $info['dispatcher_name'];

            // 连接调度服务器
            $dispatcher = IPC::link($dispatcherName);

            // 从调度服务器获取一个数据库连接IPC
            if ($connectName = $dispatcher->call('get', null)) {
                $std             = new stdClass();
                $std->dispatcher = $dispatcher;

                // 连接数据库IPC
                $std->connect = IPC::link($connectName);

                // 返回一个镜像
                return new ProcessMirroring(function ($p) {
                    // 归还连接
                    if (isset($p->flow[0]) && $p->flow[0]['m'] === 'back') {
                        $p->space->dispatcher->call('back', $p->space->connect->name);
                    }
                    // 转发调用栈
                    return $p->space->connect->call($p->flow);
                }, $std);
            }
        }
        return null;
    }

    /**
     * 停止数据库连接池服务
     *
     * @return void
     * @throws \Exception
     */
    public static function stop(): void
    {
        // 加载数据库连接池服务信息
        if ($server = Server::load('DatabasePool')) {
            $info           = $server->info();
            $dispatcherName = $info['dispatcher_name'];
            $connectNames   = $info['connect_names'];

            // 循环关闭数据库连接
            foreach ($connectNames as $connectName) {
                IPC::link($connectName)->stop();
            }

            // 关闭调度服务器
            IPC::link($dispatcherName)->stop();

            // 释放服务信息
            $server->release();

            Console::pgreen('[Database-Pool-Server] stoped!');
        } else {
            Console::pred('[Database-Pool-Server] stop failed : it\'s stop');
        }
    }
}
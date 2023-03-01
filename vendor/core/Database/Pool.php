<?php
/*
 * @Author: cclilshy cclilshy@163.com
 * @Date: 2023-03-01 20:17:12
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: cclilshy, All Rights Reserved.
 */

namespace core\Database;

use Illuminate\Database\Connection;
use core\Process\IPC;
use core\Server\Server;
use core\DB;
use core\Console;

class Pool
{
    private static array $connects = array();
    private array $flow = array();
    private IPC $ipc;

    public static function getConnect(): Connection
    {
        return DB::getConnect();
    }

    public static function launch(int $count = 1): void
    {
        if ($server = Server::create('DatabasePool')) {
            for ($i = 0; $i < $count; $i++) {
                self::$connects[] = self::getConnect();
            }

            $ipc = IPC::create(function (array $flow, IPC $ipc) {
                if ($connect = array_shift($ipc->space)) {
                    $handler = $connect;
                    foreach ($flow as $item) {
                        $handler = call_user_func_array([$handler, $item['m']], $item['a']);
                    }
                    $ipc->space[] = $connect;
                    return $handler;
                }
            }, self::$connects);

            $server->info(['ipc_name' => $ipc->name]);
            Console::pgreen('[Database-Pool-Server] started!');
        } else {
            Console::pred('[Database-Pool-Server] start failed : it\'s start');
        }
    }

    public static function stop()
    {
        if ($server = Server::load('DatabasePool')) {
            $ipcName = $server->info()['ipc_name'];
            $ipc = IPC::link($ipcName);
            $ipc->stop();
            $server->release();
            Console::pgreen('[Database-Pool-Server] stoped!');
        } else {
            Console::pred('[Database-Pool-Server] stop failed : it\'s stop');
        }
    }

    public static function link(): Pool | false
    {
        if ($server = Server::load('DatabasePool')) {
            if ($ipc = IPC::link($server->info()['ipc_name'])) {
                return new self($ipc);
            }
        }

        return false;
    }

    public function go()
    {
        $result =  $this->ipc->call($this->flow);
        $this->flow = array();
        return $result;
    }

    public function unlink(): void
    {
        $this->ipc->close();
    }

    public function __call($name, $arguments)
    {
        $this->flow[] = array('m' => $name, 'a' => $arguments);
        return $this;
    }

    public function __construct(IPC $ipc)
    {
        $this->ipc = $ipc;
    }
}

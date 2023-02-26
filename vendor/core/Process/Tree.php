<?php
/*
 * @Author: cclilshy jingnigg@gmail.com
 * @Date: 2023-02-19 16:23:07
 * @LastEditors: cclilshy jingnigg@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: cclilshy, All Rights Reserved.
 */

namespace core\Process;

use core\Console;
use core\Server\Server;

class Tree
{
    private Node $root;
    public static function launch(): bool
    {
        if ($server = Server::create('Tree')) {
            $handler = function ($fifo, $action, $data) {
                $fifo->object->handler($fifo, $action, $data);
            };
            $ipcName = IPC::create($handler, new self)->name;

            $server->info(['tree_name' => $ipcName]);
            Console::pgreen('[TreeServer] started!');
            return true;
        } else {
            Console::pred('[TreeServer] start faild : it\'s start');
            return false;
        }
    }

    public static function stop(): bool
    {
        if ($server = Server::load('Tree')) {
            $ipcName = $server->data['tree_name'];
            if ($IPC = IPC::link($ipcName)) {
                $IPC->send('quit');
                $server->release();
                Console::pgreen('[TreeServer] stoped!');
                return true;
            }
        }
        Console::pred('[TreeServer] stop faild may not run');
        return false;
    }

    private function __construct()
    {
        $this->root = new Node(0, 0, 'undefined');
    }

    public function handler($fifo, $action, $data)
    {
        Console::pdebug('[MESSAGE] '.json_encode(func_get_args()));
        switch ($action) {
            case 'new':
                return $this->root->new($data['pid'], $data['ppid'], $data['gIPCName']);
            case 'exit':
                return $this->root->exit($data['pid']);
            case 'signal':
                return $this->root->signal($data['pid'], $data['signo']);
            case 'kill':
                return $this->root->kill($data['pid']);
            default:
                return -1;
        }
    }
}

<?php
/*
 * @Author: cclilshy jingnigg@gmail.com
 * @Date: 2023-02-19 16:23:07
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: cclilshy, All Rights Reserved.
 */

namespace core\Process;

use core\Console;
use core\Server\Server;

class Tree
{
    private Node $root;
    private Node $orphanProcess;
    private array $map = array();

    private function __construct()
    {
        $this->root = new Node(0, 0, 'undefined');
        $this->orphanProcess = new Node(1, 0, 'undefined');
    }

    public static function launch(): bool
    {
        if ($server = Server::create('Tree')) {
            $handler = function ($action, $data, $fifo) {
                $fifo->object->handler($fifo, $action, $data);
            };
            $ipcName = IPC::create($handler, new self)->name;

            $server->info(['tree_name' => $ipcName]);
            Console::pgreen('[TreeServer] started!');
            return true;
        } else {
            Console::pred('[TreeServer] start failed : it\'s start');
            return false;
        }
    }

    public function handler($fifo, $action, $data): void
    {
        Console::pdebug('[MESSAGE] ' . json_encode(func_get_args()));
        switch ($action) {
            case 'new':
                if ($node = $this->find($data['ppid'])) {
                    $node->new($data['pid'], $data['ppid'], $data['IPCName']);
                    $this->map[$data['pid']] = array('ppid' => $data['ppid']);
                } else {
                    $this->orphanProcess->new($data['pid'], $data['ppid'], $data['IPCName']);
                    $this->map[$data['pid']] = array('ppid' => 1);
                }
                break;
            case 'exit':
                // 新成员退出，通知守护进程,调整树结构
                $this->exit($data['pid']);
                break;
            case 'signal':
                if ($node = $this->find($data['pid'])) {
                    $node->signal($data['signNo']);
                }
                break;
            case 'kill':
                if ($node = $this->find($data['pid'])) {
                    $this->kill($node);
                }
                break;
            case 'killAll':
                if ($node = $this->find($data['ppid'])) $this->killAll($node);
                break;
            default:
                break;
        }
    }

    private function find($pid): Node|null
    {
        if ($pid === 1) {
            return $this->orphanProcess;
        }
        // 新成员进入，找到指定节点，插入新成员
        $node = $this->root;
        $parentProcessId = $pid;
        $path = array($parentProcessId);
        while ($parentProcessId = $this->map[$parentProcessId]['ppid'] ?? null) {
            if ($parentProcessId === 1) {
                $node = $this->orphanProcess;
                break;
            }
            $path[] = $parentProcessId;
        }
        while ($parentProcessId = array_pop($path)) {
            if (!$node) break;
            $node = $node->get($parentProcessId);
        }
        return $node ?? null;
    }

    /** 处理退出的成员，并重新维护树结构
     * @param $pid
     * @return void
     */
    private function exit($pid): void
    {
        if ($node = $this->find($pid)) {
            // 修改子进程继承
            $childrenNodes = $node->exit();
            foreach ($childrenNodes as $childrenNode) {
                $this->map[$childrenNode->pid]['ppid'] = 1;
                $this->orphanProcess->add($childrenNode->extend(1));
            }

            // 从父节点中释放
            if ($parentNode = $this->find($node->ppid)) {
                $parentNode->remove($node->pid);
            }
            // 释放哈希表
            unset($this->map[$node->pid]);
        }
    }

    private function kill(Node $node): void
    {
        $childrenNodes = $node->kill();
        foreach ($childrenNodes as $childrenNode) {
            $this->map[$childrenNode->pid]['ppid'] = 1;
            $this->orphanProcess->add($childrenNode->extend(1));
        }
    }

    private function killAll(Node $node): void
    {
        foreach ($node->children as $childrenNode) {
            $this->killAll($childrenNode);
        }
        $node->kill();
        unset($this->map[$node->pid]);
    }

    public static function stop(): bool
    {
        if ($server = Server::load('Tree')) {
            $ipcName = $server->data['tree_name'];
            if ($IPC = IPC::link($ipcName)) {
                $IPC->stop();
                $server->release();
                Console::pgreen('[TreeServer] stopped!');
                return true;
            }
        }
        Console::pred('[TreeServer] stop failed may not run');
        return false;
    }
}

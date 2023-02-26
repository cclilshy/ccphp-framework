<?php
/*
 * @Author: cclilshy jingnigg@gmail.com
 * @Date: 2023-02-19 16:23:07
 * @LastEditors: cclilshy jingnigg@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: cclilshy, All Rights Reserved.
 */

namespace core\Process;

// 用于管理进程树
// 任何入口都是从根部顺下
// 任何进程都可以向任何进程发送消息
// 任何进程都可以向任何进程发送信号

class Node
{
    private static array $hashMap = []; // 哈希表索引
    private int $pid; // 进程id
    private int $ppid; // 父进程
    private array $children; // 子进程
    public IPC|false $IPC;  // 进程间通信

    public function __construct(int $pid, int $ppid, string $gIPC)
    {
        $this->pid = $pid;
        $this->ppid = $ppid;
        $this->children = [];
        if ($this->IPC = IPC::link($gIPC)) {
            $this->IPC->call('new', ['pid'=>$pid]);
        }
    }

    public function new(int $pid, int $ppid, string $gIPC): int
    {
        $this->children[$pid] = new self($pid, $ppid, $gIPC);
        if ($pnode = $this->children[$ppid] ?? null) {
            $pnode->children[$pid] = $this->children[$pid];
        }
        return 0;
    }

    public function exit(int $pid): int
    {
        if ($node = $this->children[$pid] ?? null) {
            $node->IPC->call('exit', ['pid' => $pid]);
            if ($pnode = $this->children[$node->ppid] ?? null) {
                unset($pnode->children[$pid]);
            }
            unset($this->children[$pid]);
            return 0;
        }
        return -1;
    }

    public function signal(int $pid, int $signo)
    {
        if ($node = $this->children[$pid]) {
            return $node->IPC->call('signal', ['pid' => $pid, 'signo' => $signo]);
        }
        return -1;
    }

    public function kill(int $pid)
    {
        if ($node = $this->children[$pid] ?? null) {
            $node->IPC->call('signal', ['pid' => $pid, 'signo' => SIGKILL]);
            $this->exit($pid);
        }
        return -1;
    }
}

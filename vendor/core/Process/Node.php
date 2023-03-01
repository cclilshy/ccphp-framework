<?php
/*
 * @Author: cclilshy jingnigg@gmail.com
 * @Date: 2023-02-19 16:23:07
 * @LastEditors: cclilshy cclilshy@163.com
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
    // 哈希表索引
    private int $pid; // 进程id
    private int $ppid; // 父进程
    private array $children; // 子进程
    private string $IPCName;

    /** 创建一个节点，储存其基本信息
     * @param int $pid
     * @param int $ppid
     * @param string $IPCName
     */
    public function __construct(int $pid, int $ppid, string $IPCName)
    {
        $this->pid = $pid;
        $this->ppid = $ppid;
        $this->children = [];
        $this->IPCName = $IPCName;
        $this->call('new', ['pid' => $pid]);
    }

    /** 与IPC建立一次性连接，并发送遗传自定义命令
     * @return mixed
     */
    private function call(): mixed
    {
        if ($ipc = IPC::link($this->IPCName)) {
            $res = call_user_func_array([$ipc, 'call'], func_get_args());
            $ipc->close();
            return $res;
        }
        return false;
    }

    public function __get($name): mixed
    {
        return $this->$name;
    }

    /**
     * @param Node $node
     * @return void
     */
    public function add(Node $node): void
    {
        $this->children[$node->pid] = $node;
    }

    /** 移除一个子成员
     * @param $pid
     * @return bool
     */
    public function remove($pid): bool
    {
        if (isset($this->children[$pid])) {
            unset($this->children[$pid]);
            return true;
        } else {
            return false;
        }
    }

    /** new一个子成员
     * @param int $pid
     * @param int $ppid
     * @param string $IPCName
     * @return void
     */
    public function new(int $pid, int $ppid, string $IPCName): void
    {
        $this->call('new', ['pid' => $this->pid]);
        $this->children[$pid] = new self($pid, $ppid, $IPCName);
    }

    /** 发送指定信号
     * @param int $signNo
     * @return bool
     */
    public function signal(int $signNo): bool
    {
        return $this->call('signal', ['pid' => $this->pid, 'signNo' => $signNo]);
    }

    /** 指定一个继承人
     * @param int $pid
     * @return void
     */
    public function extend(int $pid)
    {
        $this->ppid = $pid;
        return $this;
    }

    /** 明确杀死子进程
     * @return array
     */
    public function kill(): array
    {
        $this->call('signal', ['pid' => $this->pid, 'signNo' => SIGKILL]);
        return $this->exit();  // 该操作可能触发守护进程自动回收，因此最后执行
    }

    /** 将子进程交与调用者处理，并通知守护者自身结束
     * @return array
     */
    public function exit(): array
    {
        $this->call('exit', ['pid' => $this->pid]);
        return $this->children;
    }

    /** 获取一个子节点
     * @param $pid
     * @return void
     */
    public function get($pid)
    {
        return $this->children[$pid] ?? null;
    }
}

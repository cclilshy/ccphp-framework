<?php
/*
 * @Author: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @Date: 2023-02-07 22:54:36
 * @LastEditors: cclilshy jingnigg@gmail.com
 * @Description: My house
 * Copyright (c) 2023 by ${git_name} email: ${git_email}, All Rights Reserved.
 */

namespace core\Process;

use core\Server\Server;

// 进程管理器
class Process
{
    private static IPC $TreeIPC;    //进程树IPC
    private static string $GuardIPCName = '';   //守护者IPC名称
    private static bool $inited = false;    // 是否初始化

    /**
     * 初始化
     *
     * @return void
     */
    public static function initialization(): void
    {
        // 加载进程树信息
        if ($server = Server::load('Tree')) {
            // 连接进程树IPC
            if (self::$TreeIPC = IPC::link($server->data['tree_name'], 3)) {
                self::$inited = true;
                // 主进程加入树根
                self::$TreeIPC->call('new', ['pid' => posix_getpid(), 'ppid' => posix_getppid(), 'IPCName' => 'undefined']);
            }
        }
    }

    /**
     * 创建一个分叉
     *
     * @param callable $handler
     * @param string   $group
     * @return int
     */
    public static function fork(callable $handler, string $group = ''): int
    {
        if (!self::$inited)
            return -1;
        // 创建守护进程
        if (!self::$GuardIPCName)
            self::$GuardIPCName = Guardian::create();

        switch ($pid = pcntl_fork()) {
            case -1:
                return -1;

            case 0:
                $errHandler = function () {
                    self::$TreeIPC->call('exit', ['pid' => posix_getpid()]);
                    exit;
                };
                set_error_handler($errHandler, E_ERROR);
                set_exception_handler($errHandler);
                // pcntl_signal(SIGUSR2, $errHandler);
                // 通知树服务器储存
                self::$TreeIPC->call('new', ['pid' => posix_getpid(), 'ppid' => posix_getppid(), 'IPCName' => self::$GuardIPCName]);
                // 在子节点中重置守护信息
                self::$GuardIPCName = '';
                // 处理主业务
                call_user_func($handler);
                // 通知进程树销毁
                self::$TreeIPC->call('exit', ['pid' => posix_getpid()]);
                exit(0);

            default:
                return $pid;
        }
    }

    /*
     * 向任意进程发送信号
     */
    public static function signal(int $pid, int $signNo): bool
    {
        if (self::$TreeIPC->call('signal', ['pid' => $pid, 'signo' => $signNo]) === 0) {
            return true;
        }
        return false;
    }

    /**
     * 销毁任意进程
     *
     * @param int $pid
     * @return bool
     */
    public static function kill(int $pid): bool
    {
        if (self::$TreeIPC->call('kill', ['pid' => $pid]) === 0) {
            return true;
        }
        return false;
    }

    /**
     * 销毁一整棵树的进程，提供根节点
     *
     * @param int $ppid
     * @return bool
     */
    public static function killAll(int $ppid): bool
    {
        if (self::$TreeIPC->call('killAll', ['ppid' => $ppid]) === 0) {
            return true;
        }
        return false;
    }

    /**
     * 开始守护，当前进程将不再创建子进程
     *
     * @return void
     */
    public static function guard(): void
    {
        if ($guardIPC = IPC::link(self::$GuardIPCName)) {
            echo '关闭成功';

            $guardIPC->call('guard', []);
        } else {
            echo '关闭失败';
        }

        self::$TreeIPC->call('exit', ['pid' => posix_getpid()]);
    }
}

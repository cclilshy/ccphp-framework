<?php
/*
 * @Author: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @Date: 2023-02-07 22:54:36
 * @LastEditors: cclilshy jingnigg@gmail.com
 * @Description: My house
 * Copyright (c) 2023 by ${git_name} email: ${git_email}, All Rights Reserved.
 */

namespace core\Process;

use core\Console;
use core\Server\Server;

// 进程管理器


class Process
{
    private static IPC    $TreeIPC;                 //进程树IPC
    private static string $GuardIPCName = '';       //守护者IPC名称
    private static bool   $inited       = false;    // 是否初始化

    /**
     * 初始化
     *
     * @return bool
     */
    public static function initialization(): bool
    {
        if (!isset(self::$inited) === true)
            return true;
        // 加载进程树信息
        if ($server = Server::load('Tree')) {
            // 连接进程树IPC
            if ($treeIPC = IPC::link($server->data['tree_name'])) {
                // 主进程加入树根
                $treeIPC->call('new', [
                    'pid'     => posix_getpid(),
                    'ppid'    => posix_getppid(),
                    'IPCName' => 'undefined'
                ]);
                self::$TreeIPC = $treeIPC;
                return self::$inited = true;
            }
        }
        return self::$inited = false;
    }

    /**
     * 创建一个子进程
     *
     * @param callable $handler
     * @param ?string  $name
     * @return int
     */
    public static function fork(callable $handler, ?string $name = null): int
    {
        if (!isset(self::$inited) || !self::$inited)
            return -1;

        // 创建守护进程
        if (!isset(self::$GuardIPCName) || self::$GuardIPCName === '') {
            if (!$guardIPCName = Guardian::create()) {
                return -1;
            }
            self::$GuardIPCName = $guardIPCName;
        }

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

                // 通知树服务器储存
                self::$TreeIPC->call('new', [
                    'pid'     => posix_getpid(),
                    'ppid'    => posix_getppid(),
                    'IPCName' => self::$GuardIPCName
                ]);
                // 在子节点中重置守护信息
                self::$inited       = false;
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

    /**
     * @param int $pid
     * @param int $signNo
     * @return bool
     */
    public static function signal(int $pid, int $signNo): bool
    {
        if (!isset(self::$inited) || !self::$inited)
            return -1;
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
        if (!self::$inited)
            return false;
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
        if (!isset(self::$inited) || !self::$inited)
            return -1;
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
        if (!isset(self::$inited) || !self::$inited)
            return;
        if ($guardIPC = IPC::link(self::$GuardIPCName)) {
            Console::pdebug(' 开始守护成功');
            $guardIPC->call('guard', []);
        } else {
            Console::pdebug(' 开始守护失败');
        }

        self::$TreeIPC->call('exit', ['pid' => posix_getpid()]);
    }
}

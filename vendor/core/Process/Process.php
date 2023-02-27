<?php
/*
 * @Author: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @Date: 2023-02-07 22:54:36
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by ${git_name} email: ${git_email}, All Rights Reserved.
 */

namespace core\Process;

use core\Server\Server;

class Process
{
    private static IPC $TreeIPC;
    private static string $GuardIPCName = '';
    private static bool $inited = false;

    public static function init(): void
    {
        if ($server = Server::load('Tree')) {
            if (self::$TreeIPC = IPC::link($server->data['tree_name'])) {
                self::$inited = true;
                self::$TreeIPC->call('new', ['pid' => posix_getpid(), 'ppid' => posix_getppid(), 'IPCName' => 'undefined']);
            }
        }
    }

    public static function fork(callable $handler, string $group = ''): int
    {
        if (!self::$inited) return -1;
        if (!self::$GuardIPCName) self::$GuardIPCName = Guardian::create();

        switch ($pid = pcntl_fork()) {
            case -1:
                return -1;

            case 0:
                self::$TreeIPC->call('new', ['pid' => posix_getpid(), 'ppid' => posix_getppid(), 'IPCName' => self::$GuardIPCName]);
                self::$GuardIPCName = '';
                call_user_func($handler);
                self::$TreeIPC->call('exit', ['pid' => posix_getpid()]);
                exit(0);

            default:
                return $pid;
        }
    }

    public static function signal(int $pid, int $signo): bool
    {
        if (self::$TreeIPC->call('signal', ['pid' => $pid, 'signo' => $signo]) === 0) {
            return true;
        }
        return false;
    }

    public static function kill(int $pid): bool
    {
        if (self::$TreeIPC->call('kill', ['pid' => $pid]) === 0) {
            return true;
        }
        return false;
    }

    public static function killAll(int $ppid): bool
    {
        if (self::$TreeIPC->call('killAll', ['ppid' => $ppid]) === 0) {
            return true;
        }
        return false;
    }

    public static function guard(): void
    {
        IPC::link(self::$GuardIPCName)->call('guard', []);
        self::$TreeIPC->call('exit', ['pid' => posix_getpid()]);
    }
}
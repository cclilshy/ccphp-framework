<?php
/*
 * @Author: cclilshy cclilshy@163.com
 * @Date: 2023-02-26 20:49:41
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: cclilshy, All Rights Reserved.
 */

namespace console;

use core\Process\Process;

class Debug
{
    public static function register(): string
    {
        return 'using devel debug';
    }

    public function main($argv, $console): void
    {
        Process::initialization();

        // 创建100个进程
        for ($i = 0; $i < 100; $i++) {
            Process::fork(function () {
                // 子进程3个进程
                for ($i = 0; $i < 3; $i++) {
                    Process::fork(function () {
                        echo posix_getpid() . PHP_EOL;
                        sleep(1000);//堵死
                    });
                }
                sleep(1000);//堵死
            });

        }

        sleep(5);
        // 按树销毁
        Process::killAll(posix_getpid());
        Process::guard();
    }
}

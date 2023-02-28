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
        Process::init();
        for ($i = 0; $i < 1000; $i++) {
            Process::fork(function () use ($i) {
                echo $i . ',';
                sleep(rand(5, 10));
            });
            usleep(100);
        }

        sleep(3);
        // Process::killAll(posix_getpid());
        Process::guard();
    }
}

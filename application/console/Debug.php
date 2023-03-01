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
use core\Process\IPC;

class Debug
{
    public static function register(): string
    {
        return 'using devel debug';
    }

    public function main($argv, $console): void
    {
        Process::init();
        for ($i=0; $i < 100; $i++) { 
            Process::fork(function() use ($i){
                sleep(1000);
            });
        }
        sleep(1);
        Process::killAll(posix_getpid());
        Process::guard();
    }
}
<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2023-01-30 10:32:18
 * @LastEditors: cclilshy jingnigg@163.com
 * @Description: My house
 * Copyright (c) 2023 by cclilshy email: cclilshy@163.com, All Rights Reserved.
 */

namespace console;

// Start The Service Class Perform Timing Tasks And Socket Services

class Server
{
    public static function register(): string
    {
        return "As a service\n";
    }

    public function main($argv, $console): void
    {
        if (count($argv) < 2) {
            $console::printn("Please enter the command \n> master server [start|stop] [-d]\n");
            return;
        }

        $pid = \core\Process\Process::fork(function(){
            for ($i=0; $i < 10; $i++) { 
                sleep(1);
                echo "1";
            }
        });
        sleep(3);
        \core\Process\Process::kill($pid, SIGKILL);
    }
}

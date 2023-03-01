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
use core\Database\Pool;

class Debug
{
    public static function register(): string
    {
        return 'using devel debug';
    }

    public function main($argv, $console): void
    {
        Process::init();
        
        for($i=0;$i<100;$i++){
            Process::fork(function(){
                if ($link = Pool::link()){
                    for ($i = 0; $i < 10; $i++) {
                        $result = $link->table('area')
                            ->where('id', '=', mt_rand(1049112, 1050111))
                            ->first()
                            ->go();

                        echo json_encode($result);
                    }
                    $link->close();
                }
            });
        }
        sleep(1);
        Process::guard();

    }
}

<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2023-02-26 15:14:18
 * @LastEditors: cclilshy jingnigg@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: cclilshy, All Rights Reserved.
 */

namespace core\Process;

use core\Process\IPC;

class Guardian
{
    public array $processIds = array();

    public static function create()
    {
        $handler = function ($fifo, $action, $data) {
            switch ($action) {
                case 'new':
                    $fifo->object->add($data['pid']);
                    break;
                case 'exit':
                    $fifo->object->remove($data['pid']);
                    break;
                case 'signal':
                    return posix_kill($data['pid'], $data['signo']);
            }
            if (count($fifo->object->processIds) === 0 && posix_getppid() === 1) {
                return 'quit';
            }
        };
        return IPC::create($handler, new self)->name;
    }

    public function add($pid)
    {
        $this->processIds[] = $pid;
    }

    public function remove($pid)
    {
        $key = array_search($pid, $this->processIds);
        if ($key !== false) {
            unset($this->processIds[$key]);
        }
    }
}

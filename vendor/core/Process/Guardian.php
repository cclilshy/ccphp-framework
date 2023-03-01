<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2023-02-26 15:14:18
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: cclilshy, All Rights Reserved.
 */

namespace core\Process;

use core\Console;

class Guardian
{
    public array $processIds = array();
    public bool $guard = false;

    public static function create(): string
    {
        $handler = function ($action, $data, $fifo) {
            Console::pdebug('[Guardian] ' . $action . ':' . json_encode($data));
            switch ($action) {
                case 'new':
                    $fifo->object->add($data['pid']);
                    break;
                case 'exit':
                    $fifo->object->remove($data['pid']);
                    break;
                case 'signal':
                    return posix_kill($data['pid'], $data['signNo']);
                case 'guard':
                    $fifo->object->guard = true;
                    break;
            }
            if (count($fifo->object->processIds) === 0 && (posix_getppid() === 1 || $fifo->object->guard)) {
                return 'quit';
            }
        };
        return IPC::create($handler, new self)->name;
    }

    public function add($pid): void
    {
        $this->processIds[] = $pid;
    }

    public function remove($pid): void
    {
        $key = array_search($pid, $this->processIds);
        if ($key !== false) {
            unset($this->processIds[$key]);
        }
    }
}

<?php
/*
 * @Author: cclilshy cclilshy@163.com
 * @Date: 2023-03-02 21:01:04
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: cclilshy, All Rights Reserved.
 */

namespace core\Process;

class ProcessMirroring
{
    public $func;
    public array $flow = array();
    public object $space;

    public function __construct(callable $func, object $space = null)
    {
        $this->func = $func;
        if ($space !== null) {
            $this->space = $space;
        }
    }

    public static function production(object $main, $flow = null)
    {
        foreach ($flow as $k => $item) {
            $main = call_user_func_array([$main, $item['m']], $item['a']);
        }
        return $main;
    }

    public function __get($name)
    {
        return $this->name;
    }

    public function __call($name, $arguments = array()): ProcessMirroring
    {
        $this->flow[] = array('m' => $name, 'a' => $arguments);
        return $this;
    }

    public function go()
    {
        $result = call_user_func($this->func, $this);
        $this->flow = [];
        return $result;
    }
}
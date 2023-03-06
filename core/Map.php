<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-08 15:37:42
 * @LastEditors: cclilshy cclilshy@163.com
 * @FilePath: /ccphp/vendor/core/Map.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace core;

// Loading layer record all routing information configured by the system

class Map
{
    private object $object;
    private string $action;
    private string $type;
    private $callable;

    public function __construct(string $type,  string $className, string $action, callable $callable = null)
    {
        $this->type = $type;
        if ($type == 'controller') {
            $this->object = new $className();
            $this->action = $action;
        } else {
            $this->callable = $callable;
        }
    }

    public function run()
    {
        if ($this->type == 'controller') {
            return call_user_func_array([$this->object, $this->action], func_get_args());
        } else {
            return call_user_func_array($this->callable, func_get_args());
        }
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->object, $name], $arguments);
    }
}

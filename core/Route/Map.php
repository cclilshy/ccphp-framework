<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-08 15:37:42
 * @LastEditors: cclilshy cclilshy@163.com
 * @FilePath: /ccphp/vendor/core/Map.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace core\Route;

// Loading layer record all routing information configured by the system
// 用于储存路由的导向
class Map
{
    private string $type;
    private string $className;
    private string $action;
    private $callable;

    /**
     * 创建一个导向，支持类::静态函数/类名->方法/匿名函数
     *
     * @param string        $type
     * @param string        $className
     * @param string        $action
     * @param callable|null $callable
     */
    public function __construct(string $type, string $className, string $action, callable $callable = null)
    {
        $this->type = $type;
        if ($type == 'controller') {
            $this->className = $className;
            $this->action = $action;
        } else {
            $this->callable = $callable;
        }
    }

    /**
     * @param ...$vars
     * @return mixed
     */
    public function run(...$vars): mixed
    {
        if ($this->type == 'controller') {
            return call_user_func([new $this->className(...$vars), $this->action], ...$vars);
        } elseif ($this->type === 'static') {
            return call_user_func([$this->className, $this->action], ...$vars);
        } else {
            return call_user_func_array($this->callable, ...$vars);
        }
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->className, $name], $arguments);
    }

    public function __get($name)
    {
        return $this->$name;
    }
}

<?php
/*
 * @Author: cclilshy cclilshy@163.com
 * @Date: 2023-03-05 18:05:56
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: jingnigg@gmail.com, All Rights Reserved.
 */

namespace core\Ccphp;

use core\Ccphp\FlowController;

class FlowBuild
{
    private static FlowBuild $flowBuild;
    private array $modules = array();
    private $flow;
    private $errorHandler;

    public static function init()
    {
        self::$flowBuild = new self;
    }

    public static function load(string $name, $object)
    {
        self::$flowBuild->modules[$name] = $object;
    }

    public static function build(callable $flow, callable $errorHandler): FlowController
    {
        self::$flowBuild->flow = $flow;
        self::$flowBuild->errorHandler = $errorHandler;
        return new FlowController(self::$flowBuild);
    }

    public function __get($name)
    {
        return $this->$name;
    }
}

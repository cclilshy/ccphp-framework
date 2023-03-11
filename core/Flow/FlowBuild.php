<?php
/*
 * @Author: cclilshy cclilshy@163.com
 * @Date: 2023-03-05 18:05:56
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: jingnigg@gmail.com, All Rights Reserved.
 */

namespace core\Flow;

// 流程打包
class FlowBuild
{
    // 模块列表
    private array $modules = array();
    // 流程callable
    private $flow;
    // 流程错误处理
    private $errorHandler;

    public function __construct()
    {

    }

    /**
     * 保存一个对象到流程中
     * @param string $name
     * @param $object
     * @return void
     */
    public function load(string $name, $object): void
    {
        $this->modules[$name] = $object;
    }

    /**
     * 流程打包为流程控制器
     * @param callable $flow
     * @param callable $errorHandler
     * @return FlowController
     */
    public function build(callable $flow, callable $errorHandler): FlowController
    {
        $this->flow = $flow;
        $this->errorHandler = $errorHandler;
        return new FlowController($this);
    }

    /**
     * 访问属性接口
     * @param $name
     * @return mixed
     */
    public function __get($name): callable
    {
        return $this->$name;
    }
}

<?php
/*
 * @Author: cclilshy cclilshy@163.com
 * @Date: 2023-03-05 16:52:32
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: jingnigg@gmail.com, All Rights Reserved.
 */

namespace core\Flow;

// 流程控制器
class FlowController
{
    private FlowBuild $flowBuild;   // 流程模块包
    private array $working = array();   // 数据暂存区

    public function __construct(FlowBuild $flowBuild)
    {
        // 向每个模块执行build方法
        $this->flowBuild = $flowBuild;
        foreach ($this->flowBuild as &$module) {
            call_user_func([$module, 'build'], $this);
        }
    }

    /**
     * 处理主动抛出的错误
     *
     * @param int    $code
     * @param string $msg
     * @param string $file
     * @param int    $line
     * @return void
     */
    public function error(int $code, string $msg, string $file, int $line): void
    {
        call_user_func($this->flowBuild->errorHandler, $code, $msg, $file, $line, $this);
    }

    /**
     * 执行某项任务
     *
     * @param string $module
     * @param string $action
     * @param array  $arguments
     * @param        $result
     * @return $this
     */
    public function handle(string $module, string $action, array $arguments, &$result): FlowController
    {
        $result = call_user_func_array([$this->flowBuild->modules[$module], $action], $arguments);
        return $this;
    }

    /**
     * 按照预定流程执行
     *
     * @return mixed
     */
    public function go()
    {
        $params = func_get_args();
        $params[] = $this;
        return call_user_func_array($this->flowBuild->flow, $params);
    }

    /**
     * 暂存区存放数据
     *
     * @param string $key
     * @param        $value
     * @return void
     */
    public function work(string $key, $value): void
    {
        $this->working[$key] = $value;
    }

    /**
     * 取栈存区数据
     *
     * @param string $key
     * @return mixed|null
     */
    public function working(string $key): mixed
    {
        return $this->working[$key] ?? null;
    }

    /**
     * 访问模块实体接口
     *
     * @param $name
     * @return mixed
     */
    public function __get($name): mixed
    {
        return $this->flowBuild->modules[$name];
    }
}

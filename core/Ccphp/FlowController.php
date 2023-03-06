<?php
/*
 * @Author: cclilshy cclilshy@163.com
 * @Date: 2023-03-05 16:52:32
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: jingnigg@gmail.com, All Rights Reserved.
 */

namespace core\Ccphp;

use core\Ccphp\FlowBuild;

class FlowController
{
    private FlowBuild $flowBuild;

    public function __construct(FlowBuild $flowBuild)
    {
        $this->flowBuild = $flowBuild;

        foreach ($this->flowBuild as &$module) {
            call_user_func([$module, 'build'], $this);
        }
    }

    public function error(int $code, string $msg, string $file, int $line)
    {
        call_user_func($this->flowBuild->errorHandler, $code, $msg, $file, $line, $this);
    }

    public function handle(string $module, string $action, array $arguments, &$result)
    {
        $result = call_user_func_array([$this->flowBuild->modules[$module], $action], $arguments);
        return $this;
    }

    public function go()
    {
        $params = func_get_args();
        $params[] = $this;
        return call_user_func_array($this->flowBuild->flow, $params);
    }

    public function __get($name)
    {
        return $this->flowBuild->modules[$name];
    }
}

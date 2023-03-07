<?php
/*
 * @Author: cclilshy cclilshy@163.com
 * @Date: 2023-03-02 21:01:04
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: cclilshy, All Rights Reserved.
 */

namespace core\Process;

// 进程镜像，用于储存用户自定义调用栈序，可反化加载调用栈
class ProcessMirroring
{
    public $func;
    public array $flow = array();
    public object $space;

    /**
     * @param callable $func
     * @param object|null $space
     */
    public function __construct(callable $func, object $space = null)
    {
        $this->func = $func;
        if ($space !== null) {
            $this->space = $space;
        }
    }

    /**
     * 由指定程序反序列化处理栈序请求
     * @param object $main
     * @param $flow
     * @return mixed|object
     */
    public static function production(object $main, $flow = null): mixed
    {
        foreach ($flow as $k => $item) {
            $main = call_user_func_array([$main, $item['m']], $item['a']);
        }
        return $main;
    }

    /****/
    public function __get($name)
    {
        return $this->name;
    }

    /**
     * 接受任意方法，并将参数入栈
     * @param $name
     * @param $arguments
     * @return $this
     */
    public function __call($name, $arguments): ProcessMirroring
    {
        $this->flow[] = array('m' => $name, 'a' => $arguments);
        return $this;
    }

    /**
     * @return mixed
     */
    public function go()
    {
        $result = call_user_func($this->func, $this);
        $this->flow = [];
        return $result;
    }
}
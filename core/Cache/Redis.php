<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-07 13:41:59
 * @LastEditors: cclilshy jingnigg@163.com
 * @FilePath: /ccphp/vendor/core/Cache/Redis.php
 * @Description: My house
 * Copyright (c) 2023 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace core\Cache;

class Redis
{
    protected static $config;

    // KeyValue数据库的实体
    protected \core\Database\Redis $connect;

    /**
     * 在创建类时会初始化该连接
     */
    public function __construct()
    {
        $this->connect = \core\Database\Redis::connect(self::$config);
    }

    /**
     * 加载时加载连接配置
     * @param $config
     * @return void
     */
    public static function initialization($config): void
    {
        self::$config = $config;
    }

    /**
     * 创建一个新的连接并执行指定方法返回
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([new self(), $name], $arguments);
    }

    /**
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->connect, $name], $arguments);
    }

    /**
     * 类销毁时，关闭连接
     */
    public function __destruct()
    {
        $this->connect->close();
    }
}
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
    protected \core\Database\Redis $connect;

    public function __construct()
    {
        $this->connect = \core\Database\Redis::connect(self::$config);
    }

    public static function init($config): void
    {
        self::$config = $config;
    }

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([new self(), $name], $arguments);
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->connect, $name], $arguments);
    }

    public function __destruct()
    {
        $this->connect->close();
    }
}
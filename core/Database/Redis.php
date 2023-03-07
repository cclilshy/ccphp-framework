<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-07 20:03:12
 * @LastEditors: cclilshy jingnigg@163.com
 * @FilePath: /ccphp/vendor/core/Database/RedisPecl.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace core\Database;

use \Redis as RedisPeclPecl;

// 封装一个Redis连接
class Redis
{
    protected $connect;

    public function __construct($connect)
    {
        $this->connect = $connect;
    }

    /**
     * 长连接并获取一个连接
     * @param \stdClass $config
     * @return RedisPecl
     */
    public static function pconnect(\stdClass $config): Redis
    {
        $redis = new \RedisPecl();
        $redis->connect($config->host, $config->port, 1);
        if (!empty($config->password)){
            $redis->auth($config->password);
        }
        $redis->setOption(\RedisPecl::OPT_READ_TIMEOUT, -1);
        return new self($redis);
    }

    /**
     * 获取一个常规连接
     * @param \stdClass $config
     * @return Redis
     */
    public static function connect(\stdClass $config): Redis
    {
        $redis = new \RedisPecl();
        $redis->pconnect($config->host, $config->port, 1);
        if ($config->password !== '') $redis->auth($config->password);

        return new self($redis);
    }

    /**
     * 销毁前释放连接
     */
    public function __destruct()
    {
        $this->connect->close();
    }

    /**
     * 转发请求
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->connect, $name], $arguments);
    }
}
<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-07 20:03:12
 * @LastEditors: cclilshy jingnigg@163.com
 * @FilePath: /ccphp/vendor/core/Database/Redis.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */
namespace core\Database;

class Redis
{
    protected $connect;
    public static function pconnect(\stdClass $config)
    {
        $redis = new \Redis();
        $redis->connect($config->host,$config->port,1);
        if($config->password !== '') $redis->auth($config->password);
        $redis->setOption(\Redis::OPT_READ_TIMEOUT, -1);
        return new self($redis);
    }

    public static function connect(\stdClass $config)
    {
        $redis = new \Redis();
        $redis->pconnect($config->host,$config->port,1);
        if($config->password !== '') $redis->auth($config->password);

        return new self($redis);
    }

    public function __construct($connect)
    {
        $this->connect = $connect;
    }

    public function __destruct(){
        $this->connect->close();
    }

    public function __call($name,$arguments){
        return call_user_func_array([$this->connect,$name],$arguments);
    }
}
<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-07 20:41:26
 * @LastEditors: cclilshy cclilshy@163.com
 * @FilePath: /ccphp/vendor/core/Database.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace core\Database;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Connection;

class DB
{
    private static $config; // 配置
    private static $db; // 全局连接
    private static string $dbClass; // 使用的数据库类型
    private static Capsule $capsule; // Laravel的依赖
    private static Connection|Pool $connect; // 储存连接实体或连接池

    /**
     * 初始化时会载入配置并创建一个全局连接
     * @param $config
     * @return void
     */
    public static function initialization($config): void
    {
        // $type = $config['type'];
        // self::$dbClass = __NAMESPACE__ . '\Database\\' . ucfirst($type);
        // self::$config = $config[$type];
        self::$capsule = new Capsule;
        self::$capsule->addConnection($config);
        self::$connect = self::getConnect();
    }

    /**
     * 获取一个ORM连接实体
     * @return Connection
     */
    public static function getConnect(): Connection
    {
        return self::$capsule->getConnection();
    }

    /**
     * 把请求转接到全局连接实体
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([self::$connect, $name], $arguments);
    }
}

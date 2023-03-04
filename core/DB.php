<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-07 20:41:26
 * @LastEditors: cclilshy cclilshy@163.com
 * @FilePath: /ccphp/vendor/core/Database.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace core;

use core\Database\Pool;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Connection;

class DB
{
    private static $config;
    private static $db;
    private static string $dbClass;
    private static Capsule $capsule;
    private static Connection|Pool $connect;

    public static function init($config): void
    {
        // $type = $config['type'];
        // self::$dbClass = __NAMESPACE__ . '\Database\\' . ucfirst($type);
        // self::$config = $config[$type];
        self::$capsule = new Capsule;
        self::$capsule->addConnection($config);
        self::$connect = self::getConnect();
    }

    public static function getConnect(): Connection
    {
        return self::$capsule->getConnection();
    }

    public static function load(): void
    {
    }

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([self::$connect, $name], $arguments);
    }
}

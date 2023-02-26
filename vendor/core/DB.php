<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-07 20:41:26
 * @LastEditors: cclilshy jingnigg@163.com
 * @FilePath: /ccphp/vendor/core/Database.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace core;

class DB
{
    protected static $config;
    protected static $db;
    protected static string $dbClass;

    public static function init($config): void
    {
        $type = $config['type'];
        self::$dbClass = __NAMESPACE__ . '\Database\\' . ucfirst($type);
        self::$config = $config[$type];
    }

    public static function connect()
    {
        return new self::$dbClass(self::$config);
    }

    public static function table($name)
    {
        return call_user_func([new self::$dbClass(self::$config), 'table'], $name);
    }

    public static function name($name)
    {
        return call_user_func([new self::$dbClass(self::$config), self::$config['prefix'] . $name]);
    }
}
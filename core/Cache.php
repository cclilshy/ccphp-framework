<?php
/*
 * @Author: cclilshy cclilshy@163.com
 * @Date: 2023-03-04 13:04:53
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: jingnigg@gmail.com, All Rights Reserved.
 */

namespace core;

// Service Layer For Cache Data And Provide The Corresponding Method

class Cache
{
    protected static $buffer;
    protected static $config;

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([__NAMESPACE__ . '\\' . self::$buffer, $name], $arguments);
    }

    public static function init($config): void
    {
        $type = $config['type'];
        self::$config = $config[$type];
        self::$buffer = ucfirst($type);
        call_user_func([__NAMESPACE__ . '\Cache\\' . self::$buffer, 'init'], self::$config);
    }

    public static function template(string $hash, string $content): string
    {
        file_put_contents(CACHE_PATH . '/template/' . $hash . '.php', $content);
        return CACHE_PATH . '/template/' . $hash . '.php';
    }
}

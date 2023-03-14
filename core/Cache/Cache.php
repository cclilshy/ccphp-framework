<?php
/*
 * @Author: cclilshy cclilshy@163.com
 * @Date: 2023-03-04 13:04:53
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: jingnigg@gmail.com, All Rights Reserved.
 */

namespace core\Cache;

// Service Layer For Cache Data And Provide The Corresponding Method


class Cache
{
    protected static mixed $buffer;
    protected static array $config;

    /**
     * 访问指定缓存器的静态方法
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([__NAMESPACE__ . '\\' . self::$buffer, $name], $arguments);
    }

    /**
     * 初始化并实例连接
     *
     * @param $config
     * @return void
     */
    public static function initialization($config): void
    {
        $type         = $config['type'];
        self::$config = $config[$type];
        self::$buffer = ucfirst($type);
        call_user_func([__NAMESPACE__ . '\Cache\\' . self::$buffer, 'init'], self::$config);
    }

    /**
     * 将一个模板文件放入临时文件夹中并返回其哈希
     *
     * @param string $hash
     * @param string $content
     * @return string
     */
    public static function template(string $hash, string $content): string
    {
        file_put_contents(CACHE_PATH . '/template/' . $hash . '.php', $content);
        return CACHE_PATH . '/template/' . $hash . '.php';
    }
}

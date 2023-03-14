<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-08 14:48:01
 * @LastEditors: cclilshy cclilshy@163.com
 * @FilePath: /ccphp/vendor/core/Route.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace core\Route;

// 加载层, 用于记录用户请求的路由, 并提供对应的方法


/**
 * @method static console(string $string, string $string1)
 */
class Route
{
    public const METHODS = ['get', 'post', 'put', 'patch', 'delete', 'options', 'console', 'cron'];
    private static array $map = [];

    /**
     * 加载所有路由文件
     */
    public static function initialization(): void
    {
        $path = APP_PATH . FS . 'route';
        if (is_dir($path)) {
            $list = scandir($path);
            array_shift($list);
            array_shift($list);
            foreach ($list as $item) {
                require $path . FS . $item;
            }
        }
    }

    /**
     * 在允许的方法内定义路由
     *
     * @param $name
     * @param $arguments
     * @return void
     */
    public static function __callStatic($name, $arguments): void
    {
        if (!in_array($name, self::METHODS))
            return;

        $method   = strtoupper($name);
        $entrance = '/' . trim($arguments[0], '/');

        if (is_callable($arguments[1])) {
            self::$map[$method][$entrance] = new Map('anonymous', '', '', $arguments[1]);
        } else {
            $_                             = explode('@', $arguments[1]);
            self::$map[$method][$entrance] = new Map('controller', $_[0], $_[1] ?? 'main');
        }
    }

    /**
     * 组合方法定义路由
     *
     * @param $methods
     * @param $uri
     * @param $callback
     * @return void
     */
    public static function match($methods, $uri, $callback): void
    {
        foreach ($methods as $item) {
            self::$item($uri, $item, $callback);
        }
    }

    /**
     * 模拟访问执行
     *
     * @param $method
     * @param $entrance
     * @return void
     */
    public static function simulation($method, $entrance): void
    {
        $result = self::guide($method, $entrance);
        $result && $result->run();
    }

    /**
     * 根据入口匹配路由Map
     *
     * @param string $method
     * @param string $entrance
     * @return Map|null
     */
    public static function guide(string $method, string $entrance): Map|null
    {
        $entrance = '/' . trim($entrance, '/');
        $method   = strtoupper($method);
        if (isset(self::$map[$method][$entrance])) {
            return self::$map[$method][$entrance];
        }
        return null;
    }

    /**
     * 获取所有Console路由
     *
     * @return array
     */
    public static function consoles(): array
    {
        return self::$map['CONSOLE'] ?? [];
    }
}

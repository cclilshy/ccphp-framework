<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-08 14:48:01
 * @LastEditors: cclilshy cclilshy@163.com
 * @FilePath: /ccphp/vendor/core/Route.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace core;

// 加载层, 用于记录用户请求的路由, 并提供对应的方法

class Route
{
    const METHODS = array('get', 'post', 'put', 'patch', 'delete', 'options', 'console', 'cron');
    private static array $map = array();

    public static function init(): void
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

    public static function __callStatic($name, $arguments): void
    {
        if (!in_array($name, self::METHODS)) return;
        
        $method = strtoupper($name);
        $entrance = '/' . trim($arguments[0], '/');

        if (is_callable($arguments[1])) {
            self::$map[$method][$entrance] = new Map('anonymous', '', '', $arguments[1]);
        } else {
            $_ = explode('@', $arguments[1]);
            self::$map[$method][$entrance] = new Map('controller', $_[0], $_[1] ?? 'main');
        }
    }

    public static function match($methods, $uri, $callback): void
    {
        foreach ($methods as $item) {
            self::$item($uri, $item, $callback);
        }
    }

    public static function simulation($method, $entrance): void
    {
        $result = self::guide($method, $entrance);
        $result && $result->run();
    }

    public static function guide(string $method, string $entrance): Map | null
    {
        
        $entrance = '/' . trim($entrance, '/');
        // var_dump($entrance,self::$map);die;
        $method = strtoupper($method);
        if (isset(self::$map[$method][$entrance])) {
            return self::$map[$method][$entrance];
        }
        return null;
    }

    public static function consoles()
    {
        return self::$map['CONSOLE'] ?? [];
    }
}

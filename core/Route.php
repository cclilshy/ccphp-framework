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
    private static string $entrance;
    private static $guide;

    public static function __callStatic($name, $arguments)
    {
        self::push($arguments[0], $name, self::parseCallback($arguments[1], $arguments[2] ?? ''));
    }

    private static function push($entrance, $method, $map): void
    {
        if (in_array($method, self::METHODS)) self::$map[$method][trim($entrance, '/')] = $map;
    }

    private static function parseCallback($functionRoute, $params = ''): false|Map
    {
        $params = explode(',', $params);
        if (is_string($functionRoute)) {
            $route = explode('@', trim($functionRoute));
            return Map::create($route[0], $route[1] ?? 'main', $params);
        } elseif (is_callable($functionRoute)) {
            return Map::create('', $functionRoute, $params);
        }
        return false;
    }

    public static function init(): void
    {
        $path = APP_PATH . FS . 'route';
        if (is_dir($path)) {
            foreach (scandir($path) as $item) {
                $item !== '.' && $item !== '..' && require $path . FS . $item;
            }
        }
    }

    public static function match($methods, $uri, $callback, $arguments = null): void
    {
        foreach ($methods as $item) {
            self::push($uri, $item, self::parseCallback($callback, $arguments));
        }
    }

    public static function console($command, $class): void
    {
        self::push($command, 'console', self::parseCallback($class));
    }

    public static function simulation($entrance, $method): void
    {
        $result = self::guide($entrance, $method);
        $result && $result->run();
    }

    public static function guide($entrance, $method)
    {
        if ($index = strpos($entrance, strstr($entrance, '?'))) {
            $entrance = substr($entrance, 0, $index);
        }
        self::$entrance = $entrance;
        $method = strtolower($method);
        if (isset(self::$map[$method][$entrance])) {
            self::$guide = self::$map[$method][$entrance];
        } elseif ($method !== 'console') {
            foreach (self::$map[$method] as $key => $map) {
                $entranceFrags = explode('/', $entrance);
                $routeKeyFrags = explode('/', $key);
                if (count($entranceFrags) !== count($routeKeyFrags))
                    continue;
                for ($i = 0; $i < count($routeKeyFrags); $i++) {
                    if ($entranceFrags[$i] !== $routeKeyFrags[$i]) {
                        if ((str_starts_with($routeKeyFrags[$i], ':'))) {
                            Input::set('get', substr($routeKeyFrags[$i], 1), $entranceFrags[$i]);
                        } else {
                            break;
                        }
                        if ($i === count($routeKeyFrags) - 1) {
                            self::$guide = $map;
                            break;
                        }
                    }
                }
                if (self::$guide !== null) {
                    break;
                }
            }
        }

        return self::$guide;
    }

    public static function consoles()
    {
        return self::$map['console'];
    }

    public static function simulator($method, $target)
    {
        return self::guide($method, $target);
    }
}

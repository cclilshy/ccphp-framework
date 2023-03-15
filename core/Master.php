<?php
/*
 * @Author: cclilshy cclilshy@163.com
 * @Date: 2023-03-06 16:40:23
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: jingnigg@gmail.com, All Rights Reserved.
 */

namespace core;


class Master
{
    private static array  $record;
    private static string $modulePath = __NAMESPACE__;


    /**
     * @param string $name
     * @param array  $args
     * @return mixed|void
     */
    public static function rouse(string $name, array $args = [])
    {
        $class = self::$modulePath . '\\' . ucfirst($name);
        if (class_exists($class)) {
            self::$record[] = $name;
            return call_user_func_array([$class, 'initialization'], $args);
        } else {
            echo("Class $class not found\n");
        }
    }

    /**
     * @param string|null $name
     * @return array
     */
    public static function reload(string $name = null): array
    {
        $result = [];
        foreach (self::$record as $name) {
            $class         = self::$modulePath . '\\' . ucfirst($name);
            $result[$name] = call_user_func([$class, 'reload']);
        }
        return $result;
    }

    /**
     * @param string $path
     * @return void
     */
    public static function setModulePath(string $path): void
    {
        self::$modulePath = $path;
    }
}

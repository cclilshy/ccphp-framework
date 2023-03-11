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
    private static array $record;
    private static string $modulePath = __NAMESPACE__;

    /**
     * @throws \Exception
     */
    public static function rouse(string $name, array $args = [])
    {
        $class = self::$modulePath . '\\' . ucfirst($name);
        if (class_exists($class)) {
            self::$record[] = $name;
            return call_user_func_array([$class, 'initialization'], $args);
        } else {
            throw new \Exception("Class $class not found");
        }
    }

    public static function reload(string $name = null): array
    {
        $result = array();
        foreach (self::$record as $name) {
            $class = self::$modulePath . '\\' . ucfirst($name);
            $result[$name] = call_user_func([$class, 'reload']);
        }
        return $result;
    }

    public static function setModulePath(string $path): void
    {
        self::$modulePath = $path;
    }
}

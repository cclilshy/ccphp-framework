<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-06 16:15:21
 * @LastEditors: cclilshy cclilshy@163.com
 * @FilePath: /ccphp/vendor/core/Master.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace core;

// The Leader Of The Entire Framework Is Used To Start Each Module

class Master
{
    private static array $record = [];

    public static function rouse()
    {
        $arguments = func_get_args();
        $modules = array_map(function ($item) {
            self::$record[] = $item;
            return call_user_func([__NAMESPACE__ . '\\' . $item, 'init'], Config::get(strtolower($item)));
        }, $arguments);
        return count($modules) === 1 ? current($modules) : $modules;
    }

    public static function flush(): void
    {
        foreach (self::$record as $item) {
            if (in_array($item, self::$record)) {
                if (method_exists(__NAMESPACE__ . '\\' . $item, 'load')) {
                    call_user_func([__NAMESPACE__ . '\\' . $item, 'load']);
                }
            }
        }
    }

    public static function list(): array
    {
        return self::$record;
    }
}

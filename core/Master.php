<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-06 16:15:21
 * @LastEditors: cclilshy jingnigg@163.com
 * @FilePath: /ccphp/vendor/core/Master.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace core;

// The Leader Of The Entire Framework Is Used To Start Each Module

class Master
{
    public static function rouse()
    {
        $arguments = func_get_args();
        $modules = array_map(function ($item) {
            return call_user_func([__NAMESPACE__ . '\\' . $item, 'init'], Config::get(strtolower($item)));
        }, $arguments);
        return count($modules) === 1 ? current($modules) : $modules;
    }
}
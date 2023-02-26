<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-24 18:50:34
 * @LastEditors: cclilshy jingnigg@163.com
 * @FilePath: /ccphp/vendor/core/Model.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace core;

/**
 * @property string $table
 */
class Model
{
    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([DB::table(static::$table), $name], $arguments);
    }
}
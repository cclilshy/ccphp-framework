<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-03 23:15:15
 * @LastEditors: cclilshy cclilshy@163.com
 * @FilePath: /ccphp/extend/plugin/Hello.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace extend\Plugin;

class Hello
{
    public function __construct()
    {
        echo 'I am a greeting class' . PHP_EOL;
    }

    public static function main(): string
    {
        return __CLASS__;
    }
}
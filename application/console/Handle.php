<?php
/*
 * @Author: cclilshy cclilshy@163.com
 * @Date: 2023-02-26 20:52:05
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: cclilshy, All Rights Reserved.
 */

namespace console;

class Handle
{
    public static function register(): string
    {
        return 'You can use ccphp happily';
    }

    public function main($argv, $console): void
    {
        $console::printn("hello,world");
    }
}
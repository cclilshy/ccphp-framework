<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-25 19:05:33
 * @LastEditors: cclilshy jingnigg@163.com
 * @FilePath: /ccphp/vendor/core/ccphp.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace core\ccphp;

// Entry Class For Starting The Framework

use Exception;
use core\Master;
use core\Console;


class ccphp
{
    /**
     * 加载框架必要的依赖
     *
     * @return void
     */
    public static function initialization(): void
    {
        try {
            Master::rouse('Cache');
            Master::rouse('Cache');
            Master::rouse('Config');
            Master::rouse('Log');
        } catch (Exception $e) {
            Console::pred($e->getMessage());
        }
    }

    /**
     * 获取框架自带模板内容
     *
     * @param string $name
     * @return string
     */
    public static function template(string $name): string
    {
        return file_get_contents(__DIR__ . '/template/' . $name . '.tpl');
    }
}
<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-25 19:05:33
 * @LastEditors: cclilshy cclilshy@163.com
 * @FilePath: /ccphp/vendor/core/Ccphp/Launch.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace core\Ccphp;

// 加载层, 整个框架的入口,只加载一次，无法重载
// 根据不同的运行环境，启动不同的服务, 并且加载运行环境的配置文件以及初始化输入
use core\Master;
use core\Ccphp\Statistics;

class Launch
{
    public static function init($config = null)
    {
    }

    public static function start(string $app): void
    {
        switch ($app) {
            case 'Http':
                Master::rouse('Http');
                break;
            case 'Console':
                Master::rouse('Console')->run();
                break;
            case 'build':
                //
                break;
        }
    }

    public static function template(string $name): string
    {
        return file_get_contents(__DIR__ . '/template/' . $name . '.tpl');
    }
}

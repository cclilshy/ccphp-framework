<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-25 19:05:33
 * @LastEditors: cclilshy jingnigg@163.com
 * @FilePath: /ccphp/vendor/core/Ccphp/Launch.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace core\Ccphp;

// 加载层, 整个框架的入口
// 根据不同的运行环境，启动不同的服务, 并且加载运行环境的配置文件以及初始化输入

class Launch
{
    private static $launch;
    public static function init($config = null)
    {
        self::reset();
        switch (Launch::$launch->type) {
            case 'cli-server':
                Launch::httpLauncher();
                break;
            
            case 'fpm-fcgi':
                Launch::httpLauncher();
                break;

            case 'cgi':
                Launch::httpLauncher();
                break;
            case 'cli':
                \core\Master::rouse('Console')->run();
                break;
        }
    }

    private static function httpLauncher(){
        \core\Master::rouse('Http')->run();
    }
    
    // 常驻内存运行数据重置接口
    public static function reset()
    {
        Launch::$launch = new \stdClass();
        Launch::$launch->sqls = [];
        Launch::$launch->loadFiles = [];
        Launch::$launch->startTime = microtime(true);
        Launch::$launch->type = php_sapi_name();
        Launch::$launch->memory = memory_get_usage();
        Launch::$launch->maxMemory = memory_get_peak_usage();
    }


    public static function record($type = null, $data = null)
    {
        if ($type === 'sql') {
            array_push(self::$launch->sqls, $data);
        } elseif ($type === 'input') {
            Launch::$launch->get = \core\Input::get();
            Launch::$launch->post = \core\Input::post();
        } elseif($type === 'end') {
            Launch::$launch->loadFiles = get_included_files();
            Launch::$launch->endTime = microtime(true);
            Launch::$launch->memory = memory_get_usage();
            Launch::$launch->maxMemory = memory_get_peak_usage();
        }
    }

    public static function statistics()
    {
        Launch::record('end');
        return self::$launch;
    }

    public static function template(string $name) : string
    {
        return file_get_contents(__DIR__ . '/template/' . $name);
    }

    public static function getPhpEnv() : string {
        return Launch::$launch->type === 'cli' ? 'cli' : 'http';
    }
}

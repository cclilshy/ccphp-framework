<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-04 00:13:15
 * @LastEditors: cclilshy cclilshy@163.com
 * @FilePath: /ccphp/vendor/core/Http.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace core;

use core\Ccphp\Launch;
use stdClass;

// Load The Running Http Information 
// And Guide To The Destination According To The Routing Static Method Can Be Called Anywhere

class Http
{
    private static $config;
    private static $map;
    private static $request;

    public static function init($config = null): Http
    {
        Http::$config = $config;
        self::reset();
        set_error_handler([__CLASS__, 'httpErrorHandle'], E_ALL);

        return new Http();
    }

    public static function reset(): void
    {
        Master::rouse('Input', 'Session', 'Template');
        Http::$request = new stdClass;
        Http::$request->uri = trim(Input::get('route') ?? $_SERVER['REQUEST_URI'], '/');
        Http::$request->method = $_SERVER['REQUEST_METHOD'];
        Http::$request->ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    // 常驻内存运行数据重置接口

    public static function get(string $key = '')
    {
        return Input::get($key);
    }

    public static function post(string $key = '')
    {
        return Input::post($key);
    }

    public static function ajax(): bool
    {
        return Http::$request->ajax;
    }

    public static function method(): string
    {
        return Http::$request->method;
    }

    public static function getMapAttribute($name)
    {
        return Http::$map->$name;
    }

    public function run(): void
    {
        if (Http::$map = Route::guide(Http::$request->uri, Http::$request->method)) {
            Http::end(Http::$map->run());
        } else {
            Http::httpErrorHandle(0, 'Route not found: {' . Http::$request->uri . '}', __FILE__, 1, 404);
        }
    }

    public static function end(string $content = null, $statusCode = 200): void
    {
        header('HTTP/1.1 ' . $statusCode);
        if (Http::$config['debug'] === true && Http::$request->ajax === false) {
            $statistics = Launch::statistics();
            $general = [
                'timeLength' => $statistics->endTime - $statistics->startTime,
                'uri' => Http::$request->uri,
                'fileCount' => count($statistics->loadFiles),
                'memory' => $statistics->memory,
                'maxMemory' => $statistics->maxMemory
            ];
            Template::define('sqls', $statistics->sqls);
            Template::define('files', $statistics->loadFiles);
            Template::define('general', $general);
            Template::define('gets', $statistics->get);
            Template::define('posts', $statistics->post);
            $statisticsHtml = Launch::template('statistics.html');
            $statisticsHtml = Template::apply($statisticsHtml);
            $content .= PHP_EOL . $statisticsHtml;
        }
        echo $content;
    }

    public static function httpErrorHandle(int $errno, string $errstr, string $errFile, int $errLine, int $httpCode = 503): void
    {
        header('HTTP/1.1 ' . $httpCode);
        $statistics = Launch::statistics();
        $fileDescribe = '';
        if (is_file($errFile)) {
            $errLines = file($errFile);
            $startLine = max($errLine - 10, 1);
            for ($i = 0; $i < 21; $i++, $startLine++) {
                if ($startLine > count($errLines)) break;
                $fileDescribe .= $errLines[$startLine - 1];
            }
        }

        $general = [
            'info' => [
                'errno' => $errno,
                'errstr' => $errstr,
                'errFile' => $errFile,
                'errLine' => $errLine,
                'fileDescribe' => $fileDescribe
            ],
            'timeLength' => $statistics->endTime - $statistics->startTime,
            'fileCount' => count($statistics->loadFiles),
            'memory' => $statistics->memory,
            'maxMemory' => $statistics->maxMemory
        ];

        Template::define('sqls', $statistics->sqls);
        Template::define('files', $statistics->loadFiles);
        Template::define('general', $general);
        Template::define('gets', $statistics->get);
        Template::define('posts', $statistics->post);
        Template::define('config', Config::all());
        $html = Launch::template('error.html');
        $html = Template::apply($html);

        die($html);
    }
}

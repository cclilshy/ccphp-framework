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
use core\Http\Response;
use core\Http\Request;
use core\Ccphp\Statistics;

// Load The Running Http Information 
// And Guide To The Destination According To The Routing Static Method Can Be Called Anywhere

class Http
{
    private static $config;
    private static Http $http;

    private Request $request;
    private Response $response;
    private Statistics $statistics;
    private string $controllerName;
    private $functionName;

    public static function init($config = null): void
    {
        Http::$config = $config;
        Master::rouse(
            'Http\\Request',    // 唤醒请求类
            'Http\\Response',   // 唤醒响应类
            'DB',               // 唤醒数据库类
            'Session',          // 唤醒会话类
            'Model',            // 唤醒模型类
            'Input',            // 唤醒输入类
            'Ccphp\\Statistics', // 唤醒统计类
            'Template',         //  唤醒模板类
            'Log'   // 唤醒日志类
        );
        self::$http = new self;
        self::$http->executeStream();
    }

    public static function load()
    {
        self::$http = new self;
        self::$http->executeStream();
    }

    public static function isAjax(): bool
    {
        return self::$http->request->ajax;
    }

    public static function __callStatic($name, $arguments)
    {
        return self::$http->$name;
    }

    private function __construct()
    {
        $this->request = Request::get();
        $this->response = Response::get();
        $this->statistics = Statistics::get();
    }

    public function executeStream()
    {
        Log::setEnv('HTTP');
        Log::setConstant([
            'URL' => $this->request->path,
            'QUEST' => json_encode(array_merge($this->request->get, $this->request->post)),
        ]);

        if ($route  = Route::guide($this->request->path, $this->request->method)) {
            $this->controllerName = $route->controllerName;
            $this->functionName = is_callable($route->functionName) ? 'funcion' : $route->functionName;
            set_error_handler([__CLASS__, 'httpErrorHandle'], E_ALL);
            $result = $route->run();
            Http::response($result);
        } else {
            Http::httpErrorHandle(0, 'Route not found: {' . $this->request->path . '}', __FILE__, 1, 404);
        }
    }

    public function response(string $content = null, $statusCode = 200): void
    {
        $this->statistics->record('endTime', microtime(true));
        if (Http::$config['debug'] === true && self::isAjax() === false) {
            $general = [
                'timeLength' => $this->statistics->endTime - $this->statistics->startTime,
                'uri' => $this->request->path,
                'fileCount' => count($this->statistics->loadFiles),
                'memory' => $this->statistics->memory,
                'maxMemory' => $this->statistics->maxMemory
            ];
            Template::define('sqls', $this->statistics->sqls);
            Template::define('files', $this->statistics->loadFiles);
            Template::define('general', $general);
            Template::define('gets', $this->request->get);
            Template::define('posts', $this->request->post);
            $statisticsHtml = Launch::template('statistics');
            $statisticsHtml = Template::apply($statisticsHtml);
            $content .= PHP_EOL . $statisticsHtml;
        }
        Response::return($content, $statusCode);
    }

    public function httpErrorHandle(int $errno, string $errstr, string $errFile, int $errLine, int $httpCode = 503): void
    {
        $this->statistics->record('endTime', microtime(true));
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
            'timeLength' => $this->statistics->endTime - $this->statistics->startTime,
            'fileCount' => count($this->statistics->loadFiles),
            'memory' => $this->statistics->memory,
            'maxMemory' => $this->statistics->maxMemory
        ];

        Template::define('sqls', $this->statistics->sqls);
        Template::define('files', $this->statistics->loadFiles);
        Template::define('general', $general);
        Template::define('gets', $this->request->get);
        Template::define('posts', $this->request->post);
        Template::define('config', Config::all());
        $html = Launch::template('error');
        $html = Template::apply($html);

        Response::return($html, $httpCode);
    }
}

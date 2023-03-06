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
use core\Ccphp\Statistics;
use core\Http\Request;
use core\Http\Response;
use core\Ccphp\FlowBuild;
use core\Ccphp\FlowController;

// Load The Running Http Information 
// And Guide To The Destination According To The Routing Static Method Can Be Called Anywhere

class Http
{
    public Map $map;
    private Request $request;
    private Response $response;
    private Statistics $statistics;
    public static FlowController $flowController;
    private static array $config;

    private function __construct()
    {
    }

    public static function init($config = null): Http
    {
        self::$config = $config;
        FlowBuild::init();
        FlowBuild::load('Http', new self);
        FlowBuild::load('Request', Master::rouse('Http\\Request'));
        FlowBuild::load('Response', Master::rouse('Http\\Response'));
        FlowBuild::load('DB', Master::rouse('DB'));
        FlowBuild::load('Session', Master::rouse('Session'));
        FlowBuild::load('Model', Master::rouse('Model'));
        FlowBuild::load('Statistics', Master::rouse('Ccphp\\Statistics'));
        FlowBuild::load('Template', Master::rouse('Template'));
        FlowBuild::load('Log', Master::rouse('Log'));

        self::$flowController = FlowBuild::build(
            function (string $type, $client, FlowController $flow) {
                switch ($type) {
                    case 'PROXY':
                        $flow->handle('Request', 'initialization', [$type, $client], $request)
                            ->handle('Response', 'initialization', [$request], $response)
                            ->handle('Http', 'visit', [$request, $response], $_)
                            ->handle('Http', 'route', [], $route);

                        if ($route) {
                            $flow->handle('Statistics', 'record', ['endTime', microtime(true)], $statistics)
                                ->handle('Http', 'statistics', [$route->run($request), $statistics], $html)
                                ->handle('Response', 'setBody', [$html], $response)
                                ->handle('Response', 'send', [], $_);
                        } else {
                            $flow->handle('Statistics', 'record', ['endTime', microtime(true)], $statistics)
                                ->handle('Http', 'statistics', ['', $statistics], $html)
                                ->handle('Http', 'httpErrorHandle', [0, 'Route not found: {' . $request->path . '}', __FILE__, 1, 404], $_);
                        }
                        break;
                    default:

                        break;
                }
            },
            function (int $code, string $msg, string $file, int $line, FlowController $flowController) {
                $flowController->handle('Http', 'httpErrorHandle', [$code, $msg, $file, $line], $_);
            }
        );
        return self::$flowController->Http;
    }

    public static function load(): void
    {
        self::$flowController->handle('Http', 'executeStream', [], $result);
    }

    public static function run($type = 'PROXY', $client = null): void
    {
        self::$flowController->go($type, $client);
    }

    public static function isAjax(): bool
    {
        return self::$flowController->Request->ajax;
    }

    public function visit(Request $request, Response $response): void
    {
        $this->request  = $request;
        $this->response = $response;
    }

    public function route(): mixed
    {
        if ($route = Route::guide($this->request->method, $this->request->path)) {
            $this->map = $route;
        }
        return $route;
    }

    public function statistics(string $content, Statistics $statistics): string
    {
        $this->statistics = $statistics;
        if (Http::$config['debug'] === true && self::isAjax() === false) {
            $general = [
                'timeLength' => $statistics->endTime - $statistics->startTime,
                'uri'        => $this->request->path,
                'fileCount'  => count($statistics->loadFiles),
                'memory'     => $statistics->memory,
                'maxMemory'  => $statistics->maxMemory
            ];
            Template::define('sqls', $statistics->sqls);
            Template::define('files', $statistics->loadFiles);
            Template::define('general', $general);
            Template::define('gets', $this->request->get);
            Template::define('posts', $this->request->post);
            $statisticsHtml = Launch::template('statistics');
            $statisticsHtml = Template::apply($statisticsHtml);
            $content .= PHP_EOL . $statisticsHtml;
        }
        return $content;
    }

    public function httpErrorHandle(int $errno, string $errstr, string $errFile, int $errLine, int $httpCode = 503): void
    {
        $this->statistics->record('endTime', microtime(true));
        $fileDescribe = '';
        if (is_file($errFile)) {
            $errLines  = file($errFile);
            $startLine = max($errLine - 10, 1);
            for ($i = 0; $i < 21; $i++, $startLine++) {
                if ($startLine > count($errLines))
                    break;
                $fileDescribe .= $errLines[$startLine - 1];
            }
        }

        $general = [
            'info'       => [
                'errno'        => $errno,
                'errstr'       => $errstr,
                'errFile'      => $errFile,
                'errLine'      => $errLine,
                'fileDescribe' => $fileDescribe
            ],
            'timeLength' => $this->statistics->endTime - $this->statistics->startTime,
            'fileCount'  => count($this->statistics->loadFiles),
            'memory'     => $this->statistics->memory,
            'maxMemory'  => $this->statistics->maxMemory
        ];

        Template::define('sqls', $this->statistics->sqls);
        Template::define('files', $this->statistics->loadFiles);
        Template::define('general', $general);
        Template::define('gets', $this->request->get);
        Template::define('posts', $this->request->post);
        Template::define('config', Config::all());
        $html = Launch::template('error');
        $html = Template::apply($html);

        self::$flowController->Response->setStatusCode($httpCode);
        self::$flowController->Response->setBody($html);
        self::$flowController->Response->send();
    }

    public static function header($name, $value)
    {
        self::$flowController->Response->setHeader($name, $value);
    }
}

<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-04 00:13:15
 * @LastEditors: cclilshy cclilshy@163.com
 * @FilePath: /ccphp/vendor/Cclilshy\Flowphp/Http.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace core\Http;

use core\Flow\FlowBuild;
use core\Flow\FlowController;
use core\Master;
use core\Ccphp\Statistics;
use core\Config;

// Load The Running Http Information
// And Guide To The Destination According To The Routing Static Method Can Be Called Anywhere

class Http
{
    private static array $config;   // 全局配置
    public FlowController $flow;    // 流程
    public Statistics $statistics;  // 统计
    public Request $request;        // 请求信息
    public Response $response;      // 响应信息

    public function __construct()
    {
        $this->statistics = new Statistics;
        $this->request = new Request;
    }

    /**
     * 初始化配置并返回一个Http实体
     * @return Http
     * @throws \Exception
     */
    public static function initialization() : Http
    {
        // 加载路由
        self::$config = Config::get('http');
        Master::rouse('Route');
        return new self;
    }

    /**
     * 返回一个实体
     * @return Http
     */
    public static function build(): Http
    {
        return new self;
    }

    /**
     * 选定指定类型解析
     * @param ...$_
     * @return $this
     */
    public function go(...$_)
    {
        $this->request->setClient($_[0]);
        $this->request->parse();
        $this->response = $this->request->response;
        if ($route = Route::guide($this->request->method, $this->request->path)) {
            $context = $route->run((object)[
                'request' => $this->request,
                'response' => $this->response,
                'http' => $this,
                'template' => new Template
            ]);
            echo $context;
        }
        $this->statistics->record('endTime', microtime(true));
        return $this;
    }

    /**
     * 响应模板插入调试面板
     * @param string $content
     * @param Statistics $statistics
     * @return string
     */
    public function statistics(string $content, Statistics $statistics): string
    {
        $this->statistics = $statistics;
        if (Http::$config['debug'] === true && $this->flow->Request->ajax === false) {
            $general = [
                'timeLength' => $statistics->endTime - $statistics->startTime,
                'uri' => $this->flow->Request->path,
                'fileCount' => count($statistics->loadFiles),
                'memory' => $statistics->memory,
                'maxMemory' => $statistics->maxMemory
            ];
            $template = new core\Http\Template;
            $template->define('sqls', $statistics->sqls);
            $template->define('files', $statistics->loadFiles);
            $template->define('general', $general);
            $template->define('gets', $this->flow->Request->get);
            $template->define('posts', $this->flow->Request->post);

            $statisticsHtml = file_get_contents(RES_PATH . '/template/statistics.html');
            return $content .= PHP_EOL . $template->apply($statisticsHtml);
        }
        return $content;
    }

    /**
     * 由错误模板接手请求
     * @param int $errno
     * @param string $errstr
     * @param string $errFile
     * @param int $errLine
     * @param int $httpCode
     * @return void
     */
    public function httpErrorHandle(int $errno, string $errstr, string $errFile, int $errLine, int $httpCode = 503): void
    {
        $this->statistics->record('endTime', microtime(true));
        $fileDescribe = '';
        if (is_file($errFile)) {
            $errLines = file($errFile);
            $startLine = max($errLine - 10, 1);
            for ($i = 0; $i < 21; $i++, $startLine++) {
                if ($startLine > count($errLines))
                    break;
                $fileDescribe .= $errLines[$startLine - 1];
            }
        }

        $general = [
            'uri' => $this->flow->Request->path,
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

        $template = new core\Http\Template;
        $template->define('sqls', $this->statistics->sqls);
        $template->define('files', $this->statistics->loadFiles);
        $template->define('general', $general);
        $template->define('gets', $this->flow->Request->get);
        $template->define('posts', $this->flow->Request->post);
        $template->define('config', Config::all());

        $html = file_get_contents(RES_PATH . '/template/error.html');
        $html = $template->apply($html);
        $this->flow->Request->return($html);
    }
}

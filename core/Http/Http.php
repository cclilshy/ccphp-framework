<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-04 00:13:15
 * @LastEditors: cclilshy jingnigg@gmail.com
 * @FilePath: /ccphp/vendor/Cclilshy\Flow-php/Http.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace core\Http;

use core\Config;
use core\Master;
use core\Route\Route;
use core\Ccphp\Ccphp;
use core\Ccphp\Statistics;
use core\Flow\FlowController;


// Load The Running Http Information
// And Guide To The Destination According To The Routing Static Method Can Be Called Anywhere

class Http
{
    private static array  $config;         // 全局配置
    public FlowController $flow;           // 流程
    public Statistics     $statistics;     // 统计
    public Request        $request;        // 请求信息
    public Response       $response;       // 响应信息
    protected bool        $box;

    /**
     * Http constructor.
     */
    public function __construct(?Request $request = null, ?bool $box = false)
    {
        $this->statistics = new Statistics;
        $this->box        = $box;
        if ($request) {
            $this->request = $request;
        } else {
            $this->request = new Request;
        }
    }

    /**
     * 初始化配置并返回一个Http实体
     *
     * @return Http
     * @throws \Exception
     */
    public static function initialization(): Http
    {
        // 加载路由
        self::$config = Config::get('http') ?? [];
        Master::rouse('Route\Route');
        return new self;
    }

    /**
     * 返回一个实体,允许自定义请求对象,如不自定义则主动创建
     */
    public static function build(?Request $request = null, ?bool $box = false): Http
    {
        return new self($request, $box);
    }

    public function __destruct()
    {
        unset($this->request);
        unset($this->statistics);
    }

    /**
     * @param string  $type
     * @param  ?array $data
     * @return ?string
     */
    public function go(string $type, ?array $data = []): ?string
    {
        $this->request->setType($type);
        $this->request->parse();
        $this->response = $this->request->response;

        if ($map = Route::guide($this->request->method, $this->request->path)) {
            switch ($map->type) {
                case 'controller':
                    $t = (object)[
                        'request'  => $this->request,
                        'response' => $this->response,
                        'http'     => $this,
                        'plaster'  => new Plaster
                    ];
                    $_ = new $map->className($t);
                    $t = call_user_func([$_, $map->action], $t);
                    $t = $this->statistics($t, $this->statistics);
                    if ($this->box) {
                        return $this->request->result($t);
                    } else {
                        return $this->request->send($t);
                    }
                default:
                    break;
            }
        } else {
            // 判断静态资源
            $filePath = HTTP_PATH . '/public' . $this->request->path;
            if (file_exists($filePath)) {
                $fileInfo = pathinfo($filePath);
                if (strtoupper($fileInfo['extension']) !== 'PHP') {
                    $this->response->setHeader('Content-Type', mime_content_type($filePath));
                    if ($this->box) {
                        return $this->request->result(file_get_contents($filePath));
                    } else {
                        return $this->request->send(file_get_contents($filePath));
                    }
                    // return $this;
                }
            }
            $_ = $this->httpErrorHandle(404, 'Route not defined : ' . $this->request->path, __FILE__, 1);
            if ($this->box) {
                return $this->request->result($_);
            } else {
                return $this->request->send($_);
            }
        }
        $this->statistics->record('endTime', microtime(true));
    }

    /**
     * 响应模板插入调试面板
     *
     * @param string     $content
     * @param Statistics $statistics
     * @return string
     */
    public function statistics(string $content, Statistics $statistics): string
    {
        if (Http::$config['debug'] === true && $this->request->ajax === false) {
            $this->statistics->record('endTime', microtime(true));
            $general = [
                'timeLength' => $statistics->endTime - $statistics->startTime,
                'uri'        => $this->request->path,
                'fileCount'  => count($statistics->loadFiles),
                'memory'     => $statistics->memory,
                'maxMemory'  => $statistics->maxMemory
            ];
            $plaster = new Plaster();
            $plaster->assign('sqls', $statistics->sqls);
            $plaster->assign('files', $statistics->loadFiles);
            $plaster->assign('general', $general);
            $plaster->assign('gets', $this->request->get);
            $plaster->assign('posts', $this->request->post);

            $statisticsHtml = Ccphp::template('statistics');

            return $content .= PHP_EOL . $plaster->apply($statisticsHtml);
        }
        return $content;
    }

    /**
     * 由错误模板接手请求
     *
     * @param int    $errno
     * @param string $errstr
     * @param string $errFile
     * @param int    $errLine
     * @param int    $httpCode
     * @return \core\Http\Throwable|string
     */
    public function httpErrorHandle(int $errno, string $errstr, string $errFile, int $errLine, int $httpCode = 503)
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
            'uri'        => $this->request->path,
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

        $plaster = new Plaster();
        $plaster->assign('sqls', $this->statistics->sqls);
        $plaster->assign('files', $this->statistics->loadFiles);
        $plaster->assign('general', $general);
        $plaster->assign('gets', $this->request->get);
        $plaster->assign('posts', $this->request->post);
        $plaster->assign('config', Config::all());

        $html = Ccphp::template('error');
        return $plaster->apply($html);
    }
}

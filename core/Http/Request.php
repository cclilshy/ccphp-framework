<?php
/*
 * @Author: cclilshy cclilshy@163.com
 * @Date: 2023-03-03 22:47:18
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: jingnigg@gmail.com, All Rights Reserved.
 */

namespace core\Http;

class Request
{
    public static Request $request;
    private static string $env = 'ORIGIN';
    private string $original;
    private string $method;
    private string $path;
    private string $body;
    private string $cookie;
    private string $type;
    private string $version;
    private bool $ajax;
    private array $header;
    private array $get = array();
    private array $post = array();

    public function __construct()
    {
        if (self::$env === 'ORIGIN') {
            $this->useCGI();
        } elseif (self::$env === 'CCPHP') {
            $this->type = 'CCPHP';
        }
    }

    public function useCGI(): void
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->path = trim($_SERVER['REQUEST_URI'], "/");
        $this->version = floatval($_SERVER['SERVER_PROTOCOL']);
        $this->body = file_get_contents('php://input');
        $this->header = $_SERVER;
        $this->get = $_GET;
        $this->post = $_POST;
        $this->ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
        $this->type = 'CGI';
    }

    public static function init($original = ''): Request
    {
        return self::load();
    }

    public static function load(): Request
    {
        return self::$request = new Request;
    }

    public static function setEnv(string $env): void
    {
        self::$env = $env;
    }

    public static function loadHttpContext(string $context): void
    {
        self::$request->parse($context);
    }

    public function parse(string $context): void
    {
        $this->original = $context;
        // 解析HTTP请求
        $context = explode("\r\n\r\n", $this->original);
        $header = array_shift($context);
        $body = array_shift($context);

        $headerInfo = explode("\r\n", $header);
        $httpInfo = explode(' ', $headerInfo[0]);

        foreach ($headerInfo as $headerItem) {
            $headerItem = explode(':', $headerItem);
            $this->header[trim($headerItem[0])] = trim($headerItem[1] ?? '');
        }

        $this->method = $httpInfo[0];
        $this->path = trim(strtok($httpInfo[1], '?'), "/");
        $this->version = floatval($httpInfo[2]);
        $this->body = $body ?? '';
        $this->cookie = $this->header['Cookie'] ?? '';

        $urlParams = parse_url($httpInfo[1]);
        if (isset($urlParams['query'])) {
            parse_str($urlParams['query'], $get);
        }

        if ($this->method === 'POST') {
            parse_str($body, $post);
        }

        $this->get = $get ?? [];
        $this->post = $post ?? [];
        $this->ajax = isset($this->header['X-Requested-With']) && $this->header['X-Requested-With'] === 'XMLHttpRequest';
        $this->type = 'SOCKET';
    }

    public static function type(): string
    {
        return self::$request->type;
    }

    public static function get(): Request
    {
        return self::$request;
    }

    public function __get($name)
    {
        return $this->$name;
    }
}

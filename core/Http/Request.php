<?php
/*
 * @Author: cclilshy cclilshy@163.com
 * @Date: 2023-03-06 16:48:58
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: jingnigg@gmail.com, All Rights Reserved.
 */

namespace core\Http;

class Request
{
    private $client;
    private string $type;
    private string $method;
    private string $verstion;
    private string $path;
    private string $cookie;
    private string $body;
    private array $get;
    private array $post;
    private array $header;
    private bool $ajax;
    private Response $response;

    public function __construct(?array $config = [])
    {
        if ($config) {
            $this->client = $config['socket'];
            $this->type = 'SERVER';
            $this->method = $config['method'];
            $this->verstion = $config['verstion'];
            $this->path = $config['path'];
            $this->cookie = $config['header']['COOKIE'] ?? '';
            $this->get = [];
            $this->post = [];
            $this->header = $config['header'];
            $this->ajax = isset($this->header['X-REQUESTED-WITH']) && $this->header['X-REQUESTED-WITH'] === 'XMLHttpRequest';
        }
        $this->response = new Response($this);
    }

    public static function initialization(array $config = null): Request
    {
        return new self($config);
    }

    public static function reload(array $config = null): Request
    {
        return new self($config);
    }

    public function setStream($client): Request
    {
        $this->client = $client;
        return $this;
    }

    public function setHeader(string|array $key, ?string $value): Request
    {
        if (is_array($key)) {
            $this->header = array_merge($this->header, $key);
            return $this;
        }
        $this->header[$key] = $value;
        return $this;
    }

    public function setBody(string $body): Request
    {
        $this->body = $body;
        return $this;
    }

    public function setCookie(string $key, string $value): Request
    {
        $this->cookie[$key] = $value;
        return $this;
    }

    public function setType(string $type): Request
    {
        $this->type = $type;
        if ($type === 'SERVER') {
            $this->response->setClient($this->client);
        }
        return $this;
    }

    public function parse(): Request
    {
        if ($this->type === 'SERVER') {

        } elseif ($this->type === 'PROXY') {
            $this->path = '/' . trim($_SERVER['REQUEST_URI'], '/');
            $this->method = $_SERVER['REQUEST_METHOD'];
            $this->cookie = $_SERVER['HTTP_COOKIE'] ?? '';
            $this->get = $_GET;
            $this->post = $_POST;
            $this->header = getallheaders();
            $this->ajax = isset($this->header['X-Requested-With']) && $this->header['X-Requested-With'] === 'XMLHttpRequest';
        }
        return $this;
    }

    public function return(string $context)
    {
        if ($this->type === 'SERVER') {
            $this->response->setBody($context)->send();
        } else {
            echo $this->response->setBody($context);
        }
    }

    public function __get($name)
    {
        return $this->$name;
    }
}

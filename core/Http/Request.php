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
    private string $path;
    private string $cookie;
    private array $get;
    private array $post;
    private array $header;
    private bool $ajax;
    private Response $response;

    public function __construct(array $config = null)
    {
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

    public function setClient(string $type): Request
    {
        $this->type = $type;
        return $this;
    }

    public function parse(): Request
    {
        if ($this->type === 'SERVER') {
        } elseif ($this->type === 'PROXY') {
            $this->path = '/' . trim($_SERVER['REQUEST_URI'], '/');
            $this->method = $_SERVER['REQUEST_METHOD'];
            $this->cookie = $_SERVER['HTTP_COOKIE'];
            $this->get = $_GET;
            $this->post = $_POST;
            $this->header = getallheaders();
            $this->ajax = isset($this->header['X-Requested-With']) && $this->header['X-Requested-With'] === 'XMLHttpRequest';
        }
        $this->response = new Response($this);
        return $this;;
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

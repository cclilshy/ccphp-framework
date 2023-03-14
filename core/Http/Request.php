<?php
/*
 * @Author: cclilshy cclilshy@163.com
 * @Date: 2023-03-06 16:48:58
 * @LastEditors: cclilshy jingnigg@gmail.com
 * @Description: My house
 * Copyright (c) 2023 by user email: jingnigg@gmail.com, All Rights Reserved.
 */

namespace core\Http;


class Request
{
    private mixed    $client;
    private string   $type;
    private string   $method;
    private string   $version;
    private string   $path;
    private string   $cookie;
    private string   $body;
    private array    $get;
    private array    $post;
    private array    $header;
    private bool     $ajax;
    private Response $response;

    /**
     * @param array|null $config
     */
    public function __construct(?array $config = [])
    {
        if ($config) {
            $this->client  = $config['socket'];
            $this->type    = 'SERVER';
            $this->method  = $config['method'];
            $this->version = $config['version'];
            $this->path    = $config['path'];
            $this->cookie  = $config['header']['COOKIE'] ?? '';
            $this->get     = [];
            $this->post    = [];
            $this->header  = $config['header'];
            $this->ajax    = isset($this->header['X-REQUESTED-WITH']) && $this->header['X-REQUESTED-WITH'] === 'XMLHttpRequest';
        }
        $this->response = new Response($this);
    }

    /**
     * @param array|null $config
     * @return \core\Http\Request
     */
    public static function initialization(array $config = null): Request
    {
        return new self($config);
    }

    /**
     * @param array|null $config
     * @return \core\Http\Request
     */
    public static function reload(array $config = null): Request
    {
        return new self($config);
    }

    /**
     * @param $client
     * @return $this
     */
    /**
     * @param $client
     * @return $this
     */
    public function setStream($client): Request
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @param string|array $key
     * @param string|null  $value
     * @return $this
     */
    public function setHeader(string|array $key, ?string $value): Request
    {
        if (is_array($key)) {
            $this->header = array_merge($this->header, $key);
            return $this;
        }
        $this->header[$key] = $value;
        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function setCookie(string $key, string $value): Request
    {
        $this->cookie[$key] = $value;
        return $this;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type): Request
    {
        $this->type = $type;
        if ($type === 'SERVER') {
            $this->response->setClient($this->client);
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function parse(): Request
    {
        if ($this->type === 'SERVER') {
            return $this;
        } elseif ($this->type === 'PROXY') {
            $this->path   = '/' . trim($_SERVER['REQUEST_URI'], '/');
            $this->method = $_SERVER['REQUEST_METHOD'];
            $this->cookie = $_SERVER['HTTP_COOKIE'] ?? '';
            $this->get    = $_GET;
            $this->post   = $_POST;
            $this->header = getallheaders();
            $this->ajax   = isset($this->header['X-Requested-With']) && $this->header['X-Requested-With'] === 'XMLHttpRequest';
        }
        return $this;
    }

    /**
     * @param string $context
     * @return void
     */
    public function send(string $context)
    {
        if ($this->type === 'SERVER') {
            $this->response->setBody($context)->send();
        } else {
            echo $this->response->setBody($context);
        }
    }

    /**
     * @param string $body
     * @return $this
     */
    public function setBody(string $body): Request
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @param string $context
     * @return string
     */
    /**
     * @param string $context
     * @return string
     */
    public function result(string $context)
    {
        return $this->response->setBody($context)->result();
    }

    /**
     * @param $name
     * @return mixed
     */
    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->$name;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->response);
    }
}

<?php
/*
 * @Author: cclilshy cclilshy@163.com
 * @Date: 2023-03-03 22:47:18
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: jingnigg@gmail.com, All Rights Reserved.
 */

namespace core\Http;

use core\Input;
use core\Ccphp\FlowController;
use Socket;
/**
 * 
 */
class Request
{
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
    private $stream = null;

    private FlowController $flowController;


    public static function init(){
        return new self;
    }
    
    public function build(FlowController $flowController)
    {
        $this->flowController = $flowController;
    }

    public function initialization(string $type, $stream = null): Request
    {
        switch ($type) {
            case 'PROXY':
                $this->method = $_SERVER['REQUEST_METHOD'];
                $this->path = trim($_SERVER['REQUEST_URI'], "/");
                $this->version = floatval($_SERVER['SERVER_PROTOCOL']);
                $this->body = file_get_contents('php://input');
                $this->header = $_SERVER;
                $this->get = $_GET;
                $this->post = $_POST;
                $this->ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
                $this->type = 'PROXY';
                break;
            case 'SERVER':
                $this->stream = $stream;
        }

        return $this;
    }

    public function return(string $context): void
    {
        if ($this->type === 'PROXY') {
            echo $context;
        } elseif ($this->type === 'SERVER') {
            socket_write($this->stream, $context);
        }
    }

    public function __get($name)
    {
        return $this->$name;
    }
}

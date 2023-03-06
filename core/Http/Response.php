<?php
/*
 * @Author: cclilshy cclilshy@163.com
 * @Date: 2023-03-04 13:04:53
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: jingnigg@gmail.com, All Rights Reserved.
 */

namespace core\Http;

use core\Ccphp\FlowController;

class Response
{
    private string $protocol = 'HTTP/1.1';
    private string $body = '';
    private string $statusText = 'OK';
    private array $headers = [];
    private int $statusCode = 200;
    private Request $request;

    private FlowController $flowController;

    public function build(FlowController $flowController)
    {
        $this->flowController = $flowController;
    }

    public function __construct()
    {
    }

    public static function init(){
        return new self;
    }

    public function initialization(Request $request){
        $this->request = $request;
        return $this;
    }

    public function setStatusCode(int $code): void
    {
        $this->statusCode = $code;
    }

    public function setBody(string $body): Response
    {
        $this->body = $body;
        $this->setHeader('Content-Length',strlen($body));
        return $this;
    }

    public function setHeader($key,$value) : Response
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function setProtocol(string $protocol): Response
    {
        // 设置协议版本号（例如HTTP/1.0、HTTP/1.1等）
        $this->protocol = $protocol;
        return $this;
    }

    public function setStatusText(string $text): Response
    {
        // 设置HTTP状态描述（例如OK、Not Found、Internal Server Error等）
        $this->statusText = $text;
        return $this;
    }

    public function getContents(): string
    {
        if ($this->request->type === 'SERVER') {
            // 生成HTTP响应报文
            $headers = 'Content-Type: text/html;charset=utf-8;';
            foreach ($this->headers as $name => $value) {
                $headers .= "{$name}: {$value}\r\n";
            }
            return "{$this->protocol} {$this->statusCode} {$this->statusText}\r\n{$headers}\r\n{$this->body}";
        } elseif ($this->request->type === 'PROXY') {
            header('HTTP/1.1 ' . $this->statusCode);
            return $this->body;
        } else {
            return '';
        }
    }

    public function send(): void
    {
        echo $this->getContents();
    }

    public function __toString()
    {
        return $this->getContents();
    }

    public function __get($name)
    {
        return $this->name;
    }
}

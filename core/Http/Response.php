<?php
/*
 * @Author: cclilshy cclilshy@163.com
 * @Date: 2023-03-06 16:48:58
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: jingnigg@gmail.com, All Rights Reserved.
 */

namespace core\Http;

class Response
{
    private $client;
    private int $statusCode;
    private array $header = array();
    private float $version;

    private string $charset = 'utf-8';
    private string $contentType = 'text/html';
    private string $body;
    private Request $request;

    public function __construct(Request $request)
    {
        $this->version = 1.1;
        $this->statusCode = 200;
        $this->header = array(
            'Server' => 'Buildphp',
            'Connection' => 'keep-alive',
            'Content-Type' => "{$this->contentType}; charset={$this->charset}",
        );
        $this->request = $request;
    }

    public function setHttpVersion(float $version): Response
    {
        $this->version = $version;
        return $this;
    }

    public function setStatusCode(int $code): Response
    {
        $this->statusCode = $code;
        return $this;
    }

    public function setChatset(string $charset): Response
    {
        $this->charset = $charset;
        return $this->setHeader('Content-Type', "{$this->contentType}; charset={$charset}");
    }

    public function setHeader(string $key, string $value): Response
    {
        $this->header[$key] = $value;
        return $this;
    }

    public function setContentType(string $type): Response
    {
        $this->contentType = $type;
        return $this->setHeader('Content-Type', "{$type}; charset={$this->charset}");
    }

    public function setBody(string $body): Response
    {
        $this->body = $body;
        return $this->setHeader('Content-Length', strlen($body));
    }

    public function setClient($client): Response
    {
        $this->client = $client;
        return $this;
    }

    public function setCookie(string $cookie): Response
    {
        return $this->setHeader('Set-Cookie', $cookie);
    }

    public function send()
    {
       
        socket_write($this->client, $this);
    }

    public function __toString(): string
    {
        if ($this->request->type === 'PROXY') {
            header("HTTP/{$this->version} {$this->statusCode}");
            foreach ($this->header as $key => $value) {
                header("{$key}: {$value}");
            }
            $header = '';
        } elseif ($this->request->type === 'SERVER') {
            $header = "HTTP/{$this->version} {$this->statusCode} OK\r\n";
            foreach ($this->header as $key => $value) {
                $header .= "{$key}: {$value}\r\n";
            }
            $header .= "\r\n";
        }

        return $header . $this->body;
    }

    public function __get($name)
    {
        return $this->$name;
    }
}

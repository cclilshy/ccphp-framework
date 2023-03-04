<?php

namespace core\Http;

class Response
{
    public static Response $response;
    private static string $env = '';
    private string $protocol = 'HTTP/1.1';
    private int $statusCode = 200;
    private string $statusText = 'OK';
    private array $headers = [];
    private string $body = '';

    public static function init(): Response
    {
        return self::load();
    }

    public static function load(): Response
    {
        return self::$response = new self;
    }

    public static function get(): Response
    {
        return self::$response;
    }

    public static function setEnv(string $env): void
    {
        self::$env = $env;
    }

    public static function header(string $name, string $value): void
    {
        self::$response->setHeader($name, $value);
    }

    public function setHeader(string $name, string $value): void
    {
        // 设置响应头字段
        $this->headers[$name] = $value;
    }

    public static function return(string $content, int $httpCode = 200): void
    {
        self::$response->setStatusCode($httpCode);
        self::$response->setBody($content);
        self::$response->end();
    }

    public function setStatusCode(int $code): void
    {
        // 设置HTTP状态码（例如200、404、500等）
        $this->statusCode = $code;
    }

    public function setBody(string $body): void
    {
        // 设置响应体内容
        $this->body = $body;
        $this->setHeader('Content-Length', strlen($body));
    }

    public function end()
    {
        switch (Request::type()) {
            case 'CGI':
                echo $this;
                break;
            case 'SOCKET':
                return $this;
        }
    }

    public function setProtocol(string $protocol): void
    {
        // 设置协议版本号（例如HTTP/1.0、HTTP/1.1等）
        $this->protocol = $protocol;
    }

    public function setStatusText(string $text): void
    {
        // 设置HTTP状态描述（例如OK、Not Found、Internal Server Error等）
        $this->statusText = $text;
    }

    public function __toString()
    {
        return $this->getContents();
    }

    public function getContents(): string
    {
        if (Request::type() === 'CCPHP') {
            // 生成HTTP响应报文
            $headers = 'Content-Type: text/html;charset=utf-8;';
            foreach ($this->headers as $name => $value) {
                $headers .= "{$name}: {$value}\r\n";
            }
            return "{$this->protocol} {$this->statusCode} {$this->statusText}\r\n{$headers}\r\n{$this->body}";
        } else {
            header('HTTP/1.1 ' . $this->statusCode);
        }
        return $this->body;
    }

    public function __get($name)
    {
        return $this->name;
    }
}

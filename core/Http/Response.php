<?php

namespace core\Http;

class Response
{
    private $protocol = 'HTTP/1.1';
    private $statusCode = 200;
    private $statusText = 'OK';
    private $headers = [];
    private $body = '';

    public static Response $response;

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

    public static function header(string $name, string $value)
    {
        self::$response->setHeader($name, $value);
    }

    public static function return(string $content, int $httpCode = 200)
    {
        self::$response->setStatusCode($httpCode);
        self::$response->setBody($content);
        self::$response->end();
    }

    public function setProtocol(string $protocol)
    {
        // 设置协议版本号（例如HTTP/1.0、HTTP/1.1等）
        $this->protocol = $protocol;
    }

    public function setStatusCode(int $code)
    {
        // 设置HTTP状态码（例如200、404、500等）
        $this->statusCode = $code;
    }

    public function setStatusText(string $text)
    {
        // 设置HTTP状态描述（例如OK、Not Found、Internal Server Error等）
        $this->statusText = $text;
    }

    public function setHeader(string $name, string $value)
    {
        // 设置响应头字段
        $this->headers[$name] = $value;
    }

    public function setBody(string $body)
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

    public function __toString()
    {
        if (Request::type() === 'SOCKET') {
            // 生成HTTP响应报文
            $headers = '';
            foreach ($this->headers as $name => $value) {
                $headers .= "{$name}: {$value}\r\n";
            }

            $message = "{$this->protocol} {$this->statusCode} {$this->statusText}\r\n{$headers}\r\n{$this->body}";
            return $message;
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

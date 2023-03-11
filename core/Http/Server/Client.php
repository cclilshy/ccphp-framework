<?php
/*
 * @Author: cclilshy cclilshy@163.com
 * @Date: 2023-03-09 22:19:01
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: jingnigg@gmail.com, All Rights Reserved.
 */

namespace core\Http\Server;

class Client
{
    private $socket;
    private string $context;
    private string $method = 'undefined';
    private string $path;
    private string $verstion;
    private string $data;
    private array $header;
    private bool $complete = false;
    private int $createTime;
    private int $faildCount;

    public function __construct($socket)
    {
        $this->socket = $socket;
    }

    /**
     * 返回写入数据是否有效
     * @param string $context
     * @return bool
     */
    public function write(string $context): bool
    {
        $this->context .= $context;
        if ($this->method === 'undefined') {
            if (str_contains($this->context, "\r\n\r\n")) {
                $_ = explode("\r\n\r\n", $this->context);
                $headerContext = $_[0];
                $bodyContext = $_[1] ?? '';

                if ($headerLines = explode("\r\n", $headerContext)) {
                    $base = array_shift($headerLines);
                    if (count($base = explode(' ', $base)) === 3) {
                        $this->method = strtoupper($base[0]);
                        $this->path = $base[1];
                        $this->verstion = $base[2];
                        foreach ($headerLines as $item) {
                            $_ = explode(':', $item);
                            $this->header[strtoupper(trim($_[0]))] = strtoupper(trim($_[1]));
                        }
                        if ($this->method === 'POST') {
                            if (!isset($this->header['CONTENT-LENGTH'])) {
                                // 无效的POST请求
                                return false;
                            } else {
                                $this->data = $bodyContext;
                                if (strlen($this->data) >= $this->header['CONTENT-LENGTH']) {
                                    // 完整的POST请求
                                    $this->complete = true;
                                    return true;
                                }
                            }
                        } elseif ($this->method === 'POST') {
                            echo 'GET完整' . PHP_EOL;
                            // GET报文完整
                            $this->complete = true;
                            return true;
                        }
                    } else {
                        // 不正确的请求方法
                        return false;
                    }
                }
            }
        } elseif ($this->method === 'POST') {
            $this->data .= $context;
            if (strlen($this->data) >= $this->header['CONTENT-LENGTH']) {
                // 完整的POST请求
                $this->complete = true;
                return true;
            }
        }

        return true;
    }

    /**
     * 返回客户端数据是否完整
     * @return bool
     */
    public function complete(): bool
    {
        if ($this->complete) {
            var_dump($this);
        }
        return $this->complete;
    }
}

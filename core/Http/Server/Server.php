<?php

namespace core\Http\Server;

use core\Http\Server\Client;
use core\Http\Request;
use core\Http\Http;
use core\Master;

/**
 * Summary of Server
 */
class Server
{
    private static $server; // 服务套接字
    private static array $tasks = array(); // 任务队列
    private static array $clients = array(); // 客户端套接字
    private static array $clientsInfo = array(); // 客户端信息

    private static function addClient($socket)
    {
        self::$clients[spl_object_hash($socket)] = array(
            'socket' => $socket,
            'context' => false,
            'header_context' => '',
            'body_context' => '',
            'method' => 'undefined',
            'path' => '',
            'verstion' => '',
            'data' => '',
            'header' => array(),
            'complete' => false,
            'createTime' => time(),
            'faildCount' => 0,
        );
    }

    private static function removeClient($socket): void
    {
        // 移除客户端
        unset(self::$clients[spl_object_hash($socket)]);
    }

    /**
     * Summary of launch
     * @return void
     */
    public static function launch(): void
    {
        Master::rouse('Http\Http');
        ini_set('default_socket_timeout', -1);
        ini_set('max_execution_time', 0);

        // 创建连接
        self::$server = socket_create(AF_INET, SOCK_STREAM, 0);
        socket_bind(self::$server, '127.0.0.1', 2222);
        socket_listen(self::$server);

        // 开始循环监听
        while (true) {
            // 监听服务端和客户端写入
            $readList = array_merge([self::$server], array_column(self::$clients, 'socket'));

            // 只监听可以写入缓冲区的客户端
            $writeList = [];

            // 监听所有客户端的异常信息
            $exceptList = array_column(self::$clients, 'socket');

            echo '开始监听' . PHP_EOL;

            sleep(1);
            if (socket_select($readList, $writeList, $exceptList, null) !== false) {
                echo '收到数据' . PHP_EOL;
                // 处理异常连接
                foreach ($exceptList as $socket) {
                    if (empty(socket_read($socket, 1024))) {
                        // 断开连接,移除客户端
                        self::removeClient($socket);
                        if($index = array_search($readList,$socket) !== false){
                            unset($readList[$index]);
                        }
                    }
                    echo '有客户端异常' . PHP_EOL;
                }

                // 处理可读消息
                foreach ($readList as $socket) {
                    // 服务端socket发来消息
                    if (self::$server === $socket) {
                        echo '有新连接' . PHP_EOL;
                        $client = socket_accept($socket);
                        self::addClient($client);

                        // 来自客户端的消息
                    } else {
                        echo '客户端发来数据' . PHP_EOL;
                        $client = self::$clients[spl_object_hash($socket)];
                        // 读取数据
                        $context = socket_read($socket, 1024);
                        $client['context'] .= $context;

                        if ($context === '') {
                            self::removeClient($socket);
                            socket_close($socket);
                        }
                        if ($client['method'] === 'undefined') {
                            if (str_contains($client['context'], "\r\n\r\n")) {
                                $_ = explode("\r\n\r\n", $client['context']);
                                $client['header_context'] = $_[0];
                                $client['body_context'] = $_[1] ?? '';
                                if ($headerLines = explode("\r\n", $client['header_context'])) {
                                    $base = array_shift($headerLines);
                                    if (count($base = explode(' ', $base)) === 3) {
                                        var_dump($base);
                                        $client['method'] = strtoupper($base[0]);
                                        $client['path'] = $base[1];
                                        $client['verstion'] = $base[2];

                                        foreach ($headerLines as $item) {
                                            $_ = explode(':', $item);
                                            $client['header'][strtoupper(trim($_[0]))] = trim($_[1] ?? '');
                                        }

                                        if ($client['method'] === 'POST') {
                                            if (!isset($client['header']['CONTENT-LENGTH'])) {
                                                self::removeClient($socket);
                                                socket_close($socket);
                                                // POST请求必须有Content-Length
                                            } else {
                                                $client['data'] = $client['body_context'];
                                                if (strlen($client['data']) >= intval($client['header']['CONTENT-LENGTH'])) {
                                                    $client['complete'] = true;
                                                }
                                            }
                                        } elseif ($client['method'] === 'GET') {
                                            $client['complete'] = true;
                                        } else {
                                            self::removeClient($socket);
                                            socket_close($socket);
                                            // 非法请求
                                        }
                                    } else {
                                        self::removeClient($socket);
                                        socket_close($socket);
                                        // 非法请求
                                    }
                                }
                            }
                        } elseif ($client['method'] === 'POST') {
                            $client['data'] .= $context;
                            if (strlen($client['data']) >= intval($client['header']['CONTENT-LENGTH'])) {
                                self::removeClient($socket);
                                $client['complete'] = true;
                            }
                        }

                        if ($client['complete'] === true) {
                            Http::build(new Request($client))->go('SERVER');
                            self::removeClient($socket);
                        }
                    }
                }
            }
        }
    }
}

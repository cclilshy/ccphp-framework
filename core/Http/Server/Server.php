<?php

namespace core\Http\Server;

use core\Master;
use core\Config;
use core\Console;
use core\Process\Process;
use core\Server\Server as BaseServer;

/**
 * Summary of Server
 */
class Server
{
    private static mixed $server; // 服务套接字
    private static array $clients = array(); // 客户端套接字
    private static array $handlers; // 消费者列表
    private static array $tasks;

    /**
     * Summary of launch
     *
     * @return void
     */
    public static function launch(): void
    {
        if ($server = BaseServer::create('HTTP_SERVER')) {
            Process::initialization();
            // Process::fork(function () use ($server) {
            Master::rouse('Http\Http');
            ini_set('default_socket_timeout', -1);
            ini_set('max_execution_time', 0);
            try {
                // 创建连接
                self::$server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
                socket_set_option(self::$server, SOL_SOCKET, SO_REUSEADDR, 1);
                socket_set_option(self::$server, SOL_SOCKET, SO_REUSEPORT, 1);

                // 监听指定HTTP端口
                socket_bind(self::$server, '127.0.0.1', Config::get('http.server_port'));
                // 监听连接
                if (socket_listen(self::$server) === false) {
                    throw new \Exception(socket_strerror(socket_last_error()));
                }
            } catch (\Exception $e) {
                echo '发生错误: ' . $e->getMessage() . PHP_EOL;
                return;
            }

            // 初始化进程管理器

            // 启动消费者
            $handlerProcessIds = array();
            for ($i = 0; $i < Config::get('http.server_handle_count'); $i++) {
                $event = EventHandler::create();
                self::$handlers[] = $event;
                $handlerProcessIds[] = $event->pid;
            }
            Process::guard();
            $server->info(['handlerProcessIds' => $handlerProcessIds, 'serverProcessId' => posix_getpid(),]);

            // 开始循环监听
            while (true) {
                // 监听服务端和客户端写入
                $readList = array_merge([self::$server], array_column(self::$clients, 'socket'));

                // 只监听可以写入缓冲区的客户端
                $writeList = [];

                // 监听所有客户端的异常信息
                $exceptList = array_column(self::$clients, 'socket');

                echo '开始监听' . PHP_EOL;

                if (socket_select($readList, $writeList, $exceptList, null) !== false) {
                    echo '收到数据' . PHP_EOL;
                    // 处理异常连接
                    foreach ($exceptList as $socket) {
                        if (empty(socket_read($socket, 1024))) {
                            // 断开连接,移除客户端
                            self::removeClient($socket);
                            $index = array_search($readList, $socket);
                            if ($index !== false) {
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
                            $context = socket_read($socket, 1);
                            // 来自消费者的消息
                            if ($context === '@') {
                                echo '是消费者消息' . PHP_EOL;
                                $content = '';
                                $_ = array();

                                while (true) {
                                    if (!isset($_[1])) {
                                        $symbol = socket_read($socket, 1);
                                        if ($symbol === '#') {
                                            if ($content === 'stop') {
                                                foreach (array_column(self::$clients, 'socket') as $item) {

                                                    self::removeClient($item);
                                                }
                                                socket_close(self::$server);
                                                exit;
                                            }
                                            $_[] = $content;
                                            $content = '';
                                            continue;
                                        }

                                        $content .= $symbol;
                                    } else {
                                        $_[] = socket_read($socket, intval($_[1]));
                                        //handler

                                        if (isset(self::$tasks[$_[0]])) {
                                            socket_write(self::$tasks[$_[0]]['socket'], $_[2]);
                                            self::$clients[$_[0]] = array('socket' => self::$tasks[$_[0]]['socket'], 'context' => false, 'header_context' => '', 'body_context' => '', 'method' => 'undefined', 'path' => '', 'version' => '', 'data' => '', 'header' => array(), 'complete' => false, 'createTime' => time(), 'failedCount' => 0,);
                                            unset(self::$tasks[$_[0]]);
                                        }
                                        break;
                                    }
                                }
                                continue;
                            }

                            // 新的客户端进入
                            $client = &self::$clients[spl_object_hash($socket)];
                            // 读取数据
                            $context .= socket_read($socket, 1024);
                            $client['context'] .= $context;

                            if ($context === '') {
                                self::removeClient($socket);
                            }
                            if ($client['method'] === 'undefined') {
                                if (str_contains($client['context'], "\r\n\r\n")) {
                                    $_ = explode("\r\n\r\n", $client['context']);
                                    $client['header_context'] = $_[0];
                                    $client['body_context'] = $_[1] ?? '';
                                    if ($headerLines = explode("\r\n", $client['header_context'])) {
                                        $base = array_shift($headerLines);
                                        if (count($base = explode(' ', $base)) === 3) {
                                            $client['method'] = strtoupper($base[0]);
                                            $client['path'] = $base[1];
                                            $client['version'] = $base[2];

                                            foreach ($headerLines as $item) {
                                                $_ = explode(':', $item);
                                                $client['header'][strtoupper(trim($_[0]))] = trim($_[1] ?? '');
                                            }

                                            if ($client['method'] === 'POST') {
                                                if (!isset($client['header']['CONTENT-LENGTH'])) {
                                                    self::removeClient($socket);
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
                                                // 非法请求
                                            }
                                        } else {
                                            self::removeClient($socket);
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
                                echo '当前客户端总数' . count(self::$clients) . PHP_EOL;
                                // 推送处理
                                self::$tasks[spl_object_hash($socket)] = $client;
                                self::$handlers[array_rand(self::$handlers)]->push(spl_object_hash($socket), $client);
                                unset(self::$clients[spl_object_hash($socket)]);
                            }
                        }
                    }
                }
            }
            // });
            // sleep(1);
            // Process::guard();
        }
    }

    public static function stop(): void
    {
        if ($server = BaseServer::load('HTTP_SERVER')) {
            $pid = pcntl_fork();
            if ($pid > 0) {
                declare (ticks=1);
                pcntl_signal(SIGCHLD, function () use ($pid) {
                    pcntl_waitpid($pid, $status);
                    exit;
                }, false);
                sleep(5);
                posix_kill($pid, SIGKILL);
                $server->release();
                exit;
            }
            Process::initialization();
            $info = $server->info();
            foreach ($info['handlerProcessIds'] as $pid) {
                Process::kill($pid);
            }
            Process::kill($info['serverProcessId']);
            // 创建客户端套接字
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

            // 连接服务器
            socket_connect($socket, '127.0.0.1', Config::get('http.server_port'));

            socket_write($socket, '@stop#');
            socket_close($socket);

            $server->release();
            Console::pgreen('Http Server stop success!');
            var_dump($info);
        }
    }

    private static function removeClient($socket): void
    {
        echo '客户端' . spl_object_hash($socket) . '断开连接' . PHP_EOL;
        // 移除客户端
        socket_close($socket);
        unset(self::$clients[spl_object_hash($socket)]);
    }

    private static function addClient($socket): void
    {
        self::$clients[spl_object_hash($socket)] = array('socket' => $socket, 'context' => false, 'header_context' => '', 'body_context' => '', 'method' => 'undefined', 'path' => '', 'version' => '', 'data' => '', 'header' => array(), 'complete' => false, 'createTime' => time(), 'failedCount' => 0,);
    }
}

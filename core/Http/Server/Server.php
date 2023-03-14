<?php

namespace core\Http\Server;

use Exception;
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
    private static mixed $server;                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    // 服务套接字
    private static mixed $eventServer;                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              // Event套接字
    private static array $tasks          = [];                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     // 代办事项
    private static array $transfer = [];                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            // 客户端套接字
    private static array $handlerSockets = [];                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      // 消费者套接字
    private static array $handlerFifos   = [];

    /**
     * Summary of launch
     *
     * @return void
     * @throws \Exception
     */
    public static function launch(): void
    {
        if ($server = BaseServer::create('HTTP_SERVER')) {
            // Process::fork(function () use ($server) {
            Master::rouse('Http\Http');
            ini_set('default_socket_timeout', -1);
            ini_set('max_execution_time', 0);
            try {
                // 创建连接
                self::$server      = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
                self::$eventServer = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

                // 端口非互斥模式
                socket_set_option(self::$server, SOL_SOCKET, SO_REUSEADDR, 1);
                socket_set_option(self::$server, SOL_SOCKET, SO_REUSEPORT, 1);
                socket_set_option(self::$eventServer, SOL_SOCKET, SO_REUSEPORT, 1);
                socket_set_option(self::$eventServer, SOL_SOCKET, SO_REUSEPORT, 1);

                // 监听指定HTTP端口
                socket_bind(self::$server, '0.0.0.0', Config::get('http.server_port'));
                socket_bind(self::$eventServer, '127.0.0.1', 2787);

                // 监听连接
                if (!socket_listen(self::$server) || !socket_listen(self::$eventServer)) {
                    throw new Exception(socket_strerror(socket_last_error()));
                }
            } catch (Exception $e) {
                socket_close(self::$server);
                socket_close(self::$eventServer);
                Console::pdebug('发生错误: ' . $e->getMessage());
                return;
            }

            // 启动消费者
            $handlerProcessIds = [];
            try {
                if (Process::initialization()) {
                    for ($i = 0; $i < intval(Config::get('http.server_handle_count')); $i++) {
                        if ($event = EventHandler::create()) {
                            self::$handlerFifos[] = $event;
                            $handlerProcessIds[]  = $event->pid;
                        }
                    }
                    Process::guard();
                }
            } catch (\Exception $e) {
                echo $e->getMessage() . PHP_EOL;
                self::release();
                $server->release();
                return;
            }

            usleep(100000);
            $server->info(['handlerProcessIds' => $handlerProcessIds, 'serverProcessId' => posix_getpid()]);
            Console::pgreen('[HttpServer] Http Server start success!');
            // 开始循环监听
            while (true) {
                // 监听服务端和客户端写入
                $readList = array_merge([self::$server], [self::$eventServer], self::$handlerSockets, array_column(self::$transfer, 'socket'));

                // 只监听可以写入缓冲区的客户端
                $writeList = [];

                // 监听所有客户端的异常信息
                $exceptList = array_column(self::$transfer, 'socket');

                if (socket_select($readList, $writeList, $exceptList, null) !== false) {
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
                    }

                    // 处理可读消息
                    foreach ($readList as $socket) {
                        // 服务端socket发来消息
                        if (self::$server === $socket) {
                            Console::pdebug('有新连接');
                            $client = socket_accept($socket);
                            self::addClient($client);

                            // 来自客户端的消息
                        } elseif (self::$eventServer === $socket) {
                            $handler                = socket_accept($socket);
                            self::$handlerSockets[] = $handler;
                        } else {
                            Console::pdebug('客户端发来数据');
                            $socketName = spl_object_hash($socket);

                            // 取头消息
                            $context = socket_read($socket, 1);
                            if ($context === '') {
                                self::removeClient($socket);
                            }

                            if ($context === '@') {
                                Console::pdebug('是消费者消息');
                                $content = '';
                                $_       = [];

                                while (true) {
                                    if (!isset($_[1])) {
                                        $symbol = socket_read($socket, 1);
                                        if ($symbol === '#') {
                                            if ($content === 'stop') {
                                                self::release();
                                                return;
                                            }

                                            // 存如信息并清空暂存区
                                            $_[]     = $content;
                                            $content = '';
                                            continue;
                                        }

                                        $content .= $symbol;
                                    } else {
                                        $_[] = socket_read($socket, intval($_[1]));

                                        $nominator = $_[0];
                                        $context   = $_[2];

                                        if ($client = self::$tasks[$nominator] ?? null) {
                                            socket_write($client['socket'], $context);
                                            self::addClient($client['socket']);
                                            unset(self::$tasks[$nominator]);
                                        }
                                        break;
                                    }
                                }
                                continue;
                            }

                            // 新的客户端进入
                            $client = &self::$transfer[$socketName];
                            // 读取数据
                            $context           .= socket_read($socket, 1024);
                            $client['context'] .= $context;

                            if ($client['method'] === 'undefined') {
                                if (str_contains($client['context'], "\r\n\r\n")) {
                                    $_                        = explode("\r\n\r\n", $client['context']);
                                    $client['header_context'] = $_[0];
                                    $client['body_context']   = $_[1] ?? '';
                                    if ($headerLines = explode("\r\n", $client['header_context'])) {
                                        $base = array_shift($headerLines);
                                        if (count($base = explode(' ', $base)) === 3) {
                                            $client['method']  = strtoupper($base[0]);
                                            $client['path']    = $base[1];
                                            $client['version'] = $base[2];

                                            foreach ($headerLines as $item) {
                                                $_                                         = explode(':', $item);
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
                                Console::pdebug('当前客户端总数' . count(self::$transfer));
                                // 推送处理
                                self::$tasks[$socketName] = $client;
                                $handle                   = self::$handlerFifos[array_rand(self::$handlerFifos)];
                                $handle->push($socketName, $client);
                                unset(self::$transfer[$socketName]);
                            }
                        }
                    }
                }
            }
        } else {
            Console::pred('[HttpServer] Http Server may runing');
        }

    }

    /**
     * @return void
     */
    private static function release(): void
    {
        foreach (array_column(self::$transfer, 'socket') as $item) {
            self::removeClient($item);
        }
        foreach (self::$handlerFifos as $item) {
            $item->push('stop', []);
            $item->close();
        }
        socket_close(self::$server);
        socket_close(self::$eventServer);
    }

    /**
     * @param $socket
     * @return void
     */
    private static function removeClient($socket): void
    {
        Console::pdebug('客户端' . spl_object_hash($socket) . '断开连接');
        // 移除客户端
        if (self::$transfer[spl_object_hash($socket)] ?? null) {
            socket_close($socket);
            unset(self::$transfer[spl_object_hash($socket)]);
        }
    }

    /**
     * @param $socket
     * @return void
     */
    private static function addClient($socket): void
    {
        self::$transfer[spl_object_hash($socket)] = [
            'socket'         => $socket,
            'context'        => false,
            'header_context' => '',
            'body_context'   => '',
            'method'         => 'undefined',
            'path'           => '',
            'version'        => '',
            'data'           => '',
            'header'         => [],
            'complete'       => false,
            'createTime'     => time(),
            'failedCount'    => 0,
        ];
    }

    /**
     * @throws \Exception
     */
    public static function stop(): void
    {
        if ($server = BaseServer::load('HTTP_SERVER')) {
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            try {
                @$_ = socket_connect($socket, '127.0.0.1', Config::get('http.server_port'));
                if ($_) {
                    socket_write($socket, '@stop#');
                    socket_close($socket);
                } else {
                    throw new Exception('Could not connect to server');
                }
            } catch (Exception $e) {
                Console::pred('Error: ' . $e->getMessage());
            }

            usleep(10000);
            try {
                if (Process::initialization()) {
                    // 创建客户端套接字
                    $info = $server->info();
                    if (!empty($info)) {
                        foreach ($info['handlerProcessIds'] as $pid) {
                            Process::kill($pid);
                        }
                        Process::kill($info['serverProcessId']);
                    }

                }
            } catch (\Exception $e) {
                Console::pred($e->getMessage());
            }

            $server->release();
            Console::pgreen('[HttpServer] Http Server stop success!');
        } else {
            Console::pred('[HttpServer] may not runing');
        }
    }
}

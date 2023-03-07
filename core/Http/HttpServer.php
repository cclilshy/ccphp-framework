<?php
/*
 * @Author: cclilshy cclilshy@163.com
 * @Date: 2023-03-04 14:48:39
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: jingnigg@gmail.com, All Rights Reserved.
 */

namespace core\Http;

use core\Master;

class HttpServer
{
    public static function launch()
    {
        Request::setEnv('CCPHP');
        Response::setEnv('CCPHP');
        Master::rouse('Http');
        $server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_bind($server, '127.0.0.1', 2222);
        socket_listen($server);
        while ($client = socket_accept($server)) {
            $context = '';
            do {
                $context = socket_read($client, 8192);
            } while (!strpos($context, "\r\n\r\n"));
            $requestText = explode("\r\n\r\n", $context);
            $requestHeader = $requestText[0];
            $requestBody = $requestText[1];

            $headerList = explode("\r\n", $requestHeader);
            $questInfo = explode(' ', array_shift($headerList));
            $_method = $questInfo[0];
            $_path = $questInfo[1];
            $_version = $questInfo[2];
            $header = array();

            while ($item = array_shift($headerList)) {
                $slic = explode(':', $item);
                $header[trim($slic[0])] = trim($slic[1]) ?? '';
            }

            if (strtoupper($_method) === 'POST') {
                $bodyLength = intval($header['Content-Length']);
                while (strlen($requestBody) < $bodyLength) {
                    $requestBody .= socket_read($client, 8192);
                }
            }

            $originContents = $requestHeader . "\r\n\r\n" . $requestBody;
            Request::loadHttpContext($originContents);
            Master::flush();
            Http::go();
            socket_write($client, Response::get()->getContents());
        }
    }

    public static function stop()
    {
    }
}

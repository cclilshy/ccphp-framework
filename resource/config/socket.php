<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2023-02-12 14:54:54
 * @LastEditors: cclilshy jingnigg@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: cclilshy, All Rights Reserved.
 */

// Support All Classes

return [
    'onStart' => function ($socket) {
    },
    'onConnect' => function ($socket) {
        $socket->send("hello,world\n");
    },
    'onMessage' => function ($socket, $data) {
        $socket->send('you say : ' . $data);
        sleep(1000);
    },
    'onClose' => function ($socket) {
    }
];
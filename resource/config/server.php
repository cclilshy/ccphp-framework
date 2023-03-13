<?php
/*
 * @Author: cclilshy cclilshy@163.com
 * @Date: 2023-03-02 20:33:48
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: cclilshy, All Rights Reserved.
 */
return [// 启用进程树
        'tree_server'           => true,

        // 启用数据库连接池
        'database_pool'         => true,

        // 数据库连接池数量
        'database_pool_connect' => 20,

        // 一个连接最大供进程使用
        'database_pool_max'     => 20,];
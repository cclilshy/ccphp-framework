<?php
/*
 * @Author: cclilshy cclilshy@163.com
 * @Date: 2023-03-02 22:10:42
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: cclilshy, All Rights Reserved.
 */

namespace core\Database;

class ConnectDispatcher
{
    private $connect;
    private $dispatcher;

    public function __construct($connect)
    {
        $this->connect = $connect;
    }

    public function release(): void
    {

    }
}

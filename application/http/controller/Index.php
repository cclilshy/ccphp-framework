<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-04 11:34:28
 * @LastEditors: cclilshy jingnigg@163.com
 * @FilePath: /ccphp/application/http/controller/Index.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace http\controller;

use core\View;

class Index
{
    public function index(): string
    {
        return View::template();
    }

    public function hello($name): void
    {
        echo 'hello,' . $name;
    }
}

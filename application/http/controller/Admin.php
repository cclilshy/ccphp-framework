<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-03 23:03:42
 * @LastEditors: cclilshy jingnigg@163.com
 * @FilePath: /ccphp/application/http/controller/Admin.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace http\controller;

use core\View;

class Admin
{
    public function index(): string
    {
        return View::template();
    }
}
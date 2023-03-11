<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-04 11:34:28
 * @LastEditors: cclilshy cclilshy@163.com
 * @FilePath: /ccphp/application/http/controller/Index.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace http\controller;

use core\Http\Controller;

class Index extends Controller
{

    public function __construct(\stdClass $base)
    {
        parent::__construct($base);
    }

    public function index(): string
    {
        return $this;
    }

    public function hello(string $name): string
    {
        return 'hello,' . $name;
    }
}

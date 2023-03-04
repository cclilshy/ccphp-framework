<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-25 19:05:33
 * @LastEditors: cclilshy jingnigg@163.com
 * @FilePath: /ccphp/vendor/core/Ccphp.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace core;

// Entry Class For Starting The Framework

class Ccphp
{
    public static function init(): void
    {
        Master::rouse('Route','Cache','Config','Log','Ccphp\Launch');
    }
}
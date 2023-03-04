<?php

/** @noinspection ALL */
/** @noinspection ALL */

/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-06 16:14:15
 * @LastEditors: cclilshy cclilshy@163.com
 * @FilePath: /ccphp/vendor/core/Template.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace core;

use function htmlspecialchars;
use function is_string;
use function substr;

// 应用层级, 用于模板的解析, 以及模板的渲染

class Template
{
    private static \core\Template\Plaster $plaster;

    public static function init(): void
    {
        self::load();
    }

    public static function load(): void
    {
        self::$plaster = new \core\Template\Plaster;
    }

    public static function __callStatic($name, $arguments): mixed
    {
        return self::$plaster->$name(...$arguments);
    }
}

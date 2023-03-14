<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-03 23:17:34
 * @LastEditors: cclilshy jingnigg@163.com
 * @FilePath: /ccphp/vendor/core/Config.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace core;

use stdClass;

class Config
{
    protected static array $config = array();

    public static function std(string $name): stdClass
    {
        return (object)self::get($name);
    }

    public static function get(string $name)
    {
        $reqConstruct = explode('.', $name);
        $rest         = self::$config;
        for ($i = 0; $i < count($reqConstruct); $i++) {
            $rest = $rest[$reqConstruct[$i]] ?? null;
        }
        return $rest;
    }

    public static function initialization(): void
    {
        $files = scandir(CONF_PATH);
        foreach ($files as $item) {
            if ($item === '.' || $item === '..')
                continue;
            self::$config[pathinfo($item)['filename']] = require CONF_PATH . FS . $item;
        }

    }

    public static function set(string $name, $value)
    {
        $reqConstruct = explode('.', $name);
        $rest         = &self::$config;
        for ($i = 0; $i < count($reqConstruct); $i++) {
            $rest = &$rest[$reqConstruct[$i]];
        }
        $rest = $value;
        return $value;
    }

    public static function all(): array
    {
        return self::$config;
    }

    public static function env(string $name): void
    {

    }
}
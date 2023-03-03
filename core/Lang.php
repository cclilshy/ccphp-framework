<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2023-02-03 00:07:44
 * @LastEditors: cclilshy jingnigg@163.com
 * @Description: My house
 * Copyright (c) 2023 by cclilshy email: cclilshy@163.com, All Rights Reserved.
 */

/**
 * Write A Php Language Package
 */

namespace core;

use Exception;

class Lang
{
    /**
     * 语言包
     * @var array
     */
    private static array $lang = [];

    public static function select(string $language = 'zh-cn'): bool
    {
        return true;
    }

    /**
     * 获取语言包
     * @param string $name 语言包名称
     * @param string $lang 语言包
     * @return string
     * @throws Exception
     */
    public static function get(string $name, string $lang = ''): string
    {
        $lang = $lang ?: config('default_lang');
        $lang = self::load($lang);
        return $lang[$name] ?? $name;
    }

    /**
     * 加载语言包
     * @param string $lang 语言包名称
     * @return array
     * @throws Exception
     */
    public static function load(string $lang): array
    {
        if (isset(self::$lang[$lang])) {
            return self::$lang[$lang];
        }
        $file = APP_PATH . 'lang/' . $lang . '.php';
        if (is_file($file)) {
            $lang = include $file;
            self::$lang[$lang] = $lang;
            return $lang;
        } else {
            throw new Exception("找不到语言包文件：{$file}");
        }
    }
}

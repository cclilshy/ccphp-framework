<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-04 00:12:34
 * @LastEditors: cclilshy cclilshy@163.com
 * @FilePath: /ccphp/vendor/core/View.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace core;

// 它只负责根据设定输出类型, 修改Header, 并返回原始内容

class View
{
    protected static string $content;
    protected static array $data = array();

    public static function template($template = null, $data = []): string
    {
        if (is_array($template) || $template === null) {
            $templateFileName = Http::getMapAttribute('functionName');
            $templateFileName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $templateFileName));
            self::$content = file_get_contents(
                TMP_PATH . FS
                    . strtolower(Http::getMapAttribute('controllerName')) . FS
                    . $templateFileName . '.'
                    . Config::get('http.template_extension')
            );
            self::$data = $template ?? [];
        } else {
            self::$content = file_get_contents(TMP_PATH . FS . $template . '.' . Config::get('http.template_extension'));
            self::$data = $data;
        }
        foreach (self::$data as $key => $value) {
            Template::define($key, $value);
        }
        header('content-type: text/html');
        return Template::apply(self::$content);
    }

    public static function define($name, $value): void
    {
        Template::define($name, $value);
    }

    public static function html(string $code): string
    {
        header('content-type: text/html');
        return $code;
    }

    public static function json($data): string
    {
        header('content-type:application/json');
        return is_array($data) ? json_encode($data) : $data;
    }

    public static function xml($xml): string
    {
        header('content-type:application/xml');
        return $xml;
    }

    public static function javascript(string $code): string
    {
        header('content-type: application/x-javascript');
        return $code;
    }

    public static function css(string $code): string
    {
        header('content-type: text/html');
        return $code;
    }
}

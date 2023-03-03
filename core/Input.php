<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2023-01-04 00:33:01
 * @LastEditors: cclilshy cclilshy@163.com
 * @FilePath: /ccphp/vendor/core/Input.php
 * @Description: My house
 * Copyright (c) 2023 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace core;

use core\Ccphp\Launch;
use core\Http\Request;

// Load The Level Record The Input Of The User Request And Provide The Corresponding Method

class Input
{
    private static $get;
    private static $post;

    public static function init(): void
    {
        self::load();
    }

    public static function load(): void
    {
        Input::$get = Request::$request->get;
        Input::$post = Request::$request->post;
        Launch::record('input');
    }

    /** @noinspection DuplicatedCode */
    public static function get(string $key = null)
    {
        if ($key === null) {
            return Input::$get;
        } else {
            $index = explode('.', $key);
            $current = Input::$get;
            foreach ($index as $item) {
                if (isset($current[$item])) {
                    $current = $current[$item];
                } else {
                    return null;
                }
            }
            return $current;
        }
    }

    /** @noinspection DuplicatedCode */
    public static function post(string $key = null)
    {
        if ($key === null) {
            return Input::$post;
        } else {
            $index = explode('.', $key);
            $current = Input::$post;
            foreach ($index as $item) {
                if (isset($current[$item])) {
                    $current = $current[$item];
                } else {
                    return null;
                }
            }
            return $current;
        }
    }

    public static function set(string $type, string $key, $value): void
    {
        Input::${$type}[$key] = $value;
        Launch::record('input');
    }
}

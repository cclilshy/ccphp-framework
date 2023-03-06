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

use core\Http\Request;

// Load The Level Record The Input Of The User Request And Provide The Corresponding Method

class Input
{
    private $get;
    private $post;
    private static Input $input;

    public static function load(array $get, array $post)
    {
        return self::$input = new self($get, $post);
    }

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([self::$input, $name], $arguments);
    }

    public function  __construct(array $get, array $post)
    {
        $this->get = $get;
        $this->post = $post;
    }
    /** @noinspection DuplicatedCode */
    public static function get(string $key = null)
    {
        if ($key === null) {
            return Input::$input->get;
        } else {
            $index = explode('.', $key);
            $current = Input::$input->get;
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
            return Input::$input->post;
        } else {
            $index = explode('.', $key);
            $current = Input::$input->post;
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
        Input::$input->{$type}[$key] = $value;
    }
}

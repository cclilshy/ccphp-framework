<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-04 00:19:25
 * @LastEditors: cclilshy jingnigg@163.com
 * @FilePath: /ccphp/vendor/core/Session.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace core;

// 它在Http请求的生命周期内, 用于存储用户的数据, 并提供对应的方法
// 它不应该在启动时初始化, 而是在第一次使用时初始化

class Session
{
    protected static string $PHPSESSID;

    public static function init(): void
    {
        if (!session_id()) @ session_start() && self::$PHPSESSID = session_id();
    }

    public static function set(string $key, $value = null): bool
    {
        if (self::$PHPSESSID) {
            if ($value !== null) {
                $_SESSION[self::$PHPSESSID][$key] = $value;
            } else {
                unset($_SESSION[self::$PHPSESSID][$key]);
            }
            return true;
        }
        return false;
    }

    public static function get(string $key, $value = null)
    {
        if (self::$PHPSESSID && isset($_SESSION[self::$PHPSESSID][$key])) {
            $content = $_SESSION[self::$PHPSESSID][$key];
        } else {
            $content = null;
        }
        return $content ?? $value;
    }

    public static function id(): string
    {
        return self::$PHPSESSID;
    }
}
<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-04 00:19:25
 * @LastEditors: cclilshy cclilshy@163.com
 * @FilePath: /ccphp/vendor/core/Session.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace core\Http;

// 它在Http请求的生命周期内, 用于存储用户的数据, 并提供对应的方法
// 它不应该在启动时初始化, 而是在第一次使用时初始化
// 用户层应用，应当实例化
// 目前只支持Proxy形式加载


class Session
{
    // SessionID
    protected string $PHPSESSID;

    /**
     * 以Proxy形式加载时初始化
     *
     * @return void
     */
    public function __construct()
    {
        if (!session_id())
            @session_start() && $this->PHPSESSID = session_id();
    }

    /**
     * @param string $key
     * @param        $value
     * @return bool
     */
    public function set(string $key, $value = null): bool
    {
        if ($this->PHPSESSID) {
            if ($value !== null) {
                $_SESSION[$this->PHPSESSID][$key] = $value;
            } else {
                unset($_SESSION[$this->PHPSESSID][$key]);
            }
            return true;
        }
        return false;
    }

    /**
     * @param string $key
     * @param        $value
     * @return mixed|null
     */
    public function get(string $key, $value = null)
    {
        if ($this->PHPSESSID && isset($_SESSION[$this->PHPSESSID][$key])) {
            $content = $_SESSION[$this->PHPSESSID][$key];
        } else {
            $content = null;
        }
        return $content ?? $value;
    }

    /**
     * 获取ID
     *
     * @return string
     */
    public function id(): string
    {
        return $this->PHPSESSID;
    }
}

<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2023-01-05 20:29:29
 * @LastEditors: cclilshy cclilshy@163.com
 * @FilePath: /ccphp/vendor/core/Log.php
 * @Description: My house
 * Copyright (c) 2023 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace core;

use core\Ccphp\Launch;

// This Class Will Only Be Called In Effective Requests For Recording Logs 
// This Class Runs On The Last Layer Of The Loading Layer And Records Before 
// The User Request The Basic Attributes Of The Record Are Request Type Request Entry 
// Request Method Request Parameter User Information Request Data In The Route Class 
// The Initialization Method Will Be Called After Each Request Which Is Used To Reset The Log File

class Log
{
    private static $logFile;
    private static $env = '';
    private static $constant = [];

    public static function init(): void
    {
        self::$logFile = fopen(RES_PATH .'/logs/' . date("Y-m-d", time()) . '-initial' . '.log', 'a+');
    }

    public static function load(): void
    {
        self::$constant = [];
    }

    public static function setConstant(array $constant): void
    {
        self::$constant = $constant;
    }

    public static function setEnv(string $env): void
    {
        self::$env = $env;
    }

    public static function record($params = array()): bool
    {
        $nowDate = date("Y-m-d H:i:s", time());
        
        $content = '[' . self::$env . ']' . '[' . $nowDate . ']' . PHP_EOL;
        foreach (array_merge(self::$constant,$params) as $key => $value) {
            $content .= '[' . $key . ']' . $value . PHP_EOL;
        }
        // switch ($env) {
        //     case 'http':
        //         $params = array_merge(Input::get(), Input::post());
        //         $userInfo = "{$_SERVER['REMOTE_ADDR']}({$_SERVER['HTTP_USER_AGENT']})" . PHP_EOL . "[SESSIONID] " . Session::id();
        //         break;
        //     case 'cli':
        //         $params = Console::argv();
        //         $userInfo = get_current_user();
        //         break;
        // }

        return fwrite(Log::$logFile , $content. '==========='.PHP_EOL);
    }
}

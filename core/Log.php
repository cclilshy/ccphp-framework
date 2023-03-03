<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2023-01-05 20:29:29
 * @LastEditors: cclilshy jingnigg@163.com
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
    private static $env;
    private static $entrance;
    private static $guide;

    public static function init(): void
    {
        Log::$env = Launch::getPhpEnv();
        self::reset();
    }

    public static function reset(): void
    {
        $entrance = Route::entrance();
        Log::$entrance = $entrance[0];
        Log::$guide = $entrance[1];
        Log::$logFile = fopen(RES_PATH . FS . 'logs' . FS . 'ccphp-' . date("Y-m-d", time()) . '.log', 'a+');
    }

    public static function record(string $data = ''): bool
    {
        $env = Log::$env;
        $entrance = Log::$entrance;
        $guide = Log::$guide;
        $nowDate = date("Y-m-d H:i:s", time());

        switch ($env) {
            case 'http':
                $params = array_merge(Input::get(), Input::post());
                $userInfo = "{$_SERVER['REMOTE_ADDR']}({$_SERVER['HTTP_USER_AGENT']})" . PHP_EOL . "[SESSIONID] " . Session::id();
                break;
            case 'cli':
                $params = Console::argv();
                $userInfo = get_current_user();
                break;
        }

        $params = json_encode($params);
        $functionName = is_callable($guide->functionName) ? 'callable' : $guide->functionName;
        $content = "[$env] $nowDate \"$entrance\" "
            . PHP_EOL . "[ACTION] $guide->className::$functionName ($params)"
            . PHP_EOL . "[USER] {$userInfo}"
            . PHP_EOL . "[DATA] {$data}"
            . PHP_EOL . '------------------------------------------------------' . PHP_EOL;

        return fwrite(Log::$logFile, $content);
    }
}

<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-04 00:42:32
 * @LastEditors: cclilshy cclilshy@163.com
 * @FilePath: /ccphp/vendor/core/Console.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace core;

use core\Route\Route;

class Console
{
    public const RESERVED = ['help', 'list', 'run'];
    private static array $commands = [];
    private static array $argv;

    /**
     * @return \core\Console
     */
    public static function initialization(): Console
    {
        $list = Route::consoles();
        foreach ($list as $key => $item) {
            $describe       = call_user_func([$item, 'register']);
            self::$commands = array_merge(self::$commands, [$key => $describe]);
        }
        return new self();
    }

    /**
     * @return array
     */
    public static function argv(): array
    {
        return self::$argv;
    }

    /**
     * @param string $content
     * @return void
     */
    public static function pgreen(string $content): void
    {
        self::printn("\033[32m{$content}\033[0m");
    }

    /**
     * @param string $content
     * @return void
     */
    public static function printn(string $content): void
    {
        echo $content . PHP_EOL;
    }

    /**
     * @return void
     */
    public static function pdebug(): void
    {
        if (!Config::get('system.debug')) {
            return;
        }
        $args    = func_get_args();
        $content = '';
        foreach ($args as $arg) {
            if (is_array($arg) || is_object($arg)) {
                $content .= json_encode($arg, JSON_UNESCAPED_UNICODE) . ',';
            } else {
                $content .= $arg . ',';
            }
        }

        //        self::pred('[DEBUG][' . date('H:i:s') . ']' . $content);
        self::printn("\033[33m[DEBUG][" . date('H:i:s') . "]{$content}\033[0m");
    }

    /**
     * @param string $content
     * @return void
     */
    public static function pred(string $content): void
    {
        self::printn("\033[31m{$content}\033[0m");
    }

    /**
     * @return void
     */
    public function run(): void
    {
        global $argc;
        global $argv;

        $option     = $argv[1] ?? 'help';
        $map        = Route::guide('console', $option);
        self::$argv = $argv;
        if ($map !== null) {
            array_shift($argv);
            $map->run($argv, $this);
        } elseif ($option === 'help' || $option === 'list') {
            self::printn("\033[32mCCPHP is successfully initialized. Procedure \033[0m");
            self::brief('list', 'look commands');
            self::brief('help', 'See more help');
            self::brief('run', 'mini server');
            foreach (self::$commands as $key => $item)
                self::brief($key, $item);
        }
    }

    /**
     * @param string $title
     * @param string $content
     * @return void
     */
    public static function brief(string $title, string $content): void
    {
        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
        self::printn("\t\033[34m{$title}\t\033[0m \t\t\033[37m {$content} \033[0m");
    }
}

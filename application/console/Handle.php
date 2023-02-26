<?php

namespace console;

use core\Thread;

class Handle
{
    public static function register(): string
    {
        return 'You can use ccphp happily';
    }

    public function main($argv, $console): void
    {
        $console::printn("hello,world");
    }
}
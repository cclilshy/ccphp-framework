<?php

namespace console;

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
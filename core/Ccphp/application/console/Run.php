<?php

namespace core\console;

class Run
{
    public function register(): string
    {
        return 'system server';
    }

    public function main($argv, $console): void
    {
        shell_exec('php -S 0.0.0.0:8080 -t ' . HTTP_PATH . FS . 'public');
    }
}
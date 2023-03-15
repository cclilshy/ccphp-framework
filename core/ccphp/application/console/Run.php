<?php

namespace core\ccphp\application\console;

class Run
{
    /**
     * @return string
     */
    public function register(): string
    {
        return 'system server';
    }

    /**
     * @param $argv
     * @param $console
     * @return void
     */
    public function main($argv, $console): void
    {
        shell_exec('php -S 0.0.0.0:8080 -t ' . HTTP_PATH . FS . 'public');
    }
}
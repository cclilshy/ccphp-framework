<?php

namespace core\Process\interface;

interface IPC
{
    /**
     * @param callable    $observer
     * @param mixed|null  $space
     * @param string|null $name
     * @return \core\Process\IPC|false
     */
    public static function create(callable $observer, mixed $space = null, string $name = null): IPC|false;

    /**
     * @param string    $name
     * @param bool|null $destroy
     * @return \core\Process\IPC|false
     */
    public static function link(string $name, ?bool $destroy = false): IPC|false;

    /**
     * @return mixed
     */
    public function call(): mixed;
}
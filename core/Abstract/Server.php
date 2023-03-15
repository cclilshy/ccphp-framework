<?php

namespace core\Abstract;

abstract class Server
{
    abstract public static function launch(?array $config): void;

    abstract public static function stop(): void;

    abstract public static function release(): void;

    abstract public static function close(): void;
}


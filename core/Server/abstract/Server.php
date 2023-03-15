<?php

namespace core\Server\abstract;

use core\Server\Server as ServerAbstract;

abstract class Server extends ServerAbstract
{
    public function __construct(string $name)
    {
        parent::__construct($name);
    }

    abstract public function launch(): bool;

    abstract public function stop(): void;
}
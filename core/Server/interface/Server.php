<?php

namespace core\Server\interface;

interface Server
{
    /**
     * 创建服务信息
     *
     * @param string|null $name
     * @return false|static
     */
    public static function create(?string $name): self|false;

    /**
     * 加载服务信息
     *
     * @param string|null $name
     * @return false|static
     */
    public static function load(?string $name): self|false;

    /**
     * 持久化储存消息
     *
     * @param mixed|null $data
     * @return mixed
     */
    public function info(mixed $data = null): mixed;

    /**
     * 释放服务信息
     *
     * @return void
     */
    public function release(): void;
}
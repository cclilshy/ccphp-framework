<?php

namespace core\interface;

interface Module
{
    /**
     * 允许初始化不返回
     *
     * @return self|null
     */
    public static function initialization(): self|null;
}


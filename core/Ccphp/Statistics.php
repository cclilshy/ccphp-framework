<?php
/*
 * @Author: cclilshy cclilshy@163.com
 * @Date: 2023-03-04 03:14:33
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: jingnigg@gmail.com, All Rights Reserved.
 */

namespace core\Ccphp;

class Statistics
{
    private array $loadFiles = array();
    private array $posts = array();
    private array $gets = array();
    private array $sqls = array();
    private float $memory;
    private float $maxMemory;
    private float $startTime;
    private float $endTime;
    private static Statistics $statistics;
    public static function init(): Statistics
    {
        return self::$statistics = new self;
    }

    public static function load(): void
    {
        self::$statistics->reset();
    }

    public static function get(): Statistics
    {
        return self::$statistics;
    }

    public function __construct()
    {
        $this->startTime = microtime(true);
    }

    public function __get($name){
        return $this->$name;
    }

    public function record(string $type, $data)
    {
        switch ($type) {
            case 'sql':
                $this->sqls[] = $data;
                break;
        }
        $this->loadFiles = get_included_files();
        $this->endTime = microtime(true);
        $this->memory = memory_get_usage();
        $this->maxMemory = memory_get_peak_usage();
    }

    public function reset()
    {
        $this->sqls = array();
        $this->loadFiles = array();
        $this->startTime = microtime(true);
        $this->endTime = 0;
        $this->memory = 0;
        $this->maxMemory = 0;
    }
}

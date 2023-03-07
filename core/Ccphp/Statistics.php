<?php
/*
 * @Author: cclilshy cclilshy@163.com
 * @Date: 2023-03-04 03:14:33
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: jingnigg@gmail.com, All Rights Reserved.
 */

namespace core\Ccphp;

// 在协程运行时，这个类必须对象方式调用，用于统计当前进程的调用栈和信息
class Statistics
{
    public array $loadFiles = array(); // 加载的文件
    public array $posts = array(); // 所有POST内容
    public array $gets = array();  // 所有GET内容
    public array $sqls = array();  // SQL查询记录
    public float $memory;  // 内存用量
    public float $maxMemory;   // 内存峰值
    public float $startTime = 0;   // 运行时时间，在对象创建时会自动创建
    public float $endTime = 0; // 结尾时间

    public function __construct()
    {
        $this->startTime = microtime(true);
    }

    /**
     * 记录指定数据
     * @param string $type
     * @param $data
     * @return $this
     */
    public function record(string $type, $data): Statistics
    {
        switch ($type) {
            case 'sql':
                $this->sqls[] = $data;
                break;
            case 'file':
                break;
        }

        // 每次记录时会重新载入这些值
        $this->loadFiles = get_included_files();
        $this->endTime = microtime(true);
        $this->memory = memory_get_usage();
        $this->maxMemory = memory_get_peak_usage();
        return $this;
    }
}

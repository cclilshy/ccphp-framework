<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2023-02-02 11:08:58
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by cclilshy email: cclilshy@163.com, All Rights Reserved.
 */

use core\Master;

include 'configure.php';
include 'function.php';
include 'global.php';
include ROOT_PATH . '/vendor/autoload.php';
try {
    Master::rouse('Config');
} catch (Exception $e) {
}

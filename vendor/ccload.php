<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2023-02-02 11:08:58
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by cclilshy email: cclilshy@163.com, All Rights Reserved.
 */

use core\Master;

include 'autoload.php';
include 'base/configure.php';
include 'base/function.php';
include 'base/global.php';
Master::rouse('Config', 'Route', 'DB', 'Cache');
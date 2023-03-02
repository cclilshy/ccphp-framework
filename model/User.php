<?php
/*
 * @Author: cclilshy cclilshy@163.com
 * @Date: 2023-03-02 09:52:08
 * @LastEditors: cclilshy cclilshy@163.com
 * @Description: My house
 * Copyright (c) 2023 by user email: cclilshy, All Rights Reserved.
 */

namespace model;

use core\Model;
use core\DB;

class User extends Model
{
    public static function list($where = array())
    {
        return [];
        return DB::name('user')->where($where)->select()->toArray();
    }
}
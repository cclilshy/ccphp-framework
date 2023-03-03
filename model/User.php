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
        $user = DB::table('user');
        if($where){
           while($item = array_shift($where)){
                $user = $user->where($item[0],$item[1],$item[2]);
           }
        }

        return $user->get();
    }

    public static function find($uid){
        return DB::table('user')->where('id','=',$uid)->first();
    }
}
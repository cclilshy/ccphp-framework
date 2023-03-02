<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-03 23:03:42
 * @LastEditors: cclilshy cclilshy@163.com
 * @FilePath: /ccphp/application/http/controller/Admin.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

namespace http\controller;

use core\View;
use model\User;
use core\DB;

class Admin
{
    public function index(): string
    {
        return View::template();
    }

    public function general(){
        return View::template();
    }

    public function user(){
        if(!\core\Http::ajax()){
            return View::template();
        }else{
            if($list = DB::table('user')->where('id','<',50)->get()){
                return View::json(['code' => 0, 'msg' => 'success', 'data' => $list->toArray()]);
            }else{
                return View::json(['code' => -1, 'msg' => null, 'data' => []]);
            }
        }
        
    }
}
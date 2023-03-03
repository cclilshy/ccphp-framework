<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-24 17:19:03
 * @LastEditors: cclilshy cclilshy@163.com
 * @FilePath: /ccphp/application/route/http.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

use core\Route;

Route::get('admin', 'http\controller\Admin@index');
Route::get('admin/general', 'http\controller\Admin@general');
Route::get('admin/user', 'http\controller\Admin@user');
Route::get('admin/user/edit', 'http\controller\Admin@userEdit');

Route::get('admin/friend', 'http\controller\Admin@general');
Route::get('admin/message', 'http\controller\Admin@general');
Route::get('admin/group', 'http\controller\Admin@general');
Route::get('admin/group/message', 'http\controller\Admin@general');
Route::get('admin/notice', 'http\controller\Admin@general');
Route::get('admin/authority', 'http\controller\Admin@general');
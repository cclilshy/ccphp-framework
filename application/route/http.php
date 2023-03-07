<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-24 17:19:03
 * @LastEditors: cclilshy jingnigg@163.com
 * @FilePath: /ccphp/application/route/http.php
 * @Description: My house
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

use core\Route\Route;

Route::get('/', 'http\controller\Index@index');
Route::get('/func', function () {
    return 'is anonymity route';
});
Route::get('/hello/:name', 'http\controller\Index@hello', 'name');
<?php
/*
 * @Author: cclilshy jingnigg@163.com
 * @Date: 2022-12-06 20:22:11
 * @LastEditors: cclilshy jingnigg@163.com
 * @FilePath: /ccphp/configure.php
 * @Description: Public Configuration File
 * Copyright (c) 2022 by cclilshy email: jingnigg@163.com, All Rights Reserved.
 */

const UL = '_';
const FS = DIRECTORY_SEPARATOR;
const BS = '\\';
const ROOT_PATH = __DIR__ . FS . '../../';
const RES_PATH = ROOT_PATH . '/resource';
const CONF_PATH = RES_PATH . '/config';
const CERT_PATH = RES_PATH . '/cert';
const CONS_PATH = RES_PATH . '/constant';
const APP_PATH = ROOT_PATH . '/application';
const HTTP_PATH = APP_PATH . '/http';
const PUBLIC_PATH = HTTP_PATH . '/public';
const STATIC_PATH = PUBLIC_PATH . '/static';
const UPLOAD_PATH = PUBLIC_PATH . '/upload';
const CONSOLE_PATH = APP_PATH . '/console';
const CACHE_PATH = ROOT_PATH . '/cache';
const TMP_PATH = HTTP_PATH . '/template';
include ROOT_PATH . FS . 'vendor/autoload.php';

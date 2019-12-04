<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 应用入口文件 ]
*/

header('Content-Type:text/html;charset=utf-8');

// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

// 开启调试模式
define('APP_DEBUG',True);

// 定义应用目录
define('APP_PATH','./Apps/');

//关闭目录安全
define('BUILD_DIR_SECURE',false);

// 引入ThinkPHP入口文件
require './ThinkPHP/ThinkPHP.php';
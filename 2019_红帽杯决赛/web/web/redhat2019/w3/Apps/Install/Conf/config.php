<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 系统安装配置文件 ]
*/

define('INSTALL_APP_PATH', realpath('./') . '/');

return array(
    
    /* URL配置 */
    'URL_CASE_INSENSITIVE' => true, //默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL'            => 3, //URL模式 使用兼容模式安装
    'VAR_URL_PARAMS'       => '', // PATHINFO URL参数变量
    'URL_PATHINFO_DEPR'    => '/', //PATHINFO URL分割符

    'ORIGINAL_TABLE_PREFIX' => 'echo_', //默认表前缀

    //主题配置
    'DEFAULT_THEME' => 'default',

    //数据文件目录
    'DATA_DIR' =>  APP_PATH . MODULE_NAME . "/data/",

    //lock目录
    'LOCK_DATA_DIR' =>  "./data/",

    //模板替换
    'TMPL_PARSE_STRING' => array(
        '__JS__' => __ROOT__ . '/Public/' . MODULE_NAME . '/js',
        '__CSS__' => __ROOT__ . '/Public/' . MODULE_NAME . '/css',
        '__IMG__' => __ROOT__ . '/Public/' .MODULE_NAME . '/images',
        '__JQUERY__' => __ROOT__ . '/Public/jqueryui'
    ),

    //系统信息
    'SYSTEM_TITLE' => '爱客猴（echo）内容管理系统 v3.2.3 | ThinkPHP | LayerUI | Bootstrap - By CopyLian',
    'SYSTEM_KEYWORDS' => '爱客猴（echo）内容管理系统 v3.2.3 | ThinkPHP | LayerUI | Bootstrap - By CopyLian',
    'SYSTEM_DESCRIPTION' => '爱客猴（echo）内容管理系统 v3.2.3 版本，基于国内流行的ThinkPHP3.2.3框架研发，UI插件采用简洁、直观、强悍的Bootstrap3.3.5前端开发框架以及口碑极佳的web弹层组件LayerUI，全新的设计理念，带来更舒爽的体验。',
    'SYSTEM_NAME' => '爱客猴（echo）内容管理系统 v3.2.3',
    'SYSTEM_COPYRIGHT' => '版权所有 (c) 2015 ~ 2016，www.aikehou.com 保留所有权利。',
    'SYSTEM_URL' => '<a href="http://wwww.aikehou.com/" target="_blank">http://wwww.aikehou.com</a>',
    'SYSTEM_SHORTNAME' => '爱客猴',
);
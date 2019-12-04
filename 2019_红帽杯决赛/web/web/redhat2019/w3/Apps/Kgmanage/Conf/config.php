<?php
return array(
	//设置模板替换变量
	'TMPL_PARSE_STRING' => array(
		'__IMG__' => __ROOT__ . "/Public/" . MODULE_NAME . "/images",
		'__CSS__' => __ROOT__ . "/Public/" . MODULE_NAME . "/css",
		'__JS__'  => __ROOT__ . "/Public/" . MODULE_NAME . "/js",
        '__JQUERY__' => __ROOT__ . '/Public/jqueryui',
        '__UEDITOR_PATH__' => APP_PATH . 'Api/Ueditor'
	),

	//auth权限配置
	'AUTH_CONFIG' => array(
        'AUTH_ON'           => true,                // 认证开关
        'AUTH_TYPE'         => 1,                   // 认证方式，1为实时认证；2为登录认证。
        'AUTH_GROUP'        => C("DB_PREFIX").'auth_group',        // 用户组数据表名
        'AUTH_GROUP_ACCESS' => C("DB_PREFIX").'auth_group_access', // 用户-用户组关系表
        'AUTH_RULE'         => C("DB_PREFIX").'auth_rule',         // 权限规则表
        'AUTH_USER'         => C("DB_PREFIX").'adminuser'          // 用户信息表
    ),
    //超级管理员id,拥有全部权限,只要用户uid在这个角色组里的,就跳出认证.可以设置多个值,如array('1','2','3')
    'ADMINISTRATOR' => array(1),
    //不需要认证的规则
    'NO_AUTH_RULES'=>array(
    	'Kgmanage/Index/index', //后台首页
        'Kgmanage/Content/getCateList', //获取侧栏栏目
        'Kgmanage/Content/refreshCate', //更新侧栏栏目
        'Kgmanage/Cycle/getCateList', //回收站获取侧栏栏目
        'Kgmanage/Cycle/refreshCate', //回收站更新侧栏栏目
        'Kgmanage/Uploadfile/editorUploadfile', //ueditor编辑器文件上传（水印）
        'Kgmanage/Linkagemenu/getMenulist', //联动菜单ajax获取菜单
        'Kgmanage/Order/notify_url', //订单退款异步方法
    ),

    //设置后台超级管理员id
    'SUPERADMIN' => array(1),

    //设置多语言
    'LANG_SWITCH_ON' => true,
    'LANG_AUTO_DETECT' => true, // 自动侦测语言 开启多语言功能后有效
    'LANG_LIST'        => 'zh-cn', // 允许切换的语言列表 用逗号分隔
);
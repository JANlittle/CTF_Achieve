<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2016.06.15 ]
* Description [ 站点id为：1 的登录接口配置文件 ]
*/

return array(
	//新浪微博配置
	'THINK_SDK_SINA' => array(
		'APP_KEY' => '1833697893', //应用注册成功后分配的 APP ID
		'APP_SECRET' => 'f80b996a7a0a33f508e9016a01f95ecd', //应用注册成功后分配的KEY
		'APP_STATUS' => 1, //应用状态
		'APP_NAME' => '新浪微博', //应用名称
		'CALLBACK' => 'http://echo.aikehou.com/Members/callback/type/sina', //应用回调地址
	),

	//腾讯QQ配置
	'THINK_SDK_QQ' => array(
		'APP_KEY' => '101311363', //应用注册成功后分配的 APP ID
		'APP_SECRET' => 'de7bf975fc45fc4683100703a866917e', //应用注册成功后分配的KEY
		'APP_STATUS' => 1, //应用状态
		'APP_NAME' => '腾讯QQ', //应用名称
		'CALLBACK' => 'http://echo.aikehou.com/Members/callback/type/qq', //应用回调地址
	),

	//腾讯微博配置
	'THINK_SDK_TENCENT' => array(
		'APP_KEY' => '1495919877', //应用注册成功后分配的 APP ID
		'APP_SECRET' => '9e3f46e872c9f9c6393323d73488b355', //应用注册成功后分配的KEY
		'APP_STATUS' => 1, //应用状态
		'APP_NAME' => '腾讯微博', //应用名称
		'CALLBACK' => 'http://echo.aikehou.com/Members/callbak/type/tencent', //应用回调地址
	),

	//网易微博配置
	'THINK_SDK_T163' => array(
		'APP_KEY' => '1495919877', //应用注册成功后分配的 APP ID
		'APP_SECRET' => '9e3f46e872c9f9c6393323d73488b355', //应用注册成功后分配的KEY
		'APP_STATUS' => 1, //应用状态
		'APP_NAME' => '网易微博', //应用名称
		'CALLBACK' => 'http://echo.aikehou.com/Members/callback/type/t163', //应用回调地址
	),

	//人人网配置
	'THINK_SDK_RENREN' => array(
		'APP_KEY' => '1495919877', //应用注册成功后分配的 APP ID
		'APP_SECRET' => '9e3f46e872c9f9c6393323d73488b355', //应用注册成功后分配的KEY
		'APP_STATUS' => 1, //应用状态
		'APP_NAME' => '人人网', //应用名称
		'CALLBACK' => 'http://project.aikehou.com/Members/callback/type/renren', //应用回调地址
	),

	//360配置
	'THINK_SDK_X360' => array(
		'APP_KEY' => '1495919877', //应用注册成功后分配的 APP ID
		'APP_SECRET' => '9e3f46e872c9f9c6393323d73488b355', //应用注册成功后分配的KEY
		'APP_STATUS' => 1, //应用状态
		'APP_NAME' => '360', //应用名称
		'CALLBACK' => 'http://project.aikehou.com/Members/callback/type/x360', //应用回调地址
	),

	//豆瓣网配置
	'THINK_SDK_DOUBAN' => array(
		'APP_KEY' => '1495919877', //应用注册成功后分配的 APP ID
		'APP_SECRET' => '9e3f46e872c9f9c6393323d73488b355', //应用注册成功后分配的KEY
		'APP_STATUS' => 1, //应用状态
		'APP_NAME' => '豆瓣网', //应用名称
		'CALLBACK' => 'http://project.aikehou.com/Members/callback/type/douban', //应用回调地址
	),

	//Github配置
	'THINK_SDK_GITHUB' => array(
		'APP_KEY' => '1495919877', //应用注册成功后分配的 APP ID
		'APP_SECRET' => '9e3f46e872c9f9c6393323d73488b355', //应用注册成功后分配的KEY
		'APP_STATUS' => 1, //应用状态
		'APP_NAME' => 'Github', //应用名称
		'CALLBACK' => 'http://project.aikehou.com/Members/callback/type/github', //应用回调地址
	),

	//Google配置
	'THINK_SDK_GOOGLE' => array(
		'APP_KEY' => '1495919877', //应用注册成功后分配的 APP ID
		'APP_SECRET' => '9e3f46e872c9f9c6393323d73488b355', //应用注册成功后分配的KEY
		'APP_STATUS' => 1, //应用状态
		'APP_NAME' => 'Google', //应用名称
		'CALLBACK' => 'http://project.aikehou.com/Members/callback/type/google', //应用回调地址
	),

	//MSN配置
	'THINK_SDK_MSN' => array(
		'APP_KEY' => '1495919877', //应用注册成功后分配的 APP ID
		'APP_SECRET' => '9e3f46e872c9f9c6393323d73488b355', //应用注册成功后分配的KEY
		'APP_STATUS' => 1, //应用状态
		'APP_NAME' => 'MSN', //应用名称
		'CALLBACK' => 'http://project.aikehou.com/Members/callback/type/msn', //应用回调地址
	),

	//点点网配置
	'THINK_SDK_DIANDIAN' => array(
		'APP_KEY' => '1495919877', //应用注册成功后分配的 APP ID
		'APP_SECRET' => '9e3f46e872c9f9c6393323d73488b355', //应用注册成功后分配的KEY
		'APP_STATUS' => 1, //应用状态
		'APP_NAME' => '点点网', //应用名称
		'CALLBACK' => 'http://project.aikehou.com/Members/callback/type/diandian', //应用回调地址
	),

	//淘宝网配置
	'THINK_SDK_TAOBAO' => array(
		'APP_KEY' => '1495919877', //应用注册成功后分配的 APP ID
		'APP_SECRET' => '9e3f46e872c9f9c6393323d73488b355', //应用注册成功后分配的KEY
		'APP_STATUS' => 1, //应用状态
		'APP_NAME' => '淘宝网', //应用名称
		'CALLBACK' => 'http://project.aikehou.com/Members/callback/type/taobao', //应用回调地址
	),

	//百度配置
	'THINK_SDK_BAIDU' => array(
		'APP_KEY' => '1495919877', //应用注册成功后分配的 APP ID
		'APP_SECRET' => '9e3f46e872c9f9c6393323d73488b355', //应用注册成功后分配的KEY
		'APP_STATUS' => 1, //应用状态
		'APP_NAME' => '百度', //应用名称
		'CALLBACK' => 'http://project.aikehou.com/Members/callback/type/sina', //应用回调地址
	),

	//开心网配置
	'THINK_SDK_KAIXIN' => array(
		'APP_KEY' => '1495919877', //应用注册成功后分配的 APP ID
		'APP_SECRET' => '9e3f46e872c9f9c6393323d73488b355', //应用注册成功后分配的KEY
		'APP_STATUS' => 1, //应用状态
		'APP_NAME' => '开心网', //应用名称
		'CALLBACK' => 'http://project.aikehou.com/Members/callback/type/kaixin', //应用回调地址
	),

	//搜狐微博配置
	'THINK_SDK_SOHU' => array(
		'APP_KEY' => '1495919877', //应用注册成功后分配的 APP ID
		'APP_SECRET' => '9e3f46e872c9f9c6393323d73488b355', //应用注册成功后分配的KEY
		'APP_STATUS' => 1, //应用状态
		'APP_NAME' => '搜狐微博', //应用名称
		'CALLBACK' => 'http://project.aikehou.com/Members/callback/type/sohu', //应用回调地址
	),

);
?>
<?php
return array(
	//开启trace，开启可能会影响通过php输入js的插件，如：ueditor、广告插件等
	//'SHOW_PAGE_TRACE' => true,

	//加载其他的配置文件
	'LOAD_EXT_CONFIG' => 'email,router,dbconfig,pay',

	//设置模型
	'MODULE_ALLOW_LIST' => array('Home','Kgmanage','Wechat','Special','Wap','Plugins','Install'),
	'DEFAULT_MODULE' => 'Home',

	//url忽略大小写
	'URL_CASE_INSENSITIVE' => true,

	//设置url为兼容模式
	'URL_MODEL' => 3,

	//加载自定义标签
	'TAGLIB_BUILD_IN'    =>    'cx,Plugins\TagLib\Aikehou',
	'TAGLIB_PRE_LOAD'    =>    'Plugins\TagLib\Aikehou',

	//(成功)错误跳转模板文件
	'TMPL_ACTION_ERROR' =>  "./Public/ErrorTpl/dispatch_jump.tpl",
	'TMPL_ACTION_SUCCESS' =>  "./Public/ErrorTpl/dispatch_jump.tpl",

	// 开启子域名配置
	/*'APP_SUB_DOMAIN_DEPLOY'   =>    1, 
	'APP_SUB_DOMAIN_RULES'    =>    array(   
	    'm.a.com'  => 'Wap'
	),*/
);
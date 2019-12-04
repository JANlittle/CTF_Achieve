<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2019.08.08 ]
* Description [ 数据库配置文件 ]
*/

return array(
	//数据库配置
	'DB_TYPE' => 'mysql',
	'DB_HOST' => 'localhost',
	'DB_USER' => 'root',
	'DB_PWD' => 'root',
	'DB_PORT' => 3306,
	'DB_NAME' => 'echo',
	'DB_PREFIX' => 'echo_',

	//设置Ucenter
	'UCENTER_DB_NAME' => 'echoucenter',
	'UCENTER_DB_PREFIX' => 'echo_',
	'UCENTER_DB_TABLE_MEMBERS' => 'Members',
	'UCENTER_DB_TABLE_APPLICATIONS' => 'Applications',
	'UCENTER_DB_DSN' => 'mysql://root:root@localhost/echoucenter', //ucenterdsn
	'UCENTER_DOMAIN' => 'http://127.0.0.1:8090/aikehou/echoucenter/', //用户中心的域名
);
?>
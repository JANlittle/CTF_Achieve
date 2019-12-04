<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2016.06.28 ]
* Description [ 路由配置文件 ]
*/

return array(
	//开启全局路由
	'URL_ROUTER_ON' => true,

	//全局路由规则：全局路由是针对所有分组项目
	'URL_ROUTE_RULES' => array(

		//内容
		'/^lists\_(\d+)$/' => 'Home/Lists/index?p=:1',
		'/^a\/(\d+)\/(\d+)$/' => 'Home/News/details?cid=:1&id=:2',
		'/^a\/(\d+)\/(\d+)\_c(\d+)$/' => 'Home/News/details?cid=:1&id=:2&p=:3',

		//单页
		'/^about\/(\d+)$/' => 'Home/About/index?cid=:1',
	),
);
?>
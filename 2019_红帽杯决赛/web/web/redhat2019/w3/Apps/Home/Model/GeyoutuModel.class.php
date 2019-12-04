<?php
/*
* Author : [ Copy Lian ]
* Date : [ 2015.05.13 ]
* Description : [ 格优图模型 ]
*/
namespace Home\Model;
use Think\Model\RelationModel;

class GeyoutuModel extends RelationModel
{
	protected $_link = array(

		//获取分类
		'Category' => array(
			'mapping_type' => self::BELONGS_TO,
			'class_name' => 'Category',
			'foreign_key' => 'catid',
			'mapping_name' => 'Category',
			'mapping_fields' => 'id,name,route,url',
			'as_fields' => 'name:catname,route:catroute,url:caturl'
		)
	);
}
?>
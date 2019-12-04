<?php
/*
* Author: [  Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 用户行为 ]
*/
namespace Kgmanage\Model;
use Think\Model\RelationModel;

class ActionlogModel extends RelationModel
{
	protected $_link = array(
		//关联后台管理员
		'Adminuser' => array(
			'mapping_type' => self::BELONGS_TO,
			'class_name' => 'Adminuser',
			'mapping_name' => 'adminuser',
			'foreign_key' => 'user_id'
		),
		//管理Ucenter
		'Members' => array(
			'mapping_type' => self::BELONGS_TO,
			'class_name' => 'Members',
			'mapping_name' => 'ucmembers',
			'foreign_key' => 'user_id'
		),
		//管理Ucenter
		'Action' => array(
			'mapping_type' => self::BELONGS_TO,
			'class_name' => 'Action',
			'mapping_name' => 'action',
			'foreign_key' => 'action_id',
			'mapping_fields' => 'id,title,name'
		),
	);
}
?>
<?php
/*
* Author : [ Copy Lian ]
* Date : [ 2015.05.13 ]
* Description : [ 评论模型 ]
*/
namespace Home\Model;
use Think\Model\RelationModel;

class CommentsModel extends RelationModel
{
	protected function _initialize(){
		$this->_link = array(
			//关联UCenter
			"UcMembers" => array(
				'mapping_type' => self::BELONGS_TO,
				'class_name' => "UcMembers",
				'mapping_name' => 'ucmembers',
				'foreign_key' => 'uid'
			),
			//关联回复的数据
			"SonComments" => array(
				'mapping_type' => self::HAS_MANY,
				'class_name' => "Comments",
				'mapping_name' => 'soncomments',
				'parent_key' => 'pid',
				'condition' => 'status = 1'
			),
			//关联回复的数据
			"ParentComments" => array(
				'mapping_type' => self::BELONGS_TO,
				'class_name' => "Comments",
				'mapping_name' => 'parentcomments',
				'parent_key' => 'pid'
			)
		);
	}
}
?>
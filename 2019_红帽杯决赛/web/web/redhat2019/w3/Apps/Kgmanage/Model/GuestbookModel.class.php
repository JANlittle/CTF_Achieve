<?php
/**
* Author : [ Copy Lian ]
* Date : [ 2015.05.13 ]
* Description : [ 留言建议 ]
*/
namespace Kgmanage\Model;
use Think\Model\RelationModel;

class GuestbookModel extends RelationModel
{
	protected function _initialize(){
		$this->_link = array(
			//关联UCenter
			"Members" => array(
				'mapping_type' => self::BELONGS_TO,
				'class_name' => "Members",
				'mapping_name' => 'ucmembers',
				'foreign_key' => 'uid'
			)
		);
	}
}
?>
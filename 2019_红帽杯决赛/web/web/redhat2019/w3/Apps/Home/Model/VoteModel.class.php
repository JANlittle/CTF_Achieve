<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 投票管理模型 ]
*/
namespace Home\Model;
use Think\Model\RelationModel;

class VoteModel extends RelationModel
{

	public function _initialize(){
		$this->_link  = array(
			'Voteoptions' => array(
				'mapping_type' => self::HAS_MANY,
				'mapping_class' => 'Voteoptions',
				'foreign_key' => 'vid',
				'mapping_name' => 'Voteoptions',
				'condition' => 'status = 1 AND siteid = ' . C('SITEID')
			),
			'Voteinfo' => array(
				'mapping_type' => self::HAS_MANY,
				'mapping_class' => 'Voteinfo',
				'foreign_key' => 'vid',
				'mapping_name' => 'Voteinfo',
				'condition' => 'siteid = ' . C('SITEID')
			)
		);
	}
}
?>
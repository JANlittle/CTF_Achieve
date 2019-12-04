<?php
/**
* Author : [ Copy Lian ]
* Date : [ 2015.05.13 ]
* Description : [ 投票选项 ]
*/
namespace Wechat\Model;
use Think\Model\RelationModel;

class WechatVoteoptionsModel extends RelationModel
{
	protected $_link = array(
		'WechatVoteinfo' => array(
			'mapping_type' => self::HAS_MANY,
			'mapping_class' => 'WechatVoteinfo',
			'foreign_key' => 'optionsid',
			'mapping_name' => 'WechatVoteinfo',
		)
	);
}
?>
<?php
/**
* Author : [ Copy Lian ]
* Date : [ 2015.05.13 ]
* Description : [ 投票信息 ]
*/
namespace Wechat\Model;
use Think\Model\RelationModel;

class WechatVoteinfoModel extends RelationModel
{
	protected $_link = array(
		'WechatVoteoptions' => array(
			'mapping_type' => self::BELONGS_TO,
			'mapping_class' => 'WechatVoteoptions',
			'foreign_key' => 'optionsid',
			'mapping_name' => 'WechatVoteoptions'
		)
	);
}
?>
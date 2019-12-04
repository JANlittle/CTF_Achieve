<?php
/**
* Author : [ Copy Lian ]
* Date : [ 2015.05.13 ]
* Description : [ 奖品模型 ]
*/
namespace Wechat\Model;
use Think\Model\RelationModel;

class WechatScratchproductsModel extends RelationModel
{
	protected $_link = array(
		'WechatScratchmember' => array(
			'mapping_type' => self::HAS_MANY,
			'mapping_class' => 'WechatScratchmember',
			'foreign_key' => 'prizeid',
			'mapping_name' => 'WechatScratchmember',
		)
	);
}
?>
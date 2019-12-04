<?php
/**
* Author : [ Copy Lian ]
* Date : [ 2015.05.13 ]
* Description : [ 内容模型 ]
*/
namespace Wechat\Model;
use Think\Model\RelationModel;

class WechatReplyModel extends RelationModel
{
	protected $_link = array(
		'WechatZan' => array(
			'mapping_type' => self::HAS_MANY,
			'mapping_class' => 'WechatZan',
			'foreign_key' => 'aid',
			'mapping_name' => 'wechatzan',
		)
	);
}
?>
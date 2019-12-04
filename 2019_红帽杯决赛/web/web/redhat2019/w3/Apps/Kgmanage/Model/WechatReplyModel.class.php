<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 微信自定义回复 ]
*/
namespace Kgmanage\Model;
use Think\Model\RelationModel;

class WechatReplyModel extends RelationModel
{
	protected $_validate = array(
	    array('keywords','require','{%_KEYWORDS_MUST_}'),
	    array('keywords','','{%_KEYWORDS_EXISTS_}',0,'unique',3),
	    array('title','require','{%_NAME_MUST_}'),
	    array('url','url','{%_MUST_BE_URL_}',2,'regex',3)
    );
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
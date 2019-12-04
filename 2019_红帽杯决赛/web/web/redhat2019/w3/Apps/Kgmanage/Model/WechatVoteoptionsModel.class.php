<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 投票选项 ]
*/
namespace Kgmanage\Model;
use Think\Model\RelationModel;

class WechatVoteoptionsModel extends RelationModel
{
	protected $_validate = array(
	    array('title','require','{%_TITLE_MUST_}')
    );

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
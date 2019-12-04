<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 投票 ]
*/
namespace Kgmanage\Model;
use Think\Model\RelationModel;

class WechatVoteModel extends RelationModel
{
	protected $_validate = array(
	    array('title','require','{%_NAME_MUST_}'),
	    array('start','require','{%_STARTTIME_MUST_}'),
		array('end','require','{%_ENDTIME_MUST_}')
    );

     //自动完成
	protected $_auto = array(
		array('start','strtotime',3,'function'),
		array('end','strtotime',3,'function')
	);

	//关联
    protected $_link = array(
		'WechatVoteoptions' => array(
			'mapping_type' => self::HAS_MANY,
			'mapping_class' => 'WechatVoteoptions',
			'foreign_key' => 'vid',
			'mapping_name' => 'WechatVoteoptions',
		),
		'WechatVoteinfo' => array(
			'mapping_type' => self::HAS_MANY,
			'mapping_class' => 'WechatVoteinfo',
			'foreign_key' => 'vid',
			'mapping_name' => 'WechatVoteinfo',
		)
	);
}
?>
<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 投票管理模型 ]
*/
namespace Kgmanage\Model;
use Think\Model\RelationModel;

class VoteModel extends RelationModel
{
	protected $_validate = array(
	    array('title','require','{%_TITLE_MUST_}'),
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
		'Voteoptions' => array(
			'mapping_type' => self::HAS_MANY,
			'mapping_class' => 'Voteoptions',
			'foreign_key' => 'vid',
			'mapping_name' => 'Voteoptions',
		),
		'Voteinfo' => array(
			'mapping_type' => self::HAS_MANY,
			'mapping_class' => 'Voteinfo',
			'foreign_key' => 'vid',
			'mapping_name' => 'Voteinfo',
		)
	);
}
?>
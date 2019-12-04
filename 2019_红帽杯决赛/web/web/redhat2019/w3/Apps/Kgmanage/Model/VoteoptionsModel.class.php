<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 投票选项 ]
*/
namespace Kgmanage\Model;
use Think\Model\RelationModel;

class VoteoptionsModel extends RelationModel
{
	protected $_validate = array(
	    array('title','require','{%_TITLE_MUST_}')
    );

    protected $_link = array(
		'Voteinfo' => array(
			'mapping_type' => self::HAS_MANY,
			'mapping_class' => 'Voteinfo',
			'foreign_key' => 'optionsid',
			'mapping_name' => 'Voteinfo',
		),
		'Vote' => array(
			'mapping_type' => self::BELONGS_TO,
			'mapping_class' => 'Vote',
			'foreign_key' => 'vid',
			'mapping_name' => 'Vote',
		)
	);
}
?>
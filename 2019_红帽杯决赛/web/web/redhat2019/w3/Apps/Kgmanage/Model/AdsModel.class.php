<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 广告管理 ]
*/
namespace Kgmanage\Model;
use Think\Model\RelationModel;

class AdsModel extends RelationModel
{
	protected $_validate = array(
	    array('name','require','{%_NAME_MUST_}')
	);

	protected $_link = array(
		'Adspace' => array(
			'mapping_type' => self::BELONGS_TO,
			'class_name' => 'Adspace',
			'mapping_name' => 'adspace',
			'foreign_key' => 'aid'
		)
	);

	//自动完成
	protected $_auto = array(
		array('starttime','strtotime',3,'function'),
		array('endtime','strtotime',3,'function')
	);
}
?>
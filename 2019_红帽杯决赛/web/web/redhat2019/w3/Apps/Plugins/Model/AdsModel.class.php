<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 广告管理 ]
*/
namespace Plugins\Model;
use Think\Model\RelationModel;

class AdsModel extends RelationModel
{

	protected $_link = array(
		'Adspace' => array(
			'mapping_type' => self::BELONGS_TO,
			'class_name' => 'Adspace',
			'mapping_name' => 'adspace',
			'foreign_key' => 'aid',
			'condition' => 'status = 1'
		)
	);

}
?>
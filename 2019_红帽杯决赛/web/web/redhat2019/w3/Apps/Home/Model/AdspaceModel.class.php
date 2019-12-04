<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 广告管理 ]
*/
namespace Home\Model;
use Think\Model\RelationModel;

class AdspaceModel extends RelationModel
{

	protected $_link = array(
		'Ads' => array(
			'mapping_type' => self::HAS_MANY,
			'class_name' => 'Ads',
			'mapping_name' => 'ads',
			'foreign_key' => 'aid',
			'condition' => 'status = 1'
		)
	);

}
?>
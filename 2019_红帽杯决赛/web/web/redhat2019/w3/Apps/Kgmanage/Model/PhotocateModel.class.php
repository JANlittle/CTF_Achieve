<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ Í¼Æ¬·ÖÀà ]
*/
namespace Kgmanage\Model;
use Think\Model\RelationModel;

class PhotocateModel extends RelationModel
{
	protected $_validate = array(
	    array('name','require','{%_NAME_MUST_}'),
	    //array('name','','{$_NAME_EXISTS_}',0,'unique',3)
    );

    protected $_link = array(
		'Photo' => array(
			'mapping_type' => self::HAS_MANY,
			'mapping_class' => 'Photo',
			'foreign_key' => 'catid',
			'mapping_name' => 'photo'
		)
	);
}
?>
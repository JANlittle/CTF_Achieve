<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 表单向导管理 ]
*/
namespace Kgmanage\Model;
use Think\Model\RelationModel;

class FormguideModel extends RelationModel
{
	protected $_validate = array(
	    array('name','require','{%_NAME_MUST_}'),
	    array('tablename','require','{%_TABLENAME_MUST_}'),
	    array('tablename','','{%_TABLENAME_UNIQUE_}',0,'unique',3),
	    array('tablename','/^[a-zA-Z]{1}([a-zA-Z0-9]|[_]){0,19}([a-zA-Z0-9]){1}$/','{%_TABLENAME_NO_OK_}',0,'regex',3)
	);

	protected $_link = array(
		'FormguideField' => array(
			'mapping_type' => self::HAS_MANY,
			'class_name' => 'FormguideField',
			'mapping_name' => 'FormguideField',
			'foreign_key' => 'modelid',
			'mapping_fields' => 'id,modelid',
			'condition' => 'isshow = 1'
		)
	);
}
?>
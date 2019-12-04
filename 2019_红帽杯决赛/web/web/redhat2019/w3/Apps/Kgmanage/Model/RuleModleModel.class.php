<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 规则模块 ]
*/
namespace Kgmanage\Model;
use Think\Model\RelationModel;

class RuleModleModel extends RelationModel
{
	protected $_validate = array(
	    array('name','require','{%_NAME_MUST_}'),
	    //array('name','','{%_NAME_EXISTS_}',0,'unique',3),
    );

	protected $_link = array(
		//获取父类
		'RuleModlea' => array(
			'mapping_type' => self::BELONGS_TO,
			'class_name' => 'RuleModle as a',
			'foreign_key' => 'pid',
			'mapping_name' => 'rulemodlea',
			'mapping_fields' => 'id,name',
			'as_fields' => 'name:rulemodlename'
		),

		//获取子类
		'RuleModleson' => array(
			'mapping_type' => self::HAS_MANY,
			'class_name' => 'RuleModle as son',
			'foreign_key' => 'pid',
			'mapping_name' => 'rulemodleson',
			'mapping_fields' => 'id,name'
		),

		//获取权限规则
		'AuthRule' => array(
			'mapping_type' => self::HAS_MANY,
			'class_name' => 'AuthRule',
			'foreign_key' => 'mid',
			'mapping_name' => 'authrule',
			'mapping_fields' => 'id,title,mid,name',
			'condition' => 'status = 1',
			'mapping_order' => 'sort ASC'
		)
	);
}
?>
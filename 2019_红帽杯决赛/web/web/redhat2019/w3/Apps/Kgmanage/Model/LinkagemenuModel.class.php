<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 联动菜单 ]
*/
namespace Kgmanage\Model;
use Think\Model\RelationModel;

class LinkagemenuModel extends RelationModel
{
	protected $_validate = array(
	    array('name','require','{%_NAME_MUST_}'),
	    array('typeid','checkId','{%_TYPE_MUST_}',1,'callback',3),
    );

	protected $_link = array(

		//联动类型
		'LinkageType' => array(
			'mapping_type' => self::BELONGS_TO,
			'class_name' => 'LinkagemenuType',
			'foreign_key' => 'typeid',
			'mapping_name' => 'linkagetype',
			'mapping_fields' => 'id,name',
			'as_fields' => 'name:linkname'
		),

		//获取父类
		'Linkagemenuparent' => array(
			'mapping_type' => self::BELONGS_TO,
			'class_name' => 'Linkagemenu as a',
			'foreign_key' => 'pid',
			'mapping_name' => 'linkagemenuparent',
			'mapping_fields' => 'id,name',
			'as_fields' => 'name:menuname'
		),

		//获取子类
		'Linkagemenuson' => array(
			'mapping_type' => self::HAS_MANY,
			'class_name' => 'Linkagemenu as son',
			'foreign_key' => 'pid',
			'mapping_name' => 'linkagemenuson',
			'mapping_fields' => 'id,name',
		),

	);

	/**
	 * [checkModelid 模型id验证函数]
	 * @return [type] [description]
	 */
    protected function checkId($field){
    	if(!empty($field)){
    		return true;
    	} else {
    		return false;
    	}
    }
}
?>
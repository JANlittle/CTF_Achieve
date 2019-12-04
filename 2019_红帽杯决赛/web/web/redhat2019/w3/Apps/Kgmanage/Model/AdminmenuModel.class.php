<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 管理菜单 ]
*/
namespace Kgmanage\Model;
use Think\Model\RelationModel;

class AdminmenuModel extends RelationModel
{
	protected $_validate = array(
		array('mid','checkId','{%_MODULE_ERROR_}',1,'callback',3),
	    array('name','require','{%_NAME_MUST_}'),
	    array('url','require','{%_URL_MUST_}'),
	    array('url','','{%_URL_UNIQUE_}',0,'unique',3),
    );

	protected $_link = array(
		'RuleModle' => array(
			'mapping_type' => self::BELONGS_TO,
			'class_name' => 'RuleModle',
			'foreign_key' => 'mid',
			'mapping_name' => 'rulemodle',
			'mapping_fields' => 'name',
			'as_fields' => 'name:modlename',
			'condition' => "status = 1"
		),

		//获取父类
		'Adminmenua' => array(
			'mapping_type' => self::BELONGS_TO,
			'class_name' => 'Adminmenu as a',
			'foreign_key' => 'pid',
			'mapping_name' => 'adminmenua',
			'mapping_fields' => 'id,name',
			'as_fields' => 'name:adminmenuname'
		),

		//获取子类
		'Adminmenuson' => array(
			'mapping_type' => self::HAS_MANY,
			'class_name' => 'Adminmenu as son',
			'foreign_key' => 'pid',
			'mapping_name' => 'adminmenuson',
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
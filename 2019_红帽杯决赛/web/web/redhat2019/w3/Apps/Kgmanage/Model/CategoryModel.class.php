<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 导航分类 ]
*/
namespace Kgmanage\Model;
use Think\Model\RelationModel;

class CategoryModel extends RelationModel
{
	protected function _initialize(){
		$this->_validate = array(
		    array('name','require','{%_NAME_MUST_}'),
		    array('modelid','checkModelid','{%_MODULE_ADD_MUST_}',1,'callback',3),
		    array('url','url','{%_MUST_BE_URL_}',2,'regex',3),
		    array('route','require','{%_ROUTE_MUST_}'),
		    array('route','/^[a-zA-Z]{1}([a-zA-Z0-9]|[_]){0,19}([a-zA-Z0-9]){0,}$/','{%_ROUTE_ERROR_}',2,'regex',3),
	    );

		$this->_link = array(

			//获取父类
			'Categorypid' => array(
				'mapping_type' => self::BELONGS_TO,
				'class_name' => 'Category as a',
				'foreign_key' => 'pid',
				'mapping_name' => 'categorypid',
				'mapping_fields' => 'id,name',
				'as_fields' => 'name:pidname'
			),

			//获取子类
			'Categoryson' => array(
				'mapping_type' => self::HAS_MANY,
				'class_name' => 'Category as son',
				'foreign_key' => 'pid',
				'mapping_name' => 'categoryson',
				'mapping_fields' => 'id,name',
				'condition' => C('catson_condition')
			),

			//归属模型
			'Sitemodel' => array(
				'mapping_type' => self::BELONGS_TO,
				'class_name' => 'Sitemodel',
				'foreign_key' => 'modelid',
				'mapping_name' => 'sitemodel',
				'mapping_fields' => 'id,name,tablename'
			),

			//获取单页
			'Danyedata' => array(
				'mapping_type' => self::HAS_ONE,
				'class_name' => 'Danye',
				'foreign_key' => 'catid',
				'mapping_name' => 'Danyedata',
				'mapping_fields' => 'id,catid'
			),

		);
	}

	/**
	 * [checkModelid 模型id验证函数]
	 * @return [type] [description]
	 */
    protected function checkModelid($field){
    	if(!empty($field)){
    		return true;
    	} else {
    		return false;
    	}
    }
	
}
?>
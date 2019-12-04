<?php
/*
* Author: [  Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 权限规则 ]
*/
namespace Kgmanage\Model;
use Think\Model\RelationModel;

class AuthRuleModel extends RelationModel
{

	protected $_validate = array(
		array('mid','checkId','{%_MODULE_ERROR_}',1,'callback',3),
		array('name','require','{%_NAME_MUST_}'),
		array('name','','{%_NAME_UNIQUE_}',0,'unique',3),
		array('title','require','{%_TITLE_MUST_}')
	);

	protected $_link = array(
		'RuleModle' => array(
			'mapping_type' => self::BELONGS_TO,
			'class_name' => 'RuleModle',
			'foreign_key' => 'mid',
			'mapping_name' => 'rulemodle',
			'mapping_fields' => 'id,name',
			'as_fields' => 'name:modlename',
			'condition' => 'status = 1'
		)
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
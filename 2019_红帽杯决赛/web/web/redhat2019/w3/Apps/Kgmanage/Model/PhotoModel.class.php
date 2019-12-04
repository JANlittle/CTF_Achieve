<?php
/**
* Author : [ Copy Lian ]
* Date : [ 2015.05.13 ]
* Description : [ 图片 ]
*/
namespace Kgmanage\Model;
use Think\Model\RelationModel;

class PhotoModel extends RelationModel
{
	//自动验证
	protected $_validate = array(
	    array('catid','checkId','{%_NO_CATE_}',1,'callback',3),
	    array('name','require','{%_NAME_MUST_}'),
	    //array('name','','{%_NAME_UNIQUE_}',0,'unique',1),
	    array('url','require','{%_URL_MUST_}',2,'regex',3),
	    array('url','url','{%_URL_ERROR_}',2,'regex',3)
	);

	protected $_link = array(
		'Photocate' => array(
			'mapping_type' => self::BELONGS_TO,
			'mapping_class' => 'Photocate',
			'foreign_key' => 'catid',
			'mapping_name' => 'photocate'
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
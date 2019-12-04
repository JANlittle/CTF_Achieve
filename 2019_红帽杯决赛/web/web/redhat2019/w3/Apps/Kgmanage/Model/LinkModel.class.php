<?php
/**
* Author : [ Copy Lian ]
* Date : [ 2015.05.13 ]
* Description : [ 友情链接 ]
*/
namespace Kgmanage\Model;
use Think\Model\RelationModel;

class LinkModel extends RelationModel
{
	//自动验证
	protected $_validate = array(
	    array('catid','require','{%_CATE_MUST_}'),
	    array('catid','checkId','{%_NO_CATE_}',1,'callback',3),
	    array('title','require','{%_NAME_MUST_}'),
	    array('url','require','{%_URL_MUST_}'),
	    array('url','url','{%_URL_ERROR_}')
	);

	protected $_link = array(
		'Linkcate' => array(
			'mapping_type' => self::BELONGS_TO,
			'mapping_class' => 'Linkcate',
			'foreign_key' => 'catid',
			'mapping_name' => 'linkcate',
			'mapping_fields' => 'id,name',
			'as_fields' => 'name:linkname'
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
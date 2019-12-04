<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 友情链接分类 ]
*/
namespace Kgmanage\Model;
use Think\Model\RelationModel;

class LinkcateModel extends RelationModel
{
	protected $_validate = array(
	    array('name','require','{%_NAME_MUST_}'),
	    //array('name','','{$_NAME_EXISTS_}',0,'unique',3)
    );

    protected $_link = array(
		'Link' => array(
			'mapping_type' => self::HAS_MANY,
			'mapping_class' => 'Link',
			'foreign_key' => 'catid',
			'mapping_name' => 'links'
		)
	);
}
?>
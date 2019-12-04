<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 角色 ]
*/
namespace Kgmanage\Model;
use Think\Model\RelationModel;

class AuthGroupModel extends RelationModel
{
	protected $_validate = array(
	    array('title','require','{%_NAME_MUST_}'),
	    //array('title','','{%_NAME_EXISTS_}',0,'unique',3),
    );

    protected $_link = array(
    	'AuthGroupAccess' => array(
    		'mapping_type' => self::HAS_MANY,
    		'class_name' => 'AuthGroupAccess',
    		'foreign_key'   => 'group_id',
		    'mapping_name'  => 'authgroupaccess',
		    'mapping_fields'  => 'group_id'
    	),
        'Site' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'Site',
            'foreign_key'   => 'siteid',
            'mapping_name'  => 'Site',
            'mapping_fields'  => 'id,name'
        )
    );
}
?>
<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 管理员 ]
*/
namespace Kgmanage\Model;
use Think\Model\RelationModel;

class AdminuserModel extends RelationModel
{
	protected $_validate = array(
	    array('username','require','{%_USERNAME_MUST_}'),
	    array('username','2,20','{%_UERNAMELENGTH_ERROR_}',0,'length',3),
	    array('username','','{%_USERNAME_EXISTS_}',0,'unique',3),
	    array('password','require','{%_PASSWORD_MUST_}',0,'regex',1), //密码插入时验证
	    array('password','6,20','{%_PASSWORDLENTH_ERROR_}',0,'length',1),//密码插入时验证
	    array('ckpassword','password','{%_CHECKPASSWORD_ERROR_}',0,'confirm',1), //密码插入时验证
	    array('email','email','{%_EMAIL_ERROR_}',2,'regex',3), //邮件
	    array('mobile','11','{%_MOBILE_LENGTH_}',2,'length',3) //手机
    );

    protected $_link = array(
    	'AuthGroupAccess' => array(
    		'mapping_type' => self::HAS_MANY,
    		'class_name' => 'AuthGroupAccess',
    		'foreign_key'   => 'uid',
		    'mapping_name'  => 'authgroupaccess',
		    'mapping_fields'  => 'group_id'
    	),

    	'AuthGroup' => array(
		    'mapping_type'      =>  self::MANY_TO_MANY,
		    'class_name'        =>  'AuthGroup',
		    'mapping_name'      =>  'authgroup',
		    'foreign_key'       =>  'uid',
		    'relation_foreign_key'  =>  'group_id',
		    'relation_table'    =>  '__AUTH_GROUP_ACCESS__' //此处应显式定义中间表名称，且不能使用C函数读取表前缀
		)
    );
}
?>
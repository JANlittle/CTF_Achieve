<?php
/*
* Author : [ Copy Lian ]
* Date : [ 2015.05.13 ]
* Description : [ 用户注册模型 ]
*/
namespace Home\Model;
use Think\Model\RelationModel;

class UcMembersModel extends RelationModel
{
	protected $tableName;
	protected $dbName;
	protected $tablePrefix;
	protected $connection;

	public function _initialize(){
		$this->tableName = C('UCENTER_DB_TABLE_MEMBERS');
		$this->dbName = C('UCENTER_DB_NAME');
		$this->tablePrefix = C('UCENTER_DB_PREFIX');
		$this->connection = C('UCENTER_DB_DSN');
	}

	//验证用户注册数据
	protected $_validate = array(
	 	array('username','require','用户名必须填写！'),
	 	array('username','','用户名已经存在！',1,'unique',3),
	 	array('password','require','密码必须填写！',0,'regex',1),//密码插入时验证
	 	array('password','6,20','密码长度必须是6到20位！',0,'length',1),//密码插入时验证
	 	array('checkpassword','password','两次输入的密码确不一致！',0,'confirm',1), //密码插入时验证
	 	array('email','require','邮件必须填写！'),
	 	array('email','email','邮件格式不正确！'),
	 	array('email','','邮件已经存在！',0,'unique',3),
	 	array('mobile','require','手机必须填写！'),
	 	array('mobile','11','手机长度必须是11位！',0,'length',3),
	 	array('mobile','','手机已经存在！',0,'unique',3)
	);
}
?>
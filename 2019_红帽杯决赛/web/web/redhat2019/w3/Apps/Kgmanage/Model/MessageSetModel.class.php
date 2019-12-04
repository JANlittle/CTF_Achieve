<?php
/*
* Author: [  Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ MessageSet ]
*/
namespace Kgmanage\Model;
use Think\Model;

class MessageSetModel extends Model {
	//自动验证
	protected $_validate = array(
	    array('accout','require','{%_ACCOUNT_MUST_}'),
	    array('password','require','{%_PASSWORD_MUST_}')
	);
}
?>
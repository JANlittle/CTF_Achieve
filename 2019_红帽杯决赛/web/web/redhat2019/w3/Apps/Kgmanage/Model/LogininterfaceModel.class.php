<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 登录接口 ]
*/
namespace Kgmanage\Model;
use Think\Model;

class LogininterfaceModel extends Model
{
	protected $_validate = array(
	    array('name','require','{%_NAME_MUST_}'),
	    array('typename','require','{%_TYPE_MUST_}'),
	    array('appkey','require','{%_APPKEY_ERROR_}'),
	    array('appsecret','require','{%_APPSECRET_MUST_}'),
	    array('callbak','require','{%_CALLBACK_MUST_}')
	);
}
?>
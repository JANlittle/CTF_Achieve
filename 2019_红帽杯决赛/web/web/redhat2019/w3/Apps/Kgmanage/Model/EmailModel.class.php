<?php
/*
* Author: [  Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ Email ]
*/
namespace Kgmanage\Model;
use Think\Model;

class EmailModel extends Model {
	//自动验证
	protected $_validate = array(
	    array('email','require','{%_EMAILADDR_MUST_}'),
	    array('smtp','require','{%_SMTP_MUST_}'),
	    array('port','require','{%_SMTPPORT_MUST_}'),
	    array('accout','require','{%_EMAILACCOUNT_MUST_}'),
	    array('password','require','{%_EMAILPASS_MUST_}')
	);
}
?>
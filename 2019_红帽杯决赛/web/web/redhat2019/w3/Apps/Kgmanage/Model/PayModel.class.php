<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 支付接口 ]
*/
namespace Kgmanage\Model;
use Think\Model;

class PayModel extends Model
{
	protected $_validate = array(
	    array('payname','require','{%_NAME_MUST_}'),
	    array('payname','','{%_NAME_EXISTS_}',0,'unique',3)
	);
}
?>
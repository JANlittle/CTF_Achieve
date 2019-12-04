<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 微信会员卡成员 ]
*/
namespace Kgmanage\Model;
use Think\Model;

class WechatCardmemberModel extends Model
{
	protected $_validate = array(
		array('openid','require','{%_OPENID_MUST_}'),
		array('openid','','{%_OPENID_EXISTS_}',0,'unique',3),
	    array('name','require','{%_XINGMING_MUST_}'),
	    array('tel','require','{%_TEL_MUST_}')
    );
}
?>
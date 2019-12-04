<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 微信公众号 ]
*/
namespace Kgmanage\Model;
use Think\Model;

class WechatPublicModel extends Model
{
	protected $_validate = array(
	    array('name','require','{%_NAME_MUST_}'),
	    array('originid','require','{%_YUANSHIID_MUST_}'),
	    array('account','require','{%_WECHATACCOUT_MUST_}'),
	    array('appid','require','{%_YINGYONGID_MUST_}'),
	    array('appsecret','require','{%_YINGYONGKEY_MUST_}'),
	    array('encodingaeskey','require','{%_MESSAGEKEY_MUST_}')
	);
}
?>
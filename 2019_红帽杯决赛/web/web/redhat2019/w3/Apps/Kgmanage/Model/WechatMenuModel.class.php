<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 微信自定义菜单 ]
*/
namespace Kgmanage\Model;
use Think\Model;

class WechatMenuModel extends Model
{
	protected $_validate = array(
	    array('name','require','{%_NAME_MUST_}'),
	    array('url','url','{%_MUST_BE_URL_}',2,'regex',3)
    );
}
?>
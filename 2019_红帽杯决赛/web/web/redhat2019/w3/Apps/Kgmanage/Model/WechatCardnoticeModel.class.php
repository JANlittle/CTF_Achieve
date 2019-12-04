<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 通知管理 ]
*/
namespace Kgmanage\Model;
use Think\Model;

class WechatCardnoticeModel extends Model
{
	protected $_validate = array(
		array('title','require','{%_TITLE_MUST_}'),
		array('title','','{%_TITLE_EXISTS_}',0,'unique',3),
    );
}
?>
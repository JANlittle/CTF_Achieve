<?php
/*
* Author : [ Copy Lian ]
* Date : [ 2015.05.13 ]
* Description : [ 在线预约 ]
*/
namespace Home\Model;
use Think\Model;

class BaomingModel extends Model
{
	//自动验证
	protected $_validate = array(
	 	array('name','require','姓名必须填写！'),
	 	array('tel','require','联系电话必须填写！'),
	 	array('email','require','邮件必须填写！'),
	 	array('email','email','邮件格式不正确！'),
	 	array('qq','require','QQ号必须填写！'),
	);
}
?>
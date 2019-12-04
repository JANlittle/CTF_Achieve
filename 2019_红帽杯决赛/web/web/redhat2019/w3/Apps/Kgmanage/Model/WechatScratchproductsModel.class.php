<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 刮刮卡奖品 ]
*/
namespace Kgmanage\Model;
use Think\Model;

class WechatScratchproductsModel extends Model
{
	

	//自动验证
	protected $_validate = array(
		array('title','require','{%_TITLE_MUST_}'),
		array('name','require','{%_NAME_MUST_}')
    );
}
?>
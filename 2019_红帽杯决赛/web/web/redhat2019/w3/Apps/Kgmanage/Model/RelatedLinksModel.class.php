<?php
/**
* Author : [ Copy Lian ]
* Date : [ 2015.05.13 ]
* Description : [ 关联链接 ]
*/
namespace Kgmanage\Model;
use Think\Model;

class RelatedLinksModel extends Model
{
	//自动验证
	protected $_validate = array(
	    array('title','require','{%_NAME_MUST_}'),
	    array('url','url','{%_MUST_BE_URL_}',2,'regex',3),
	);
}
?>
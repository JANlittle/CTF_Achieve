<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 站点管理 ]
*/
namespace Kgmanage\Model;
use Think\Model;

class SiteModel extends Model
{
	protected $_validate = array(
	    array('name','require','{%_NAME_MUST_}'),
	    array('name','','{%_NAME_EXISTS_}',0,'unique',3),
    );
}
?>
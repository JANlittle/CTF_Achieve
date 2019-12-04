<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ URL路由 ]
*/
namespace Kgmanage\Model;
use Think\Model;

class RouterModel extends Model
{
	protected $_validate = array(
	    array('name','require','{%_NAME_MUST_}'),
	    array('expression','','{%_ROUTEREXP_UNIQUE_}',2,'unique',3)
    );
}
?>
<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 联动菜单类型 ]
*/
namespace Kgmanage\Model;
use Think\Model\RelationModel;

class LinkagemenuTypeModel extends RelationModel
{
	protected $_validate = array(
	    array('name','require','{%_NAME_MUST_}'),
	    array('name','','{$_NAME_EXISTS_}',0,'unique',3)
    );
}
?>
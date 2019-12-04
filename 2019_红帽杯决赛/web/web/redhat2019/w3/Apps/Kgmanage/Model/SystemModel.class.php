<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 管理菜单 ]
*/
namespace Kgmanage\Model;
use Think\Model;

class SystemModel extends Model
{
	protected $tableName = 'config';
	protected $_validate = array(
	    array('name','require','{%_NAME_MUST_}'),
	    //array('name','','{%_NAME_UNIQUE_}',0,'unique',3),
	    array('title','require','{%_TITLE_MUST_}'),
	    //array('title','','{%_TITLE_UNIQUE_}',0,'unique',3)
    );
}
?>
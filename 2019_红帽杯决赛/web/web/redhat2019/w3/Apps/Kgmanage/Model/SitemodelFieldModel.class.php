<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 字段管理 ]
*/
namespace Kgmanage\Model;
use Think\Model;

class SitemodelFieldModel extends Model
{
	protected $_validate = array(
	    array('field','require','{%_FIELDNAME_MUST_}'),
	    array('field','/^[a-zA-Z]{1}([a-zA-Z0-9]|[_]){0,19}([a-zA-Z0-9]){1}$/','{%_FIELDNAME_ERROR_}',0,'regex',3),
	    array('name','require','{%_FIELDTITLE_MUST_}'),
	    array('type','require','{%_FIELDTYPE_MUST_}'),
	    array('field_rule','require','{%_FIELDDINGYI_MUST_}'),
	);
}
?>
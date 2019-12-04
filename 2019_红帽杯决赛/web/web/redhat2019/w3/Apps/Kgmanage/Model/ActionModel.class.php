<?php
/*
* Author: [  Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 用户行为 ]
*/
namespace Kgmanage\Model;
use Think\Model\RelationModel;

class ActionModel extends RelationModel
{

	protected $_validate = array(
		array('name','require','{%_NAME_MUST_}'),
		array('name','','{%_NAME_UNIQUE_}',0,'unique',3),
		array('title','require','{%_TITLE_MUST_}')
	);
}
?>
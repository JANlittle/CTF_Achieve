<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 会员分组 ]
*/
namespace Kgmanage\Model;
use Think\Model\RelationModel;

class MembergroupModel extends RelationModel
{
	protected $_validate = array(
	    array('name','require','{%_NAME_MUST_}'),
	    array('name','','{%_NAME_EXISTS_}',0,'unique',3),
	    array('ltscore','number',"{%_JIFEN_MUST_NUMBER_}")
    );
}
?>
<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 刮刮卡 ]
*/
namespace Kgmanage\Model;
use Think\Model\RelationModel;

class WechatScratchModel extends RelationModel
{
	

	//自动验证
	protected $_validate = array(
		array('name','require','{%_NAME_MUST_}'),
		array('name','','{%_NAME_EXISTS_}',0,'unique',3),
		array('start','require','{%_STARTTIME_MUST_}'),
		array('end','require','{%_ENDTIME_MUST_}'),
		array('prizeendtime','require','{%_DUIJIANGTIME_MUST_}')
    );

    //自动完成
	protected $_auto = array(
		array('start','strtotime',3,'function'),
		array('end','strtotime',3,'function'),
		array('prizeendtime','strtotime',3,'function')
	);

	//关联
	protected $_link = array(
		'WechatScratchmember' => array(
			'mapping_type' => self::HAS_MANY,
			'class_name' => 'WechatScratchmember',
			'foreign_key' => 'cid',
			'mapping_name' => 'WechatScratchmember'
		),
		'WechatScratchproducts' => array(
			'mapping_type' => self::HAS_MANY,
			'class_name' => 'WechatScratchproducts',
			'foreign_key' => 'pid',
			'mapping_name' => 'WechatScratchproducts'
		)
	);

}
?>
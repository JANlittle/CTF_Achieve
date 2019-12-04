<?php
/**
* Author : [ Copy Lian ]
* Date : [ 2015.05.13 ]
* Description : [ 会员中心模型 ]
*/
namespace Kgmanage\Model;
use Think\Model\RelationModel;

class MembersModel extends RelationModel
{

	/**
	 * [_initialize 初始化模型]
	 * @return [type] [description]
	 */
	protected function _initialize(){
		//设置会员中心的信息
		$this->dbName = C('UCENTER_DB_NAME');
		$this->tablePrefix = C('UCENTER_DB_PREFIX');
		$this->tableName = C('UCENTER_DB_TABLE_MEMBERS');
	}
}
?>
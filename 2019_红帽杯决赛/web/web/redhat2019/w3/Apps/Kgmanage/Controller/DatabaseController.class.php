<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 数据库备份 ]
*/
namespace Kgmanage\Controller;

class DatabaseController extends CommonController
{
	/**
	 * [index 列表]
	 */
	public function export()
	{
		$list = M()->query('SHOW TABLE STATUS');
		$this->data = $list;
		$this->display();
	}

	/**
	 * [optimize 优化表]
	 */
	public function optimize()
	{
		if(IS_POST){
			$table = M();
			$tables = I('post.data');
			if(is_array($tables)){
				$tables_str = implode("`,`", $tables);
				$optimize = $table->query('OPTIMIZE TABLE  `{$tables_str}`');
				if($optimize){
					$this->success("优化表成功！");
				} else {
					$this->error("优化表失败！");
				}
			} else {
				$optimize = $table->query('OPTIMIZE TABLE  `{$tables}`');
				if($optimize){
					$this->success("优化表 {$tables} 成功！");
				} else {
					$this->error("优化表 {$tables} 失败！");
				}
			}
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [repair 修复表]
	 */
	public function repair()
	{
		if(IS_POST){
			$table = M();
			$tables = I('post.data');
			if(is_array($tables)){
				$tables_str = implode("`,`", $tables);
				$repair = $table->query('REPAIR TABLE  `{$tables_str}`');
				if($repair){
					$this->success("修复表成功！");
				} else {
					$this->error("修复表失败！");
				}
			} else {
				$repair = $table->query('REPAIR TABLE  `{$tables}`');
				if($repair){
					$this->success("修复表 {$tables} 成功！");
				} else {
					$this->error("修复表 {$tables} 失败！");
				}
			}
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [exportsql 备份数据库：要保证备份目录数据可写]
	*/
	public function exportsql()
	{
		if(IS_POST){
			//数据表
			$tables = I('post.data');
			if(!is_array($tables) || empty($tables)){
				$this->error('请选择操作的数据');
			}

			$data_path = C('SYSTEM_DBBACK_PATH');
			//确认备份目录是否可写
			if(!is_writeable($data_path)){
				$this->error('备份目录不可写！');
			}

			//判断是否存在锁定文件
			if(is_file($data_path . 'lock.txt')){
				$this->error('已经有备份进程，请稍后！');
			}

			//备份时锁定
			file_put_contents($data_path . 'lock.txt','lock!');

			//实例化备份类
			$dbback = new \Api\Dbback\Dbback();
			$res = $dbback->export($tables);

			//备份完成删除锁定文件
			if(is_file($data_path . 'lock.txt')){
				unlink($data_path . 'lock.txt');
			}

			if($res){
				$this->success('备份成功！');
			} else {
				$this->error('备份失败！');
			}
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [import 还原列表]
	 */
	public function import()
	{
		$files = glob(C('SYSTEM_DBBACK_PATH') . C('DB_NAME')  . "_" ."*.sql");
		$files_arr = array();
		if(!empty($files)){
			foreach ($files as $key => $value) {
				$pathinfo = pathinfo($value);
				$pathinfo['size'] = filesize($value);
				$pathinfo['create_time'] = filectime($value);
				$files_arr[] = $pathinfo;
			}
		}
		//排序
		sort($files_arr,SORT_NUMERIC);
		$this->data = $files_arr;
		$this->display();
	}

	/**
	 * [del 删除备份数据文件]
	 */
	public function del()
	{
		if(IS_POST){
			$data_path = C('SYSTEM_DBBACK_PATH');
			$filename = I('post.id');
			if(is_file($data_path.$filename)){
				unlink($data_path.$filename);
			}
			$this->success('删除成功！');
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [importsql 还原数据库]
	 * @return [type] [description]
	*/
	public function importsql()
	{
		if(IS_POST){
			$data_path = C('SYSTEM_DBBACK_PATH');

			$filename = I('post.data');
			if(!is_file($data_path.$filename)){
				$this->error('文件不存在！');
			}
			//获取数据
			$content = file_get_contents($data_path.$filename);

			//确认备份目录是否可写
			if(!is_writeable($data_path)){
				$this->error('备份目录不可写！');
			}

			//判断是否存在锁定文件
			if(is_file($data_path . 'lock_import.txt')){
				$this->error('已经有还原进程，请稍后！');
			}

			//还原时锁定
			file_put_contents($data_path . 'lock_import.txt','lock_import!');

			//实例化备份类
			$dbback = new \Api\Dbback\Dbback();
			$res = $dbback->import($content);

			//还原完成删除锁定文件
			if(is_file($data_path . 'lock_import.txt')){
				unlink($data_path . 'lock_import.txt');
			}

			if($res){
				$this->success('还原成功！');
			} else {
				$this->error('还原失败！');
			}
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}
}
?>
<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ Db备份 ]
*/
namespace Api\Dbback;
use Think\Db;
class Dbback
{
	protected $dblink; //数据库连接

	protected $db_configs; //配置

	/**
	 * [__construct 构造函数]
	 */
	public function __construct()
	{
		$this->dblink = M();
		$this->db_configs = array(
			'path' => C('SYSTEM_DBBACK_PATH'),
			'system_name' => C('SYSTEM_NAME'),
			'system_version' => C('SYSTEM_VERSION'),
			'system_url' => C('SYSTEM_COPYRIGHT_URL'),
			'db_name' => C('DB_NAME')
		);
	}

	/**
	 * [export 备份数据sql]
	 * @param  [array] $tables [表名数组]
	 * @return [int]           [返回写入数据长度]
	 */
	public function export($tables)
	{
		//初始化
		$sql = "-- --------------------------------------\n";
		$sql .= "-- " . $this->db_configs['system_name'] . " SQL Dump --\n";
		$sql .= "-- " . $this->db_configs['system_version'] . " --\n";
		$sql .= "-- " . $this->db_configs['system_url'] . " --\n";
		$sql .= "-- 主机: localhost --\n";
		$sql .= "-- 生成日期: " . date("Y-m-d H:i:s") . " --\n";
		$sql .= "-- 服务器版本: " . mysqli_get_server_info() . " --\n";
		$sql .= "-- PHP 版本: ". PHP_VERSION ." --\n";
		$sql .= "-- --------------------------------------\n\n";

		/*$sql .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
		$sql .= "SET time_zone = '+00:00';\n\n";*/

		//数据库
		$sql .= "-- 数据库：" . $this->db_configs['db_name'] . " --\n";
		$default_charset = $this->dblink->query("show variables like 'character_set_%'"); //获取编码
		$collate = $this->dblink->query("show variables like 'collation_%'"); //获取字符集
		$sql .= "CREATE DATABASE IF NOT EXISTS `" . $this->db_configs['db_name'] . "` DEFAULT CHARACTER SET " . $default_charset[0]['value'] . " COLLATE " . $collate[0]['value'] . ";\n";
		$sql .= "USE `" . $this->db_configs['db_name'] . "`;\n";
		$sql .= "-- end 数据库：" . $this->db_configs['db_name'] . " --\n\n";

		//循环多表导出
		foreach ($tables as $key => $value) {
			//数据表
			$sql .= "-- ----------------start table " . $value . "----------------------\n\n";
			$sql .= "DROP TABLE IF EXISTS `" . $value . "`;\n";

			//获取创建表信息
			$result = $this->dblink->query("SHOW CREATE TABLE `" . $value . "`");
			$sql .= $result[0]['create table'].";\n\n";

			//插入信息
			$sql .= "-- 插入数据 --\n";
			$result = $this->dblink->query("SELECT * FROM `" . $value . "`");
	        foreach ($result as $row) {

	        	//mysqli_real_escape_string这个函数要在已经连接数据库的情况下才能执行，否则处理之后的数据变成空
	            //$row = array_map('mysqli_real_escape_string', $row);

	            $row = array_map('addslashes', $row);
	            $sql .= "INSERT INTO `" . $value . "` VALUES ('" . implode("', '", $row) . "');\n";
	        }
	        $sql .= "\n";
			$sql .= "-- ----------------end table " . $value . "----------------------\n\n";
		}
		//写入数据
		return file_put_contents($this->db_configs['path'] . $this->db_configs['db_name'] . "_" .date("YmdHis").".sql", $sql);
	}

	/**
	 * [import 还原数据]
	 * @param  [type] $content [description]
	 * @return [type]          [description]
	 */
	public function import($content)
	{
		return $this->dblink->execute($content);
	}
}
?>
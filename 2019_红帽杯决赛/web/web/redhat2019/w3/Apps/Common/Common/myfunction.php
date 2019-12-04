<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 不自动加载公共函数库 ]
*/

/**
 * [clearEmptyData 清空数组值为空的选项]
 * @param  array  $data [description]
 * @return [type]       [description]
 */
function clearEmptyData($data = array())
{
	//筛选条件
	if(!empty($data)){
		foreach ($data as $key => $value) {
			if($value === ''){
				unset($data[$key]);
			}
		}
	}
	return $data;
}
?>
<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 清除缓存 ]
*/
namespace Kgmanage\Controller;

class ClearcacheController extends CommonController
{
	/**
	 * [index 列表]
	 */
	public function index()
	{
		if(IS_POST){
			delDir(RUNTIME_PATH . "Cache");
			delDir(RUNTIME_PATH . "Data");
			delDir(RUNTIME_PATH . "Temp");
			if(file_exists(RUNTIME_PATH."common~runtime.php")){
				unlink(RUNTIME_PATH."common~runtime.php");
			}
			$this->success(L('_CLEARCACHE_SUCCESS_'));
		}else {
			$this->display();
		}
	}

}
?>
<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 空模块 ]
*/
namespace Kgmanage\Controller;
use Think\Controller;

class EmptyController extends Controller
{
	/**
	 * [index 空模块]
	 */
	public function index()
	{
		$this->redirect("Index/index");
	}
}
?>
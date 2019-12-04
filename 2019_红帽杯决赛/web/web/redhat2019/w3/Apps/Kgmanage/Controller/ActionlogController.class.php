<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 用户行为日志 ]
*/
namespace Kgmanage\Controller;

class ActionlogController extends CommonController
{
	/**
	 * [index 列表]
	 */
	public function index()
	{
		$Actionlog = D('Actionlog');

		$this->user_type = I('get.type',1);
		$where['user_type'] = array('eq',$this->user_type);

		$where['siteid'] = array('eq',session('siteid'));
		$count = $Actionlog->field('id')->where($where)->count();

		//获取分页
		$page = getPage($count);
		$this->pagelist = $page->show();

		//获取用户信息
		if($this->user_type == 1){
			//获取后台管理员信息
			$relation = array('action','adminuser');
		} elseif($this->user_type == 2) {
			//获取ucente中心的用户信息
			$relation = array('action','ucmembers');
		}

		//获取数据
		$data = $Actionlog->where($where)->relation($relation)->order('create_time DESC,id DESC')->limit($page->firstRow,$page->listRows)->select();

		if(!empty($data)){
			foreach ($data as $key => $value) {
				//设置地区
		        $ip = new \Org\Net\IpLocation('UTFWry.dat');
		        $data[$key]['area'] = $ip->getlocation($value['action_ip']);
			}
		}

		$this->data = $data;
		$this->display();
	}


	/**
	 * [details 详细信息]
	 */
	public function details()
	{
		$id = I('get.id');
		$this->user_type = I('get.user_type');
		if(!is_numeric($id) || !is_numeric($this->user_type)){
			$this->error(L('_ACCESS_ERROR_'));
		}

		//获取用户信息
		if($this->user_type == 1){
			//获取后台管理员信息
			$relation = array('action','adminuser');
		} elseif($this->user_type == 2) {
			//获取ucente中心的用户信息
			$relation = array('action','ucmembers');
		}

		//获取数据
		if(!$data = D('Actionlog')->relation($relation)->find($id)){
			$this->error(L('_NODATA_'));
		}

		//设置地区
        $ip = new \Org\Net\IpLocation('UTFWry.dat');
        $data['area'] = $ip->getlocation($data['action_ip']);

        $this->data = $data;
		$this->display();
	}


	/**
	 * [del 删除]
	 */
	public function del()
	{
		if(IS_POST){
			//验证数据
			$id = I('post.id');
			$ids = explode(",", $id);

			//没有数据
			if(empty($ids)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//验证数据
			$Actionlog = M('Actionlog');
			foreach ($ids as $key => $value) {
				$data = $Actionlog->field('id')->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//删除数据
			if($Actionlog->delete($id)){
				$this->success(L('_DEL_SUCCESS_'));
			} else {
				$this->error(L('_DEL_ERROR_'));
			}
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}
}
?>
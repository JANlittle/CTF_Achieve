<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 站点管理 ]
*/
namespace Kgmanage\Controller;

class SiteController extends CommonController
{
	/**
	 * [index 站点列表]
	 */
	public function index()
	{
		$Site = M('Site');
		$count = $Site->field('id')->count();
		//获取分页
		$page = getPage($count);
		$this->pagelist = $page->show();
		$this->data = $Site->order('sort ASC,id DESC')->limit($page->firstRow,$page->listRows)->select();

	/*获取url参数*/
		$this->parameter = getParameter(I('get.'),$page);
	/* end 获取url参数*/
		$this->display();
	}

	/**
	 * [add 新增站点]
	 */
	public function add()
	{
		//数据提交
		if(IS_POST){
			$site = D('Site');
			if($data = $site->create()){
				if($insertid = $site->add()){
					//新增排序
					$site->where("id = {$insertid}")->setField('sort',$insertid);

					//设置默认站点id
					$count = $site->where('status = 1')->count();
					if($count == 1){
						session('siteid',$insertid);
					}

					$this->success(L('_ADD_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($site->getError());
			}
		} else {
			//获取参数
			$this->parameter = I('get.parameter');
			$this->display();
		}
	}

	/**
	 * [edit 编辑站点]
	 */
	public function edit()
	{
		//提交数据处理
		if(IS_POST){
			$Site = D('Site');
			if($data = $Site->create()){
				if($Site->save()){
					//设置默认站点id
				    /*第一种情况：如果保存状态是开启而且只有一条数据时*/
					$count = $Site->where('status = 1')->count();
					if($count == 1 && $data['status'] == 1){
						session('siteid',$data['id']);
					}
					/*第二种情况：session的siteid等于当前的siteid而且保存状态是关闭*/
					if(session('siteid') == $data['id'] && $data['status'] == 0){
						$new_site = $Site->where('status = 1')->find();
						!empty($new_site) ? session('siteid',$new_site['id']) : session('siteid',0);
					}

					$this->success(L('_SAVE_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($Site->getError());
			}
		} else {
			//验证数据
			$id = I('get.id');
			if(!is_numeric($id)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//获取参数
			$this->parameter = I('get.parameter');

			//获取数据
			$Site = M('Site');
			$data = $Site->find($id);
			if(!$data){
				$this->error(L('_NODATA_'));
			}
			$this->data = $data;
			$this->display();
		}
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
			$Site = M('Site');
			if(!empty($ids)){
				foreach ($ids as $key => $value) {
					$data = $Site->field('id')->find($value);
					if(!$data){
						$this->error(L('_NODATA_'));
						break;
					}
				}
			} else {
				$this->error(L('_NOACCESS_DEL_'));
			}

			//删除数据
			if($Site->delete($id)){
				/*设置默认站点id:session的siteid等于当前的siteid*/
				if(session('siteid') == $id){
					$new_site = $Site->where('status = 1')->find();
					!empty($new_site) ? session('siteid',$new_site['id']) : session('siteid',0);
				}
				$this->success(L('_DEL_SUCCESS_'));
			} else {
				$this->error(L('_DEL_ERROR_'));
			}
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [sort 排序]
	 * @return [type] [description]
	 */
	public function sort()
	{
		if(IS_POST){
			$sort = I('post.sort');
			$sortarr = explode(",", $sort);

			//验证数据
			if(empty($sortarr)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			$Site = M('Site');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$Site->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}
}
?>
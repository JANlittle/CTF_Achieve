<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 管理菜单 ]
*/
namespace Kgmanage\Controller;

class AdminmenuController extends CommonController
{
	/**
	 * [index 列表]
	 */
	public function index()
	{
		$pid = I('get.pid',0);
		$adminmenu = D('Adminmenu');
		$where['pid'] = array('eq',$pid);
		$where['siteid'] = array('in',array(session('siteid'),0));
		$count = $adminmenu->field('id')->where($where)->count();
		//获取分页
		$page = getPage($count);
		$this->pagelist = $page->show();
		$this->data = $adminmenu->where($where)->relation(array('rulemodle','adminmenuson','adminmenua'))->order('sort ASC,id DESC')->limit($page->firstRow,$page->listRows)->select();

	/*获取url参数*/
		$this->link_parameter = getParameter(I('get.'),$page);
	/* end 获取url参数*/
		$this->display();
	}

	/**
	 * [add 新增]
	 */
	public function add()
	{
		//数据提交
		if(IS_POST){
			$adminmenu = D('Adminmenu');
			if($data = $adminmenu->create()){
				//设置站点id
				$adminmenu->siteid = session('siteid');
				if($insertid = $adminmenu->add()){
					//新增排序
					$adminmenu->where("id = {$insertid}")->setField('sort',$insertid);
					$this->success(L('_ADD_SUCCESS_'),U('index',decode(I('post.link_parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($adminmenu->getError());
			}
		} else {
			//获取菜单列表
			$adminmenu = M('Adminmenu');
			$where['status'] = array('eq',1);
			$where['siteid'] = array('in',array(session('siteid'),0));
			$menulist_a = $adminmenu->field('id,name,pid')->where($where)->order('sort ASC,id DESC')->select();
			//组装select下拉菜单

			$this->menulist_a = getSelectedOption($menulist_a, 0, 0);

			//获取菜单列表
			$rulemodule = M('RuleModle');
			$rulemodule_where['status'] = array('eq',1);
			$rulemodule_where['siteid'] = array('in',array(session('siteid'),0));
			$rulemodule_ab = $rulemodule->field('id,name,pid')->where($rulemodule_where)->order('sort ASC,id DESC')->select();

			//组装select下拉菜单
			$this->rulemodule_ab = getSelectedOption($rulemodule_ab, 0, 0);

			//获取规则模块
			/*$this->rulemodle = M('RuleModle')->field('id,name')->where('status=1')->select();
			if(empty($this->rulemodle)){
				$this->error(L('_MODULE_ERROR_'));
			}*/

			//获取参数
			$this->link_parameter = I('get.link_parameter');

			//获取菜单
			$this->display();
		}
	}

	/**
	 * [edit 编辑]
	*/
	public function edit()
	{
		//提交数据处理
		if(IS_POST){
			$adminmenu = D('Adminmenu');
			if($data = $adminmenu->create()){
				//设置站点id
				$adminmenu->siteid = I('post.siteid');
				//保存数据
				if($adminmenu->save()){
					$this->success(L('_SAVE_SUCCESS_'),U('index',decode(I('post.link_parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($adminmenu->getError());
			}
		} else {
			//验证数据
			$id = I('get.id');
			if(!is_numeric($id)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//获取数据
			$adminmenu = M('Adminmenu');
			$adminmenu_where['siteid'] = array('in',array(session('siteid'),0));
			$adminmenu_where['id'] = array('eq',$id);
			$data = $adminmenu->where($adminmenu_where)->find();
			if(!$data){
				$this->error(L('_NODATA_'));
			}
			$this->data = $data;

			//获取菜单列表
			$where['status'] = array('eq',1);
			$where['siteid'] = array('in',array(session('siteid'),0));
			$menulist_a = $adminmenu->field('id,name,pid')->where($where)->order('sort ASC,id DESC')->select();

			//编辑菜单需要清楚自身菜单以及子类菜单
			$menulist_child = getChilds($menulist_a,$data['id']);
			//获取当前菜单以及所有子类id
			$idss = array();
			if(!empty($menulist_child)){
				foreach ($menulist_child as $key => $value) {
					$idss[] = $value['id'];
				}
			}
			//将父类id压入数组中
			array_push($idss,$data['id']);

			//删除数据中当前id包括子类id的数据
			if(!empty($menulist_a)){
				foreach ($menulist_a as $key => $value) {
					if(in_array($value['id'],$idss)){
						unset($menulist_a[$key]);
					}
				}
			}

			//组装select下拉菜单
			$this->menulist_a = getSelectedOption($menulist_a, 0, $data['pid']);

			//获取菜单列表
			$rulemodule = M('RuleModle');
			$rulemodule_where['status'] = array('eq',1);
			$rulemodule_where['siteid'] = array('in',array(session('siteid'),0));
			$rulemodule_a = $rulemodule->field('id,name,pid')->where($rulemodule_where)->order('sort ASC,id DESC')->select();

			//组装select下拉菜单
			$this->rulemodule_a = getSelectedOption($rulemodule_a, 0, $data['mid']);

			//获取规则模块
			/*$this->rulemodle = M('RuleModle')->field('id,name')->where('status = 1')->select();
			if(empty($this->rulemodle)){
				$this->error(L('_MODULE_ERROR_'));
			}*/

			//获取参数
			$this->link_parameter = I('get.link_parameter');

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
			$adminmenu = M('Adminmenu');
			foreach ($ids as $key => $value) {
				$data = $adminmenu->field('id')->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//删除数据时需要删除子类
			$data = $adminmenu->field('id,pid')->select();
			$arr = array();
			foreach ($ids as $key => $value) {
				//获取子类id数组
				$arr[] = getChilds($data,$value);
			}

			//取出id
			$idss = array();
			if(!empty($arr)){
				foreach ($arr as $key => $value) {
					foreach ($value as $key_1 => $value_1) {
						$idss[] = $value_1['id'];
					}
				}
			}

			//合并父级id与找到的子类id
			$ids = array_unique(array_merge($ids,$idss));
			$id = implode(',',$ids);
			//删除数据
			if($adminmenu->delete($id)){
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

			$adminmenu = M('Adminmenu');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$adminmenu->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}
}
?>
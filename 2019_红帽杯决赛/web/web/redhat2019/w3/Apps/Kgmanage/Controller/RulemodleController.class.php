<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 模块分组 ]
*/
namespace Kgmanage\Controller;

class RulemodleController extends CommonController
{
	/**
	 * [index 模块分组]
	 */
	public function index()
	{
		$pid = I('get.pid',0);
		$rulemodle = D('RuleModle');
		$where['pid'] = array('eq',$pid);
		$where['siteid'] = array('in',array(session('siteid'),0));
		$count = $rulemodle->field('id')->where($where)->count();
		//获取分页
		$page = getPage($count);
		$this->pagelist = $page->show();
		$this->data = $rulemodle->field('id,name,pid,sort,status')->where($where)->relation(array('rulemodlea','rulemodleson'))->order('sort ASC,id DESC')->limit($page->firstRow,$page->listRows)->select();

	/*获取url参数*/
		$this->parameter = getParameter(I('get.'),$page);
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
			$rulemodule = D('RuleModle');
			if($data = $rulemodule->create()){
				//设置站点id
				$rulemodule->siteid = session('siteid');

				//验证同一站点不能有相同的名称
				$check_where['siteid'] = array("eq",session('siteid'));
				$check_where['name'] = $rulemodule->name;
				if(!!$one = $rulemodule->where($check_where)->find()){
					$this->error(L('_NAME_EXISTS_'));
				}

				if($insertid = $rulemodule->add()){
					//新增排序
					$rulemodule->where("id = {$insertid}")->setField('sort',$insertid);
					$this->success(L('_ADD_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($rulemodule->getError());
			}
		} else {
			//获取菜单列表
			$rulemodule = M('RuleModle');
			$where['status'] = array('eq',1);
			$where['siteid'] = array('in',array(session('siteid'),0));
			$rulemodule_a = $rulemodule->field('id,name,pid')->where($where)->order('sort ASC,id DESC')->select();
			//组装select下拉菜单
			$this->rulemodule_a = getSelectedOption($rulemodule_a, 0, 0);

			//获取参数
			$this->parameter = I('get.parameter');
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
			$rulemodle = D('RuleModle');
			if($data = $rulemodle->create()){
				//设置站点id
				$rulemodle->siteid = I('post.siteid');

				//验证同一站点不能有相同的名称
				$check_where['siteid'] = array("eq",$rulemodle->siteid);
				$check_where['name'] = $rulemodle->name;
				$check_where['id'] = array('neq',$rulemodle->id);
				if(!!$one = $rulemodle->where($check_where)->find()){
					$this->error(L('_NAME_EXISTS_'));
				}

				if($rulemodle->save()){
					$this->success(L('_SAVE_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($rulemodle->getError());
			}
		} else {
			//验证数据
			$id = I('get.id');
			if(!is_numeric($id)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//获取数据
			$rulemodle = M('RuleModle');
			$where['siteid'] = array('in',array(session('siteid'),0));
			$where['id'] = array('eq',$id);
			$data = $rulemodle->field('id,siteid,pid,name,status,description')->where($where)->find();
			if(!$data){
				$this->error(L('_NODATA_'));
			}
			$this->data = $data;

			//获取菜单列表
			$rulelist_where['status'] = array('eq',1);
			$rulelist_where['siteid'] = array('in',array(session('siteid'),0));
			$rulelist_a = $rulemodle->field('id,name,pid')->where($rulelist_where)->order('sort ASC,id DESC')->select();

			//编辑菜单需要清楚自身菜单以及子类菜单
			$menulist_child = getChilds($rulelist_a,$data['id']);
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
			if(!empty($rulelist_a)){
				foreach ($rulelist_a as $key => $value) {
					if(in_array($value['id'],$idss)){
						unset($rulelist_a[$key]);
					}
				}
			}
			//组装select下拉菜单
			$this->rulelist_a = getSelectedOption($rulelist_a, 0, $data['pid']);

			//获取参数
			$this->parameter = I('get.parameter');
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
			$rulemodle = M('RuleModle');
			if(!empty($ids)){
				foreach ($ids as $key => $value) {
					$data = $rulemodle->field('id')->find($value);
					if(!$data){
						$this->error(L('_NODATA_'));
						break;
					}
				}
			} else {
				$this->error(L('_NOACCESS_DEL_'));
			}

			//删除数据时需要删除子类
			$data = $rulemodle->field('id,pid')->select();
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

			//因为关联了auth_rule表，这里需要验证：如果权限规则表中存在此id则不允许删除
			$authrule = M('AuthRule');
			foreach ($ids as $key => $value) {
				if($authrule->field('id')->where("mid={$value}")->find()){
					unset($ids[$key]);
				}
			}

			//删除有子类id的父级id
			if(!empty($ids)){
				foreach ($ids as $key => $value) {
					if($rulemodle->field('id')->where("pid={$value}")->find()){
						unset($ids[$key]);
					}
				}
			}

			if(empty($ids)){
				$this->error(L('_DATACANNOT_DEL_'));
			}

			//将id数组转成字符串
			$id = implode(",", $ids);

			//删除数据
			if($rulemodle->delete($id)){
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

			$rulemodle = M('RuleModle');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$rulemodle->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}
}
?>
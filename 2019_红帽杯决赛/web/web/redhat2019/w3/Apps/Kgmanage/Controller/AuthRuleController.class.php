<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 规则 ]
*/
namespace Kgmanage\Controller;

class AuthRuleController extends CommonController
{
	/**
	 * [index 规则列表]
	 */
	public function index()
	{
		/*搜索条件*/
		//获取模块列表
		$rulemodule = M('RuleModle');
		$rulemodule_where['status'] = array('eq',1);
		$rulemodule_where['siteid'] = array('in',array(session('siteid')));
		$rulemodule_a = $rulemodule->field('id,name,pid')->where($rulemodule_where)->order('sort ASC,id DESC')->select();
		//组装select下拉菜单
		$this->rulemodule_a = getSelectedOption($rulemodule_a, 0, 0);

		$searchData = I('get.');
		$condition = array();
		if(!empty($searchData)){
			//name
			isset($searchData['name']) ? $condition['name'] = array('like',"%".$searchData['name']."%") : '';
			//title
			isset($searchData['title']) ? $condition['title'] = array('like',"%".$searchData['title']."%") : '';
			//email
			isset($searchData['status']) ? $condition['status'] = array('eq',$searchData['status']) : '';
			//mid
			if(isset($searchData['mid'])){
				$rulemodule_a = getChilds($rulemodule_a,$searchData['mid']);
				$mids[] = $searchData['mid'];
				if(!empty($rulemodule_a)){
					foreach ($rulemodule_a as $key => $value) {
						$mids[] = $value['id'];
					}
				}
				!empty($mids) ? $condition['mid'] = array('IN',$mids) : '';
			}
		}
		//设置where
		if(!empty($condition)){
			$where = $condition;
		} else {
			$where = array();
		}
	/*end 搜索条件*/

		$authrule = D('AuthRule');
		$where['siteid'] = array('in',array(session('siteid')));
		$count = $authrule->field('id')->where($where)->count();

		//获取分页
		$page = getPage($count);
		$this->pagelist = $page->show();
		$this->data = $authrule->field('id,name,sort,status,title,mid')->where($where)->relation('rulemodle')->order('sort ASC,id DESC')->limit($page->firstRow,$page->listRows)->select();

	/*获取url参数*/
		$this->parameter = getParameter(I('get.'),$page);
	/* end 获取url参数*/

		$this->display();
	}

	/**
	 * [search 搜索]
	 */
	public function search()
	{
		if(IS_POST){
			$data = I('post.');
			//加载自定义函数库
			load('myfunction',APP_PATH.'Common/Common');
			$data = clearEmptyData($data);
			//直接跳转
			$this->success(L('_SEARCHING_'),U("index",$data));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [add 新增规则]
	 */
	public function add()
	{
		//数据提交
		if(IS_POST){
			$authrule = D('AuthRule');

			//条件不进行过滤
			//$condition = I('post.condition','',false);

			if($data = $authrule->create()){
				//设置站点
				$authrule->siteid = session('siteid');

				//条件不进行过滤
				$authrule->condition = I('post.condition','',false);
				if($insertid = $authrule->add()){
					//新增排序
					$authrule->where("id = {$insertid}")->setField('sort',$insertid);
					$this->success(L('_ADD_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($authrule->getError());
			}
		} else {
			//获取菜单列表
			$rulemodule = M('RuleModle');
			$rulemodule_where['status'] = array('eq',1);
			$rulemodule_where['siteid'] = array('in',array(session('siteid'),0));
			$rulemodule_a = $rulemodule->field('id,name,pid')->where($rulemodule_where)->order('sort ASC,id DESC')->select();

			//组装select下拉菜单
			$this->rulemodule_a = getSelectedOption($rulemodule_a, 0, 0);

			//获取规则模块
			/*$this->rulemodle = M('RuleModle')->field('id,name')->where('status=1')->select();
			if(empty($this->rulemodle)){
				$this->error(L('_MODULE_ERROR_'));
			}*/

			//获取参数
			$this->parameter = I('get.parameter');

			$this->display();
		}
	}

	/**
	 * [edit 编辑规则]
	 */
	public function edit()
	{
		//提交数据处理
		if(IS_POST){
			$authrule = D('AuthRule');
			if($data = $authrule->create()){
				//设置站点
				$authrule->siteid = I('post.siteid');

				//条件不进行过滤
				$authrule->condition = I('post.condition','',false);

				if($authrule->save()){
					$this->success(L('_SAVE_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($authrule->getError());
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
			$authrule = M('AuthRule');
			$where['siteid'] = array('in',array(session('siteid'),0));
			$where['id'] = array('eq',$id);
			$data = $authrule->field('id,name,siteid,status,title,mid,condition')->where($where)->find();
			if(!$data){
				$this->error(L('_NODATA_'));
			}
			$this->data = $data;

			//获取菜单列表
			$rulemodule = M('RuleModle');
			$rulemodule_where['status'] = array('eq',1);
			$rulemodule_where['siteid'] = array('in',array(session('siteid'),0));
			$rulemodule_a = $rulemodule->field('id,name,pid')->where($rulemodule_where)->order('sort ASC,id DESC')->select();

			//组装select下拉菜单
			$this->rulemodule_a = getSelectedOption($rulemodule_a, 0, $data['mid']);

			//获取规则模块
			/*$this->rulemodle = M('RuleModle')->field('id,name')->where('status=1')->select();
			if(empty($this->rulemodle)){
				$this->error(L('_MODULE_ERROR_'));
			}*/

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
			$authrule = M('AuthRule');
			foreach ($ids as $key => $value) {
				$data = $authrule->field('id')->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//删除数据
			if($authrule->delete($id)){
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

			$authrule = M('AuthRule');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$authrule->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}
}
?>
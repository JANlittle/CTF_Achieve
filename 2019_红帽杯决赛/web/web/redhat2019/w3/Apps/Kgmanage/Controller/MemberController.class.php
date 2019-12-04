<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 会员 ]
*/
namespace Kgmanage\Controller;

class MemberController extends CommonController
{
	/**
	 * [index 列表]
	 * @return [type] [description]
	 */
	public function index()
	{
	/*搜索条件*/
		$searchData = I('get.');
		$condition = array();
		if(!empty($searchData)){
			//score
			isset($searchData['score']) ? $condition['score'] = array('elt',abs(intval($searchData['score']))) : '';
			//lock
			isset($searchData['lock']) ? $condition['lock'] = array('eq',$searchData['lock']) : '';
			//status
			isset($searchData['status']) ? $condition['status'] = array('eq',$searchData['status']) : '';
		}
		//设置where
		if(!empty($condition)){
			$where = $condition;
		} else {
			$where = array();
		}
	/*end 搜索条件*/

		//开始获取数据
		$member = D('Member');

		//固定条件
		$where['siteid'] = array('eq',session('siteid'));

		//统计
		$count = $member->where($where)->count();
		//分页
		$page = getPage($count);
		$this->pagelist = $page->show();
		$data = $member->where($where)->relation('ucmembers')->order('sort ASC,create_time DESC')->limit($page->firstRow,$page->listRows)->select();
		//获取会员组信息
		$membergroup_where['status'] = array('eq',1);
		$membergroup_where['siteid'] = array('eq',session('siteid'));
		$membergroup = M('Membergroup')->where($membergroup_where)->order('ltscore ASC')->select();

		//设置会员组
		if(!empty($data)){
			foreach ($data as $key => $value) {
				//解析地区
				$data[$key]['last_login_area'] = unserialize($value['last_login_area']);

				//解析头像
				$data[$key]['thumb'] = unserialize($value['thumb']);

				//解析ucenter头像
				$data[$key]['ucmembers']['thumb'] = unserialize($value['ucmembers']['thumb']);

				//设置会员组
				if(!empty($membergroup)){
					foreach ($membergroup as $key_1 => $value_1) {
						if($value['score'] <= $value_1['ltscore']){
							$data[$key]['membergroup'] = $value_1['name'];
							break;
						}
					}
				}

			}
		}

		$this->data = $data;

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
	 * [edit 编辑规则]
	 */
	public function edit()
	{
		//提交数据处理
		if(IS_POST){
			$member = D('Member');
			if($data = $member->create()){
				$member->update_time = time();
				if($member->save()){
					$this->success(L('_SAVE_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($member->getError());
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
			$member = D('Member');
			$where['siteid'] = array('eq',session('siteid'));
			$where['uid'] = array('eq',$id);
			$data = $member->relation('ucmembers')->where("uid = {$id}")->find();
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

			$pictures_files_arr = array(); //附件、图片数组

			//验证数据
			$member = M('Member');
			foreach ($ids as $key => $value) {
				$data = $member->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
				//设置附件与图像
				$pictures_files_arr[] = unserialize($data['thumb']);
			}

			//删除数据
			if($member->delete($id)){
				//删除图片
				delfilefun($pictures_files_arr);
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

			$member = M('Member');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['uid'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$member->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [membergroup 会员组列表]
	 */
	public function membergroup()
	{
		$pid = I('get.pid',0);
		$membergroup = M('Membergroup');

		$membergroup_where['siteid'] = array('eq',session('siteid'));
		$membergroup_where['pid'] = array('eq',$pid);
		$count = $membergroup->field('id')->where($membergroup_where)->count();

		//获取分页
		$page = getPage($count);
		$this->pagelist = $page->show();
		$data = $membergroup->where($membergroup_where)->order('ltscore ASC')->limit($page->firstRow,$page->listRows)->select();

		//获取会员组下会员个数
		$member = M('Member');
		if(!empty($data)){
			//开始位置
			$start = 0;
			foreach ($data as $key => $value) {
				$end = $value['ltscore'];
				$where['score'] = array(array('gt',$start),array('elt',$end));
				$data[$key]['count'] = $member->where($where)->count('uid');
				//设置start的值
				$start = $value['ltscore'];
			}
		}

		//获取参数
		$this->parameter = I('get.parameter');

		//设置分页
		$this->linkcate_parameter =  getParameter(I('get.'),$page);

		$this->data = $data;
		$this->display();
	}

	/**
	 * [membergroupadd 新增会员组]
	 */
	public function membergroupadd()
	{
		//数据提交
		if(IS_POST){
			$Membergroup = D('Membergroup');
			if($data = $Membergroup->create()){
				$Membergroup->create_time = time();
				$Membergroup->siteid = session('siteid');
				$Membergroup->ltscore = intval($Membergroup->ltscore);
				if($insertid = $Membergroup->add()){
					//新增排序
					$Membergroup->where("id = {$insertid}")->setField('sort',$insertid);
					$this->success(L('_ADD_SUCCESS_'),U('membergroup',decode(I('post.linkcate_parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($Membergroup->getError());
			}
		} else {
			//获取参数
			$this->linkcate_parameter = I('get.linkcate_parameter');
			$this->display();
		}
	}

	/**
	 * [membergroupedit 编辑会员组]
	 */
	public function membergroupedit()
	{
		//提交数据处理
		if(IS_POST){
			$Membergroup = D('Membergroup');
			if($data = $Membergroup->create()){
				$Membergroup->update_time = time();
				$Membergroup->ltscore = intval($Membergroup->ltscore);
				//保存数据
				if($Membergroup->save()){
					$this->success(L('_SAVE_SUCCESS_'),U('membergroup',decode(I('post.linkcate_parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($Membergroup->getError());
			}
		} else {
			//验证数据
			$id = I('get.id');
			if(!is_numeric($id)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//获取参数
			$this->linkcate_parameter = I('get.linkcate_parameter');

			//获取数据
			$Membergroup = M('Membergroup');

			$membergroup_where['siteid'] = array('eq',session('siteid'));
			$membergroup_where['id'] = array('eq',$id);
			$data = $Membergroup->where($membergroup_where)->find();
			if(!$data){
				$this->error(L('_NODATA_'));
			}
			$this->data = $data;
			$this->display();
		}
	}

	/**
	 * [membergroupdel 删除会员组]
	 */
	public function membergroupdel()
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
			$Membergroup = M('Membergroup');
			foreach ($ids as $key => $value) {
				$data = $Membergroup->field('id')->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//删除数据
			if($Membergroup->delete($id)){
				$this->success(L('_DEL_SUCCESS_'));
			} else {
				$this->error(L('_DEL_ERROR_'));
			}
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [membergroupsort 会员组排序]
	 */
	public function membergroupsort()
	{
		if(IS_POST){
			$sort = I('post.sort');
			$sortarr = explode(",", $sort);

			//验证数据
			if(empty($sortarr)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			$Membergroup = M('Membergroup');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$Membergroup->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}
}
?>
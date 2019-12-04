<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 角色 ]
*/
namespace Kgmanage\Controller;

class AuthGroupController extends CommonController
{
	/**
	 * [index 角色列表]
	 */
	public function index()
	{
		$authgroup = D('AuthGroup');
		$where['siteid'] = array('eq',session('siteid'));
		$count = $authgroup->field('id')->where($where)->count();
		//获取分页
		$page = getPage($count);
		$this->pagelist = $page->show();
		$this->data = $authgroup->field('id,title,sort,status')->where($where)->relation('authgroupaccess')->order('sort ASC,id DESC')->limit($page->firstRow,$page->listRows)->select();

	/*获取url参数*/
		$this->parameter = getParameter(I('get.'),$page);
	/* end 获取url参数*/

		$this->display();
	}

	/**
	 * [add 新增角色]
	 */
	public function add()
	{
		//数据提交
		if(IS_POST){
			$authgroup = D('AuthGroup');
			if($data = $authgroup->create()){
				//设置站点id
				$authgroup->siteid = session('siteid');

				//验证同一站点不能有相同的名称：如果要添加公共配置，请自行到echo_config数据表修改
				$check_where['siteid'] = array("eq",session('siteid'));
				$check_where['title'] = $authgroup->title;
				if(!!$one = $authgroup->where($check_where)->find()){
					$this->error(L('_NAME_EXISTS_'));
				}

				if($insertid = $authgroup->add()){
					//新增排序
					$authgroup->where("id = {$insertid}")->setField('sort',$insertid);
					$this->success(L('_ADD_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($authgroup->getError());
			}
		} else {
			//获取参数
			$this->parameter = I('get.parameter');
			$this->display();
		}
	}

	/**
	 * [edit 编辑角色]
	 */
	public function edit()
	{
		//提交数据处理
		if(IS_POST){
			$authgroup = D('AuthGroup');
			if($data = $authgroup->create()){
				//设置站点id
				$authgroup->siteid = I('post.siteid');

				//验证同一站点不能有相同的名称
				$check_where['siteid'] = array("eq",$authgroup->siteid);
				$check_where['title'] = $authgroup->title;
				$check_where['id'] = array('neq',$authgroup->id);
				if(!!$one = $authgroup->where($check_where)->find()){
					$this->error(L('_NAME_EXISTS_'));
				}

				if($authgroup->save()){
					$this->success(L('_SAVE_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($authgroup->getError());
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
			$authgroup = M('AuthGroup');
			$where['siteid'] = array('eq',session('siteid'));
			$where['id'] = array('eq',$id);
			$this->data = $authgroup->field('id,siteid,title,status,description')->where($where)->find();
			if(!$this->data){
				$this->error(L('_NODATA_'));
			}

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
			$authgroup = D('AuthGroup');
			foreach ($ids as $key => $value) {
				$data = $authgroup->field('id')->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//删除数据时需要删除auth_group_access里面相应的数据
			//删除数据
			if($authgroup->relation('authgroupaccess')->delete($id)){
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

			$authgroup = M('AuthGroup');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$authgroup->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [shouquan 角色授权]
	 */
	public function shouquan()
	{
		if(IS_POST){
			$data = I('post.');
			$rules = $data['rules'];
			/*if(empty($rules)){
				$this->error(L('_SHOUQUAN_ERROR_'));
			}*/

			//设置权限rules
			$rules_str = implode(",", $rules);
			//更新数据
			if(M('AuthGroup')->where("id=".$data['gid']."")->setField("rules",$rules_str)){
				$this->success(L('_SHOUQUAN_SUCCESS_'));
			} else {
				$this->success(L('_SHOUQUAN_FAIL_'));
			}
		} else {
			$id = I('get.id');
			if(!is_numeric($id)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//获取参数
			$this->parameter = I('get.parameter');

			//获取角色数据
			$authgroup = M('AuthGroup');
			$where['siteid'] = array('eq',session('siteid'));
			$where['id'] = array('eq',$id);
			if(!$authinfo = $authgroup->field('id,rules,title')->where($where)->find()){
				$this->error(L('_NODATA_'));
			}

			//处理rules
			$authinfo['rules'] = explode(",", $authinfo['rules']);
			$this->authinfo = $authinfo;

			//获取规则节点1：这种方式太过于零散所以不推荐使用
			/*$authrule = D('AuthRule');
			$data = $authrule->field('id,name,title,mid')->relation('rulemodle')->where('status=1')->order('sort ASC,id DESC')->select();
			$this->data = groupArray($data,'modlename');*/

			//获取规则节点2：这种方式采用无限极组装
			$rulemodle_where['status'] = array('eq',1);
			//筛选出当前站点已经公共部分，如果是公共的角色则筛出全部
			if(session('siteid') != 0){
				$rulemodle_where['siteid'] = array('in',array(0,session('siteid')));
			}
			$rulemodle = D('RuleModle')->field('id,pid,name')->where($rulemodle_where)->order('sort ASC,id DESC')->relation('authrule')->select();
			$this->rulemodle = getAuthRuleList($rulemodle,0,$authinfo['rules']);
			$this->display();
		}
	}

	/**
	 * [adminuser 成员管理]
	 */
	public function adminuser()
	{
		$this->group_id = I('get.id');
		if(!is_numeric($this->group_id)){
			$this->error('_ACCESS_ERROR_');
		}

		//获取拥有当前角色的用户id
		$where['group_id'] = array('eq',$this->group_id);
		$uid = M('AuthGroupAccess')->field('uid')->where($where)->select();

		//uid数组
		$uid_arr =array();
		if(!empty($uid)){
			foreach ($uid as $key => $value) {
				$uid_arr[] = $value['uid'];
			}
		}

		$uid_str = implode(',',$uid_arr);
		//获取成员信息
		$adminuser = M('Adminuser');
		$where_admin['id'] = array('IN',$uid_str);
		$count = $adminuser->where($where_admin)->count();
		$page = getPage($count);
		$this->pagelist = $page->show();
		$this->data = $adminuser->field('id,username,email,last_login_time,last_login_ip,status,mobile,login_count')->where($where_admin)->limit($page->firstRow,$page->listRows)->select();

		//获取参数
		$this->parameter = I('get.parameter');

		$this->display();
	}

	/**
	 * [adminuserdel 移除成员]
	 */
	public function adminuserdel()
	{
		if(IS_POST){
			//验证数据
			$id = I('post.id');
			$ids = explode(",", $id);

			//获取gid
			$group_id = I('post.gid');

			//没有数据
			if(empty($ids) || empty($group_id)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//验证数据
			$AuthGroupAccess = M('AuthGroupAccess');
			foreach ($ids as $key => $value) {
				$where['uid'] = array('eq',$value);
				$where['group_id'] = array('eq',$group_id);
				$data = $AuthGroupAccess->field('uid')->where($where)->find();
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//删除数据
			$del_where['uid'] = array('IN',$ids);
			$del_where['group_id'] = array('eq',$group_id);
			if($AuthGroupAccess->where($del_where)->delete()){
				$this->success(L('_DEL_SUCCESS_'),U('adminuser',array('id'=>$group_id)));
			} else {
				$this->error(L('_DEL_ERROR_'));
			}
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}


	/**
	 * [catshouquan 栏目授权]
	 */
	public function catshouquan()
	{
		if(IS_POST){
			$data = I('post.');
			$postdata = $data['data'];
			/*if(empty($postdata['catids'])){
				$this->error(L('_SHOUQUAN_ERROR_'));
			}*/

			//更新数据
			if(M('AuthGroup')->where("id=".$postdata['gid']."")->setField("catids",$postdata['catids'])){
				$this->success(L('_SHOUQUAN_SUCCESS_'));
			} else {
				$this->success(L('_SHOUQUAN_FAIL_'));
			}
		} else {
			$id = I('get.id');
			if(!is_numeric($id)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//获取角色数据
			$authgroup = M('AuthGroup');
			$where['siteid'] = array('eq',session('siteid'));
			$where['id'] = array('eq',$id);
			if(!$authinfo = $authgroup->field('id,catids,title')->where($where)->find()){
				$this->error(L('_NODATA_'));
			}

			//处理catsid
			$authinfo['catids'] = explode(",", $authinfo['catids']);
			$this->authinfo = $authinfo;

			//获取栏目
			$cat_where['siteid'] = array('eq',session('siteid'));
			$category = M('Category');
			$catinfo = $category->field('id,pid,name')->where($cat_where)->select();
			$catinfo = unlimitedForLayer($catinfo);

			$this->catids = getCateAuthList($catinfo,'child',$authinfo['catids']);
			$this->display();
		}
	}
}
?>
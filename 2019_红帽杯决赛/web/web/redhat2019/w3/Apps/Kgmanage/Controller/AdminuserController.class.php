<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 管理员 ]
*/
namespace Kgmanage\Controller;

class AdminuserController extends CommonController
{
	/**
	 * [index 管理员列表]
	 */
	public function index()
	{
		$adminuser = D('Adminuser');
		//不显示超级管理员
		$where['id'] = array('notin',C('SUPERADMIN'));
		$where['siteid'] = array('eq',session('siteid'));
		$count = $adminuser->field('id')->where($where)->count();
		//获取分页
		$page = getPage($count);
		$this->pagelist = $page->show();
		$this->data = $adminuser->where($where)->relation('authgroupaccess')->order('sort ASC,id DESC')->limit($page->firstRow,$page->listRows)->select();

	/*获取url参数*/
		$this->parameter = getParameter(I('get.'),$page);
	/* end 获取url参数*/
		$this->display();
	}

	/**
	 * [add 新增管理员]
	 */
	public function add()
	{
		//数据提交
		if(IS_POST){
			$adminuser = D('Adminuser');
			if($data = $adminuser->create()){
				//设置站点id
				$adminuser->siteid = session('siteid');

				$adminuser->password = sha1(md5($adminuser->password));
				$adminuser->reg_ip = get_client_ip();
				$adminuser->reg_time = time();
				if($insertid = $adminuser->add()){
					//新增排序
					$adminuser->where("id = {$insertid}")->setField('sort',$insertid);
					$this->success(L('_ADD_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($adminuser->getError());
			}
		} else {
			//获取参数
			$this->parameter = I('get.parameter');
			$this->display();
		}
	}

	/**
	 * [edit 编辑管理员]
	*/
	public function edit()
	{
		//提交数据处理
		if(IS_POST){
			$adminuser = D('Adminuser');
			if($data = $adminuser->create()){
				//设置站点id
				$adminuser->siteid = I('post.siteid');

				//密码不填时不进行验证
				if(!empty($data['password'])){
					$rules = array(
						array('password','require',L('_PASSWORD_MUST_'),0,'regex',2), //密码插入时验证
		    			array('password','6,20',L('_PASSWORDLENTH_ERROR_'),0,'length',2),//密码插入时验证
		    			array('ckpassword','password',L('_CHECKPASSWORD_ERROR_'),0,'confirm',2) //密码插入时验证
					);

					//验证
					$admin = M('Adminuser');
					if(!$admin->validate($rules)->create()){
						$this->error($admin->getError());
					}

					//设置密码
					$adminuser->password = sha1(md5($adminuser->password));
				} else {
					//删除对象里的密码
					unset($adminuser->password);
				}

				//设置更新时间
				$adminuser->update_time = time();

				//保存数据
				if($adminuser->save()){
					$this->success(L('_SAVE_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($adminuser->getError());
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
			$adminuser = M('Adminuser');
			$where['siteid'] = array('eq',session('siteid'));
			$where['id'] = array('eq',$id);
			$this->data = $adminuser->where($where)->find();
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

			//自己不允许删除自己
			if(in_array(session('uid'), $ids)){
				$this->error(L('_DEL_ERROR_'));
			}

			//不允许删除管理员
			$intersect = array_intersect($ids, C("SUPERADMIN"));
			if(!empty($intersect)){
				$this->error(L('_DEL_ERROR_'));
			}

			//验证数据
			$adminuser = D('Adminuser');
			foreach ($ids as $key => $value) {
				$data = $adminuser->field('id')->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//删除数据时需要删除auth_group_access里面相应的数据
			//删除数据
			if($adminuser->relation('authgroupaccess')->delete($id)){
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

			$adminuser = M('Adminuser');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$adminuser->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [shouquan 分配角色]
	 */
	public function fenpei()
	{
		if(IS_POST){
			$data = I('post.');
			$groups = $data['groups'];
			/*if(empty($groups)){
				$this->error(L('_FENPEI_ERROR_'));
			}*/

			//设置数组
			$dataList = array();
			if(!empty($groups)){
				foreach ($groups as $key => $value) {
					$dataList[] = array('uid'=>$data['uid'],'group_id'=>$value);
				}
			}
			//更新数据auth_group_access，先删除所有uid下的角色
			$AuthGroupAccess = M('AuthGroupAccess');
			if($AuthGroupAccess->where("uid = ".$data['uid']."")->find()){
				$deletedata = $AuthGroupAccess->where("uid = ".$data['uid']."")->delete();
			}

			//清空角色时插入数据为空，删除数据后直接返回成功
			if(empty($dataList)){
				$this->success(L('_FENPEI_SUCCESS_'));
			}
			//添加
			if($AuthGroupAccess->addAll($dataList)){
				$this->success(L('_FENPEI_SUCCESS_'),U('index',decode(I('post.parameter'))));
			} else {
				$this->error(L('_FENPEI_FAIL_'));
			}
		} else {
			$id = I('get.id');
			if(!is_numeric($id)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//获取参数
			$this->parameter = I('get.parameter');

			//获取用户数据
			$adminuser = D('Adminuser');
			$where['siteid'] = array('eq',session('siteid'));
			$where['id'] = array('eq',$id);
			if(!$userinfo = $adminuser->field('id,siteid,username')->where($where)->relation('authgroupaccess')->find()){
				$this->error(L('_NODATA_'));
			}

			//设置数据
			if(!empty($userinfo['authgroupaccess'])){
				foreach ($userinfo['authgroupaccess'] as $key => $value) {
					$userinfo['groupid'][] = $value['group_id'];
				}
			}
			$this->userinfo = $userinfo;

			//获取角色组数据
			$authrule = D('AuthGroup');
			if(session('siteid') != 0){
				$authrule_where['siteid'] = array('eq',session('siteid'));
			}
			$authrule_where['status'] = array('eq',1);
			$data = $authrule->field('id,siteid,title')->where($authrule_where)->relation(array('Site'))->order('sort ASC,id DESC')->select();
			$new_data = array();
			if(!empty($data)){
				//进行分组
				foreach ($data as $key => $value) {
					if(empty($value['Site'])){
						$new_data['公共设置'][] = $value;
					} else {
						$new_data[$value['Site']['name']][] = $value;
					}
				}
			}
			$this->data = $new_data;
			$this->display();
		}
	}


	/**
	 * [edit 用户修改资料]
	*/
	public function profile()
	{
		//提交数据处理
		if(IS_POST){
			$oldpassword = I('post.oldpassword','');
			$adminuser = M('Adminuser');

			//验证密码是否与数据库的匹配
			if(empty($oldpassword)){
				$this->error(L('_OLDPASSWORD_ISNEED_'));
			}

			//转换成加密后的密码
			$where['password'] = array('eq',sha1(md5($oldpassword)));
			$where['id'] = array('eq',session('uid'));

			//验证原始密码是否正确
			if(!$adminuser->field('id')->where($where)->find()){
				$this->error(L('_OLDPASSWORD_ERROR_'));
			}

			if($data = $adminuser->create()){

				//密码不填时不进行验证
				if(!empty($data['password'])){
					$rules = array(
						array('password','require',L('_PASSWORD_MUST_'),0,'regex',2), //密码插入时验证
		    			array('password','6,20',L('_PASSWORDLENTH_ERROR_'),0,'length',2),//密码插入时验证
		    			array('ckpassword','password',L('_CHECKPASSWORD_ERROR_'),0,'confirm',2) //密码插入时验证
					);

					//验证
					if(!$adminuser->validate($rules)->create()){
						$this->error($adminuser->getError());
					}

					//设置密码
					$adminuser->password = sha1(md5($adminuser->password));
				} else {
					//删除对象里的密码
					unset($adminuser->password);
				}

				//设置更新时间
				$adminuser->update_time = time();

				//保存数据
				if($adminuser->save()){
					$this->success(L('_SAVE_SUCCESS_'));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($adminuser->getError());
			}
		} else {
			//验证数据
			$id = I('get.id');
			if(!is_numeric($id)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//获取数据
			$adminuser = M('Adminuser');
			//$where['siteid'] = array('eq',session('siteid'));
			$where['id'] = array('eq',$id);
			$this->data = $adminuser->field('id,username,siteid,email,mobile')->where($where)->find();
			if(!$this->data){
				$this->error(L('_NODATA_'));
			}

			$this->display();
		}
	}
}
?>
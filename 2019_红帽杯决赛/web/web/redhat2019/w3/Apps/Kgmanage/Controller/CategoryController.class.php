<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 导航分类 ]
*/
namespace Kgmanage\Controller;

class CategoryController extends CommonController
{
	/**
	 * [index 列表]
	 */
	public function index()
	{
		$pid = I('get.pid',0);
		$where['pid'] = array('eq',$pid);
		$where['siteid'] = array('eq',session('siteid'));

	/*作为栏目权限筛选条件*/	
		//获取用户数据
		$adminuser = D('Adminuser');
		$where_user['siteid'] = array('eq',session('siteid'));
		$where_user['id'] = array('eq',session('uid'));
		$userinfo = $adminuser->field('id')->where($where_user)->relation('authgroup')->find();

		//获取catids数据
		$catids = array(-1);
		if(!empty($userinfo['authgroup'])){
			foreach ($userinfo['authgroup'] as $key => $value) {
				if(!empty($value['catids'])){
					$catids = array_merge_recursive($catids,explode(',',$value['catids']));
				}
			}
		}
		$catids = array_unique($catids);

		//如果是当前用户添加的栏目id是可以不需要验证
		$cate_uid_where['uid'] = array('eq',session('uid'));
		$catids_array_uid = M('Category')->where($cate_uid_where)->field('id')->select();
		if(!empty($catids_array_uid)){
			foreach ($catids_array_uid as $key => $value) {
				if(!is_array($value['id'],$catids)){
					$catids[] = $value['id'];
				}
			}
		}

		//获取包括父级分类所有信息
		$catids_array = M('Category')->field('id,pid')->select();
		$catids_new = array(-1);
		if(!empty($catids_array)){
			foreach ($catids as $key => $value) {
				$all_catids_arr = getParents($catids_array,$value);
				if(!empty($all_catids_arr)){
					foreach ($all_catids_arr as $key_1 => $value_1) {
						$catids_new[] = $value_1['id'];
					}
				}
			}
		}
		$catids = array_unique($catids_new);

		//如果是超级管理员可看全部内容
		if(!in_array(session('uid'),C('ADMINISTRATOR'))){
			$where['id'] = array('in',$catids);

			//用于在Model中关联数据的条件
			C("catson_condition","id IN(".implode(',',$catids).")");
		}
	/*end 作为栏目权限筛选条件*/

		$category = D('Category');
		$count = $category->field('id')->where($where)->count();
		//获取分页
		$page = getPage($count);
		$this->pagelist = $page->show();
		$data = $category->where($where)->relation(array('categorypid','categoryson','sitemodel'))->order('sort ASC,id DESC')->limit($page->firstRow,$page->listRows)->select();

		//处理图片解析
		if(!empty($data)){
			foreach ($data as $key => $value) {
				if(!empty($value['thumb'])){
					$data[$key]['thumb'] = unserialize($value['thumb']);
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
	 * [add 新增]
	 */
	public function add()
	{
		//数据提交
		if(IS_POST){
			$category = D('Category');
			if($data = $category->create()){
				//设置站点id
				$category->siteid = session('siteid');

				//设置管理员id
				$category->uid = session('uid');

				//key重新索引解决冲突
				$category->thumb = array_values($category->thumb); 
				$category->thumb = serialize($category->thumb);

				$category->create_time = time();
				if($insertid = $category->add()){
					//新增排序
					$category->where("id = {$insertid}")->setField('sort',$insertid);
					$this->success(L('_ADD_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($category->getError());
			}
		} else {
			//获取菜单列表
			$category = M('Category');
			$where['status'] = array('eq',1);
			$where['siteid'] = array('eq',session('siteid'));

		/*作为栏目权限筛选条件*/
			//获取用户数据
			$adminuser = D('Adminuser');
			$where_user['siteid'] = array('eq',session('siteid'));
			$where_user['id'] = array('eq',session('uid'));
			$userinfo = $adminuser->field('id')->where($where_user)->relation('authgroup')->find();

			//获取catids数据
			$catids = array(-1);
			if(!empty($userinfo['authgroup'])){
				foreach ($userinfo['authgroup'] as $key => $value) {
					if(!empty($value['catids'])){
						$catids = array_merge_recursive($catids,explode(',',$value['catids']));
					}
				}
			}
			$catids = array_unique($catids);

			//获取包括父级分类所有信息
			$catids_array_where['status'] = array('eq',1);
			$catids_array = $category->field('id,pid')->where($catids_array_where)->select();
			$catids_new = array(-1);
			if(!empty($catids_array)){
				foreach ($catids as $key => $value) {
					$all_catids_arr = getParents($catids_array,$value);
					if(!empty($all_catids_arr)){
						foreach ($all_catids_arr as $key_1 => $value_1) {
							$catids_new[] = $value_1['id'];
						}
					}
				}
			}
			$catids = array_unique($catids_new);

			//如果是当前用户添加的栏目id是可以不需要验证
			$cate_uid_where['uid'] = array('eq',session('uid'));
			$catids_array_uid = M('Category')->where($cate_uid_where)->field('id')->select();
			if(!empty($catids_array_uid)){
				foreach ($catids_array_uid as $key => $value) {
					if(!is_array($value['id'],$catids)){
						$catids[] = $value['id'];
					}
				}
			}

			//如果是超级管理员可看全部内容
			if(!in_array(session('uid'),C('ADMINISTRATOR'))){
				$where['id'] = array('in',$catids);
			}
		/*end 作为栏目权限筛选条件*/

			$menulist_a = $category->field('id,name,pid')->where($where)->order('sort ASC,id DESC')->select();
			//组装select下拉菜单
			$this->menulist_a = getSelectedOption($menulist_a, 0, 0);

			//获取模型列表
			$Sitemodel = M('Sitemodel');
			$sitemodel_where['status'] = array('eq',1);
			$sitemodel_where['siteid'] = array('in',array(0,session('siteid')));
			$sitemodellist = $Sitemodel->field('id,name')->where($sitemodel_where)->order('sort ASC,id DESC')->select();
			//组装select下拉菜单
			$this->sitemodellist = getSelectedOption($sitemodellist, 0, 0,'');
			//获取菜单
			
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
			$category = D('Category');
			if($data = $category->create()){
				//设置站点id
				$category->siteid = I('post.siteid');

				//key重新索引解决冲突
				$category->thumb = array_values($category->thumb); 
				$category->thumb = serialize($category->thumb);

				$category->update_time = time();
				//保存数据
				if($category->save()){
					$this->success(L('_SAVE_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($category->getError());
			}
		} else {
			//验证数据
			$id = I('get.id');
			if(!is_numeric($id)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//获取数据
			$category = M('Category');
			$where_category['siteid'] = array('eq',session('siteid'));
			$where_category['id'] = array('eq',$id);
			$data = $category->where($where_category)->find();
			if(!$data){
				$this->error(L('_NODATA_'));
			}

			//处理图片解析
			if(!empty($data['thumb'])){
				$data['thumb'] = unserialize($data['thumb']);
			}

			$this->data = $data;

			//获取菜单列表
			$where['status'] = array('eq',1);
			$where['siteid'] = array('eq',session('siteid'));

		/*作为栏目权限筛选条件*/
			//获取用户数据
			$adminuser = D('Adminuser');
			$where_user['siteid'] = array('eq',session('siteid'));
			$where_user['id'] = array('eq',session('uid'));
			$userinfo = $adminuser->field('id')->where($where_user)->relation('authgroup')->find();

			//获取catids数据
			$catids = array(-1);
			if(!empty($userinfo['authgroup'])){
				foreach ($userinfo['authgroup'] as $key => $value) {
					if(!empty($value['catids'])){
						$catids = array_merge_recursive($catids,explode(',',$value['catids']));
					}
				}
			}
			$catids = array_unique($catids);

			//如果是当前用户添加的栏目id是可以不需要验证
			$cate_uid_where['uid'] = array('eq',session('uid'));
			$catids_array_uid = M('Category')->where($cate_uid_where)->field('id')->select();
			if(!empty($catids_array_uid)){
				foreach ($catids_array_uid as $key => $value) {
					if(!is_array($value['id'],$catids)){
						$catids[] = $value['id'];
					}
				}
			}

			//获取包括父级分类所有信息
			$catids_array_where['status'] = array('eq',1);
			$catids_array = $category->field('id,pid')->where($catids_array_where)->select();
			$catids_new = array(-1);
			if(!empty($catids_array)){
				foreach ($catids as $key => $value) {
					$all_catids_arr = getParents($catids_array,$value);
					if(!empty($all_catids_arr)){
						foreach ($all_catids_arr as $key_1 => $value_1) {
							$catids_new[] = $value_1['id'];
						}
					}
				}
			}
			$catids = array_unique($catids_new);

			//如果是超级管理员可看全部内容
			if(!in_array(session('uid'),C('ADMINISTRATOR'))){
				$where['id'] = array('in',$catids);
			}
		/*end 作为栏目权限筛选条件*/

			$menulist_a = $category->field('id,name,pid')->where($where)->order('sort ASC,id DESC')->select();

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


			//获取模型列表
			$Sitemodel = M('Sitemodel');
			$sitemodel_where['status'] = array('eq',1);
			$sitemodel_where['siteid'] = array('in',array(0,session('siteid')));
			$sitemodellist = $Sitemodel->field('id,name')->where($sitemodel_where)->order('sort ASC,id DESC')->select();
			//组装select下拉菜单
			$this->sitemodellist = getSelectedOption($sitemodellist, 0, $data['modelid'],'');

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

			$pictures_files_arr = array(); //附件、图片数组
			//验证数据
			$category = D('Category');
			foreach ($ids as $key => $value) {
				$data = $category->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}

				//设置附件与图像
				$pictures_files_arr[] = unserialize($data['thumb']);
			}

			//删除数据时需要删除子类
			$data = $category->field('id,pid')->select();
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

			//删除相应模型下的数据
			$delmodeldata = $category->relation(array('Danyedata','sitemodel'))->select($id);

			//验证数据[由于直接删除栏目直接清空所有内容太过于危险，因此需要限制]
			foreach ($delmodeldata as $key => $value) {
				if(!empty($value['sitemodel'])){
					$checkmodel_where['catid'] = array('eq',$value['id']);
					if(!!$check_cate = M($value['sitemodel']['tablename'])->where($checkmodel_where)->find()){
						$this->error(L('_CATEDEL_ERROR_'));
						break;
					}
				}
			}

			//删除数据
			if($category->relation(array('Danyedata'))->delete($id)){

				//删除模型中的内容
				foreach ($delmodeldata as $key => $value) {
					$delmodel_where['catid'] = array('eq',$value['id']);
					M($value['sitemodel']['tablename'])->where($delmodel_where)->delete();
				}

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

			$category = M('Category');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$category->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}
}
?>
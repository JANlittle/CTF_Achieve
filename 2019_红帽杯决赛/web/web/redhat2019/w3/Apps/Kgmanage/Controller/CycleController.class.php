<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 回收站 ]
*/
namespace Kgmanage\Controller;

class CycleController extends CommonController
{
	/**
	 * [getCateList 获取栏目分类]
	 * @return [type] [description]
	 */
	private function getCateList()
	{
		//获取菜单列表
		$category = M('Category');
		$category_where['siteid'] = array('eq',session('siteid'));
		$category_where['status'] = array('eq',1); //分类状态
		$category_where['modelid'] = array('neq',0); //模型id
		$category_where['danye'] = array('eq',0); //限制单页条件

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

		//如果是超级管理员可看全部内容
		if(!in_array(session('uid'),C('ADMINISTRATOR'))){
			$category_where['id'] = array('in',$catids);
		}
	/*end 作为栏目权限筛选条件*/
	
		$menulist = $category->field('id,modelid,danye,name,pid')->where($category_where)->order('sort ASC,id DESC')->select();
		//获取数据量
		if(!empty($menulist)){
			foreach ($menulist as $key => $value) {
				//获取模型表名
				if(!$sitemodelone = M('Sitemodel')->find($value['modelid'])){
					$menulist[$key]['countnum'] = 0;
					break;
				}
				$cout_where['catid'] = array('eq',$value['id']);
				$cout_where['isdel'] = array('eq',1); //已经进去回收站的数据

				//获取当前用户发布的信息，如果是超级管理员可看全部内容
				if(!in_array(session('uid'),C('ADMINISTRATOR'))){
					$cout_where['uid'] = array('eq',session('uid')); 
				}

				$menulist[$key]['countnum'] = M($sitemodelone['tablename'])->field('id')->where($cout_where)->count();
			}
		}
		$menulist = unlimitedForLayer($menulist);
		return getCateList($menulist);
	}

	/**
	 * [refreshCate 更新左侧栏目]
	 * @return [type] [description]
	 */
	public function refreshCate()
	{
		if(IS_POST){
			$menulist_data = $this->getCateList();
			echo $menulist_data;
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [index 内容首页]
	 */
	public function index()
	{
		//获取菜单
		$this->menulist_show = $this->getCateList();
		$this->display();
	}

	/**
	 * [contentlist 内容列表页面]
	 * @return [type] [description]
	 */
	public function contentlist()
	{
		$this->cid = I('get.id');
		$this->modelid = I('get.modelid');
		//获取菜单
		$this->menulist_show = $this->getCateList();

		//获取当前分类数据
		$catone_where['id'] = array('eq',$this->cid);
		$catone_where['modelid'] = array('eq',$this->modelid);
		$catone_where['siteid'] = array('eq',session('siteid'));
		$cateone = M('Category')->field('id,modelid,name,danye')->where($catone_where)->find();

		if(!is_numeric($this->cid) || !is_numeric($this->modelid)){
			$this->display('contentempty');
		} else {
			//如果是单页则选择单页模板，否则选择默认
			if($cateone['danye']){
				//获取单页信息，如果有该内容则读取并且是保存状态，否则是新增状态
				$danye_where['catid'] = array('eq',$cateone['id']);
				$this->data = M('Danye')->where($danye_where)->find();
				$this->catid = $cateone['id'];
				$this->display('single');
			} else {
				/*搜索条件*/
				$searchData = I('get.');
				$condition = array();
				if(!empty($searchData)){
					//title
					isset($searchData['title']) ? $condition['title'] = array('like',"%".$searchData['title']."%") : '';
					//status
					isset($searchData['status']) ? $condition['status'] = array('eq',$searchData['status']) : '';
					//regdate
					isset($searchData['starttime']) ? $starttime = array('egt',$searchData['starttime']) : $starttime = '';
					isset($searchData['endtime']) ? $endtime = array('elt',$searchData['endtime']) : $endtime = '';
					if(!empty($starttime) || !empty($endtime)){
						$regdate = array();
						if(!empty($starttime)){
							$regdate[] = $starttime;
						}
						if(!empty($endtime)){
							$regdate[] = $endtime;
						}
						$condition['input_time'] = $regdate;
					}
				}
				//设置where
				if(!empty($condition)){
					$where = $condition;
				}
			/*end 搜索条件*/

				//获取模块表名
				$sitemodel_where['id'] = array('eq',$this->modelid);
				$sitemodel_where['siteid'] = array('in',array(0,session('siteid')));
				if(!$model = M('Sitemodel')->where($sitemodel_where)->find()){
					$this->error(L('_ACCESS_ERROR_'));
				}

			/*获取字段*/
				$SitemodelField = M('SitemodelField');
				$SitemodelField_where['modelid'] = array('eq',$this->modelid);
				$SitemodelField_where['listshow'] = array('eq',1);
				$fields = $SitemodelField->where($SitemodelField_where)->order('listsort ASC,id DESC')->select();

				//处理extra成数组
				foreach ($fields as $key => $value) {
					$extra_arr = array();

					//数组方式：array(1,2)，字符串方式：开启:1|关闭:0
					if(!empty($value['extra'])){
						if(preg_match('/^array\((.*)\)$/', $value['extra'])){
							eval("\$fields[\$key]['extra'] = ".$value['extra'].";");
						} else {
							$arr = array();
							$extra = explode("|", $value['extra']);
							foreach ($extra as $key_1 => $value_1) {
								list($k,$v) = explode(":",$value_1);
								$arr[$k] = $v;
								$fields[$key]['extra'] = $arr;
							}
						}
					}
				}

				$this->fields = $fields;
			/*end 获取字段*/	

				//获取数据
				$where['catid'] = array('eq',$this->cid); //初始条件
				$where['isdel'] = array('eq',1); //已经进去回收站的数据

				//获取当前用户发布的信息，如果是超级管理员可看全部内容
				if(!in_array(session('uid'),C('ADMINISTRATOR'))){
					$where['uid'] = array('eq',session('uid')); 
				}

				$table = M($model['tablename']);
				$count = $table->field('id')->where($where)->count();
				//分页
				$page = getPage($count);
				$this->pagelist = $page->show();
				$data = $table->where($where)->order('input_time DESC,sort DESC,update_time DESC,create_time DESC')->limit($page->firstRow,$page->listRows)->select();

				//获取评论
				$comments = M('Comments');
				if(!empty($data)){
					foreach ($data as $key => $value) {
						$comments_where['aid'] = array('eq',$value['id']);
						$comments_where['siteid'] = array('eq',session('siteid'));
						$comments_where['modelid'] = array('eq',$this->modelid);
						$data[$key]['commentscount'] = $comments->where($comments_where)->select();
					}
				}

				$this->data = $data;
				$this->tablename = $model['tablename'];

				$this->display();
			}
		}
	}


	/**
	 * [search 搜索页面]
	 */
	public function search()
	{
		if(IS_POST){
			$data = I('post.');
			//处理时间
			if(!empty($data['starttime'])){
				$data['starttime'] = strtotime($data['starttime']);
			}
			if(!empty($data['endtime'])){
				$data['endtime'] = strtotime($data['endtime']);
			}
			//加载自定义函数库
			load('myfunction',APP_PATH.'Common/Common');
			$data = clearEmptyData($data);

			//返回数据
			$new_data = array();
			$new_data['url'] = U("contentlist",$data);
			$new_data['status'] = 1;
			$new_data['info'] = L('_SEARCHING_');
			$this->ajaxReturn($new_data);
			//直接跳转
			//$this->success(L('_SEARCHING_'),U("contentlist",$data));
		} else {
			$this->cid = I('get.id');
			$this->modelid = I('get.modelid');
			$this->iframe = I('get.iframe');
			$this->display();
		}
	}

	/**
	 * [recycle 还原]
	 */
	public function recycle()
	{
		if(IS_POST){
			//验证数据
			$id = I('post.id');
			$ids = explode(",", $id);

			//没有数据
			if(empty($ids)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			$modelid = I('post.other');
			$sitemodel = M('Sitemodel')->find($modelid);
			$table = M($sitemodel['tablename']);

			//验证数据
			foreach ($ids as $key => $value) {
				$data = $table->field('id')->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//还原当前发布人发布的内容，如果是超级管理员可还原全部内容
			if(!in_array(session('uid'),C('ADMINISTRATOR'))){
				$recycle_where['uid'] = array('eq',session('uid')); 
			}

			//设置isdel为0
			$recycle_where['id'] = array('in',$ids);
			if($table->where($recycle_where)->setField("isdel",0)){
				$this->success(L('_RECYCLE_SUCCESS_'));
			} else {
				$this->error(L('_RECYCLE_ERROR_'));
			}
		} else {
			$this->error(L('_ACCESS_ERROR_'));
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

			//获取模块表名
			$modelid = I('post.other');
			if(!is_numeric($modelid)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			if(!$sitemodel = M('Sitemodel')->find($modelid)){
				$this->error(L('_ACCESS_ERROR_'));
			}
			$table = M($sitemodel['tablename']);

		/*获取字段*/
			$SitemodelField = M('SitemodelField');
			$SitemodelField_where['modelid'] = array('eq',$modelid);
			$fields = $SitemodelField->where($SitemodelField_where)->order('sort ASC,id DESC')->select();
		/*end 获取字段*/

			$pictures_files_arr = array(); //附件、图片数组
			//验证数据
			foreach ($ids as $key => $value) {
				$data = $table->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}

				//设置附件与图像
				if(!empty($fields)){
					foreach ($fields as $key => $value) {
						switch ($value['type']) {
							case 'picture':
								$pictures_files_arr[] = unserialize($data[$value['field']]);
								break;
							case 'thumb':
								$pictures_files_arr[] = unserialize($data[$value['field']]);
								break;	
							case 'file':
								$pictures_files_arr[] = unserialize($data[$value['field']]);
								break;
						}
					}
				}
			}

			//删除当前发布人发布的内容，如果是超级管理员可删除全部内容
			if(!in_array(session('uid'),C('ADMINISTRATOR'))){
				$del_where['uid'] = array('eq',session('uid')); 
			}

			//删除条件
			$del_where['id'] = array('in',$ids);
			if($table->where($del_where)->delete()){
				//删除图片或附件数据
				if(!empty($pictures_files_arr)){
					foreach ($pictures_files_arr as $key => $value) {
						if(!empty($value)){
							foreach ($value as $key_1 => $value_1) {
								if($value_1['location'] == 'upload'){
									if($value_1['type'] == 'images'){
										if(file_exists(".".$value_1['thumb'])){
											unlink(".".$value_1['thumb']);
										}
										if(file_exists(".".$value_1['photo'])){
											unlink(".".$value_1['photo']);
										}
									} elseif($value_1['type'] == 'files') {
										if(file_exists(".".$value_1['filepath'])){
											unlink(".".$value_1['filepath']);
										}
									}
								}
							}
						}
					}
				}

				//删除评论
				$comments = M('Comments');
				$comments_where['siteid'] = array('eq',session('siteid'));
				$comments_where['modelid'] = array('eq',$modelid);
				$comments_where['aid'] = array('in',$ids);
				$comments->where($comments_where)->delete();
				
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
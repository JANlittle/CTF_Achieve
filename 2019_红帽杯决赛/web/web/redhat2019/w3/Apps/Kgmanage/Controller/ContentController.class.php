<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 内容管理 ]
*/
namespace Kgmanage\Controller;

class ContentController extends CommonController
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
				$cout_where['isdel'] = array('eq',0); //去除已经进去回收站的数据

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
				$where['isdel'] = array('eq',0); //去除已经进去回收站的数据

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

			/*获取url参数*/
				$this->parameter = getParameter(I('get.'),$page);
			/*end 获取url参数*/	
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
	 * [single 单页处理]
	 * @return [type] [description]
	 */
	public function single()
	{
		if(IS_POST){
			$validate = array(
				array('title','require',L('_TITLE_MUST_'))
			);

			$danye = M('Danye');
			$method = I('post.method');
			if($danye->validate($validate)->create()){
				//验证新增还是编辑
				if($method == 'add'){
					$danye->create_time = time();
					$danye->uid = session('uid');
					$danye->username = session('username');
					if($danye->add()){
						$this->success(L('_ADD_SUCCESS_'));
					} else {
						$this->error(L('_ADD_ERROR_'));
					}
				} else if($method == 'save'){
					$danye->update_time = time();
					$danye->euid = session('uid');
					$danye->edituser = session('username');
					if($danye->save()){
						$this->success(L('_SAVE_SUCCESS_'));
					} else {
						$this->error(L('_SAVE_ERROR_'));
					}
				}
			} else {
				$this->error($danye->getError());
			}
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [add 新增]
	 */
	public function add()
	{
		//数据提交
		if(IS_POST){
			$modelid = I('post.modelid');
			$cid = I('post.catid');

			$sitemodelinfo = M('Sitemodel')->find($modelid);
			$tablename = $sitemodelinfo['tablename'];
			$model = M($tablename);

			//获取字段
			$SitemodelField = M('SitemodelField');
			$SitemodelField_where['modelid'] = array('eq',$modelid);
			$SitemodelField_where['status'] = array('eq',1);
			//$SitemodelField_where['isshow'] = array('eq',1);
			$fields = $SitemodelField->field('id,field,auto,validate,type')->where($SitemodelField_where)->order('sort ASC,id DESC')->select();

			//自动完成、自动验证
			$validate = array();
			$auto = array();
			foreach ($fields as $key => $value) {
				if(!empty($value['validate'])){
					//解析数组字符串
					eval("\$validate_arr = ".str_replace("field",$value['field'],$value['validate']).";");
					if(is_array($validate_arr)){
						foreach ($validate_arr as $key_1 => $value_1) {
							$validate[] = $value_1;
						}
					}
				}

				if(!empty($value['auto'])){
					//解析数组字符串
					eval("\$auto_arr = ".str_replace("field",$value['field'],$value['auto']).";");
					if(is_array($auto_arr)){
						foreach ($auto_arr as $key_1 => $value_1) {
							$auto[] = $value_1;
						}
					}
				}
			}

			//验证
			if($model->validate($validate)->create()){
				//自动完成
				$data = $model->auto($auto)->create();

				//字段类型数组
				$field_type = array();
				foreach ($fields as $key => $value) {
					//处理checkbox重复的值
					if($value['type'] == 'checkbox'){
						$data[$value['field']] = array_unique($data[$value['field']]);
					}

					//处理相应字段下不同类型的数据
					$field_type[$value['field']] = $value['type'];

					//判断file、picture、thumb类型如果没有数据时
					if($value['type'] == 'picture' || $value['type'] == 'file' || $value['type'] == 'thumb'){
						if(!isset($data[$value['field']])){
							$data[$value['field']] = array();
						}
					}
				}

				//开始处理
				if(!empty($data)){
					foreach ($data as $key => $value) {
						//如果时间
						switch ($field_type[$key]) {
							case 'datetime':
								$data[$key] = strtotime($value);
								break;
							case 'rangetime':
								if(!empty($value)){
									foreach ($value as $key_1 => $value_1) {
										$value[$key_1] = strtotime($value_1);
									}
								}
								$data[$key] = serialize($value);
								break;
							case 'checkbox':
								$data[$key] = serialize($value);
								break;
							case 'password':
								$password = I('post.'.$key);
								if(empty($password)){
									unset($data[$key]);
								}
								break;
							case 'picture':
								$value = array_values($value); //key重新索引解决冲突
								$data[$key] = serialize($value);
								break;
							case 'thumb':
								$value = array_values($value); //key重新索引解决冲突
								$data[$key] = serialize($value);
								break;	
							case 'file':
								$value = array_values($value);//key重新索引解决冲突
								$data[$key] = serialize($value);
								break;
						}
					}
				}

				//创建时间
				$data['create_time'] = time();

				//发布时间
				if(empty($data['input_time']) || !isset($data['input_time'])){
					$data['input_time'] = $data['create_time'];
				}
				//发布人
				$data['uid'] = session('uid');
				$data['username'] = session('username');

				//新增数据
				if($insertid = $model->add($data)){
					//新增排序
					$model->where("id = {$insertid}")->setField('sort',$insertid);
					$this->success(L('_ADD_SUCCESS_'),U('contentlist',decode(I('post.parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($model->getError());
			}
		} else {
			//获取参数
			$this->cid = I('get.cid');
			$this->modelid = I('get.modelid');
			if(!is_numeric($this->cid) || !is_numeric($this->modelid)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//获取参数
			$this->parameter = I('get.parameter');

			//获取模型
			$Sitemodel = M('Sitemodel');
			$Sitemodel_where['status'] = array('eq',1);
			$Sitemodel_where['id'] = array('eq',$this->modelid);
			$Sitemodel_where['siteid'] = array('in',array(0,session('siteid')));
			$model = $Sitemodel->where($Sitemodel_where)->order('sort ASC,id DESC')->find();
			if(empty($model)){
				$this->error(L('_MODULE_CANNOT_FOUND_'));
			}
			$this->model = $model;

			//获取字段
			$SitemodelField = M('SitemodelField');
			$SitemodelField_where['modelid'] = array('eq',$this->modelid);
			$SitemodelField_where['status'] = array('eq',1);
			//$SitemodelField_where['isshow'] = array('eq',1);
			$fields = $SitemodelField->where($SitemodelField_where)->order('sort ASC,id DESC')->select();

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
			$this->display();
		}
	}


	/**
	 * [edit 编辑]
	 */
	public function edit()
	{
		//数据提交
		if(IS_POST){
			$modelid = I('post.modelid');
			$cid = I('post.catid');
			$id = I('post.id');

			$sitemodelinfo = M('Sitemodel')->find($modelid);
			$tablename = $sitemodelinfo['tablename'];
			$model = M($tablename);

			//获取字段
			$SitemodelField = M('SitemodelField');
			$SitemodelField_where['modelid'] = array('eq',$modelid);
			$SitemodelField_where['status'] = array('eq',1);
			//$SitemodelField_where['isshow'] = array('eq',1);
			$fields = $SitemodelField->field('id,field,auto,validate,type')->where($SitemodelField_where)->order('sort ASC,id DESC')->select();

			//自动完成、自动验证
			$validate = array();
			$auto = array();
			foreach ($fields as $key => $value) {
				if(!empty($value['validate'])){
					//解析数组字符串
					eval("\$validate_arr = ".str_replace("field",$value['field'],$value['validate']).";");
					if(is_array($validate_arr)){
						foreach ($validate_arr as $key_1 => $value_1) {
							$validate[] = $value_1;
						}
					}
				}

				if(!empty($value['auto'])){
					//解析数组字符串
					eval("\$auto_arr = ".str_replace("field",$value['field'],$value['auto']).";");
					if(is_array($auto_arr)){
						foreach ($auto_arr as $key_1 => $value_1) {
							$auto[] = $value_1;
						}
					}
				}
			}

			//验证
			if($model->validate($validate)->create()){
				//自动完成
				$data = $model->auto($auto)->create();

				//字段类型数组
				$field_type = array();
				foreach ($fields as $key => $value) {
					//处理checkbox重复的值
					if($value['type'] == 'checkbox'){
						$data[$value['field']] = array_unique($data[$value['field']]);
					}

					//处理相应字段下不同类型的数据
					$field_type[$value['field']] = $value['type'];

					//判断file、picture、thumb类型如果没有数据时
					if($value['type'] == 'picture' || $value['type'] == 'file' || $value['type'] == 'thumb'){
						if(!isset($data[$value['field']])){
							$data[$value['field']] = array();
						}
					}
				}

				//开始处理
				if(!empty($data)){
					foreach ($data as $key => $value) {
						//如果时间
						switch ($field_type[$key]) {
							case 'datetime':
								$data[$key] = strtotime($value);
								break;
							case 'rangetime':
								if(!empty($value)){
									foreach ($value as $key_1 => $value_1) {
										$value[$key_1] = strtotime($value_1);
									}
								}
								$data[$key] = serialize($value);
								break;
							case 'checkbox':
								$data[$key] = serialize($value);
								break;
							case 'password':
								$password = I('post.'.$key);
								if(empty($password)){
									unset($data[$key]);
								}
								break;
							case 'picture':
								$value = array_values($value);//key重新索引解决冲突
								$data[$key] = serialize($value);
								break;
							case 'thumb':
								$value = array_values($value); //key重新索引解决冲突
								$data[$key] = serialize($value);
								break;	
							case 'file':
								$value = array_values($value);//key重新索引解决冲突
								$data[$key] = serialize($value);
								break;
						}
					}
				}

				//更新时间
				$data['update_time'] = time();

				//发布时间
				if(empty($data['input_time']) || !isset($data['input_time'])){
					$data['input_time'] = $data['update_time'];
				}
				//编辑人
				$data['euid'] = session('uid');
				$data['edituser'] = session('username');

				//新增数据
				if($model->save($data)){
					$this->success(L('_SAVE_SUCCESS_'),U('contentlist',decode(I('post.parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($model->getError());
			}
		} else {
			//获取参数
			$this->id = I('get.id');
			$this->cid = I('get.cid');
			$this->modelid = I('get.modelid');
			if(!is_numeric($this->cid) || !is_numeric($this->modelid) || !is_numeric($this->id)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//获取参数
			$this->parameter = I('get.parameter');

			//获取模型
			$Sitemodel = M('Sitemodel');
			$Sitemodel_where['status'] = array('eq',1);
			$Sitemodel_where['id'] = array('eq',$this->modelid);
			$Sitemodel_where['siteid'] = array('in',array(0,session('siteid')));
			$model = $Sitemodel->where($Sitemodel_where)->order('sort ASC,id DESC')->find();
			if(empty($model)){
				$this->error(L('_MODULE_CANNOT_FOUND_'));
			}
			$this->model = $model;

			//获取编辑数据
			$data_where['catid'] = array('eq',$this->cid);
			$data_where['id'] = array('eq',$this->id);

			//获取当前发布人发布的内容，如果是超级管理员可编辑全部内容
			if(!in_array(session('uid'),C('ADMINISTRATOR'))){
				$data_where['uid'] = array('eq',session('uid')); 
			}

			$data = M($model['tablename'])->where($data_where)->find();
			if(!$data){
				$this->error(L('_NODATA_'));
			}
			$this->data = $data;

			//获取字段
			$SitemodelField = M('SitemodelField');
			$SitemodelField_where['modelid'] = array('eq',$this->modelid);
			$SitemodelField_where['status'] = array('eq',1);
			//$SitemodelField_where['isshow'] = array('eq',1);
			$fields = $SitemodelField->where($SitemodelField_where)->order('sort ASC,id DESC')->select();

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

			//删除当前发布人发布的内容，如果是超级管理员可删除全部内容
			if(!in_array(session('uid'),C('ADMINISTRATOR'))){
				$del_where['uid'] = array('eq',session('uid')); 
			}

			//设置isdel为1
			$del_where['id'] = array('in',$ids);
			if($table->where($del_where)->setField("isdel",1)){
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

			$modelid = I('post.other');
			$sitemodel = M('Sitemodel')->find($modelid);
			$table = M($sitemodel['tablename']);
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$table->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [move 移动]
	 * @return [type]        [description]
	 */
	public function move()
	{
		if(IS_POST){
			//执行移动
			$modelid = I('post.modelid');
			$id = I('post.id');
			$catid = I('post.catid');
			if(!is_numeric($catid) || !is_numeric($modelid)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//获取当前模型
			$sitemodel = M('Sitemodel');
			$sitemodelinfo = $sitemodel->find($modelid);

			$table = M($sitemodelinfo['tablename']);
			$ids = explode(",",$id);
			foreach ($ids as $key => $value) {
				$data['catid'] = $catid;
				$data['id'] = $value;
				if(!$table->save($data)){
					$this->error(L('_NODATA_MOVE_'));
					break;
				}
			}
			$this->success(L('_MOVE_SUCCESS_'));

		} else {
			//获取数据
			$this->id = I('get.id');
			$this->catid = I('get.cid');
			$this->modelid = I('get.modelid');
			if(!is_numeric($this->catid) || !is_numeric($this->modelid)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//获取iframe的值，解决iframe不刷新
			$this->iframe = I('get.iframe');

			$category = D('Category');
			//获取当前栏目信息
			$this->one = $category->field('id,modelid,name,pid')->find($this->catid);

			//获取当前模型下的分类

			$category_where['siteid'] = array('eq',session('siteid'));
			$category_where['status'] = array('eq',1); //分类状态
			$category_where['danye'] = array('eq',0); //不是单页
			$category_where['modelid'] = array('neq',0); //模型id

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

			$menulist = $category->field('id,modelid,danye,name,pid')->where($category_where)->relation('sitemodel')->order('sort ASC,id DESC')->select();
			foreach ($menulist as $key => $value) {
				$menulist[$key]['catmodelid'] = $value['sitemodel']['id'];
			}
			$menulist = unlimitedForLayer($menulist);
			$this->menulist_show = getMoveCate($menulist,'child',array('modelid'=>$this->modelid,'catid'=>$this->catid));
			$this->display();
		}
	}

	/**
	 * [comments 文章评论]
	 * @return [type]        [description]
	 */
	public function comments()
	{
		
		/*搜索条件*/
		$searchData = I('get.');
		$condition = array();
		if(!empty($searchData)){
			//title
			isset($searchData['comments']) ? $condition['comments'] = array('like',"%".$searchData['comments']."%") : '';
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
				$condition['create_time'] = $regdate;
			}
		}
		//设置where
		if(!empty($condition)){
			$where = $condition;
		}
	/*end 搜索条件*/
		$this->aid = I('get.aid');
		$this->modelid = I('get.modelid');
		$this->cid = I('get.cid');

		//验证数据
		if(!is_numeric($this->aid) || !is_numeric($this->modelid) || !is_numeric($this->cid)){
			$this->error(L('_ACCESS_ERROR_'));
		}

		//数据条件
		$where['aid'] = array('eq',$this->aid);
		$where['modelid'] = array('eq',$this->modelid);
		$where['siteid'] = array('eq',session('siteid'));

		//模型
		$comments = D('Comments');
		$count = $comments->field('id')->where($where)->count();

		//分页
		$page = getPage($count);
		$this->pagelist = $page->show();
		$data = $comments->where($where)->order('create_time DESC,sort DESC')->relation(array('ucmembers'))->limit($page->firstRow,$page->listRows)->select();

        $this->data = $data;
		$this->display();
	}

	/**
	 * [commentssearch 评论搜索页面]
	 */
	public function commentssearch()
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
			$new_data['url'] = U("comments",$data);
			$new_data['status'] = 1;
			$new_data['info'] = L('_SEARCHING_');
			$this->ajaxReturn($new_data);
			//直接跳转
			//$this->success(L('_SEARCHING_'),U("contentlist",$data));
		} else {
			$this->cid = I('get.cid');
			$this->aid = I('get.aid');
			$this->modelid = I('get.modelid');
			$this->iframe = I('get.iframe');
			$this->display();
		}
	}

	/**
	 * [commentsdel 删除]
	 */
	public function commentsdel()
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
			$Comments = M('Comments');
			$delids = array();
			foreach ($ids as $key => $value) {
				$where['uniqid'] = array('eq',$value);
				$data = $Comments->field('id')->where($where)->find();
				$delids[] = $data['id'];
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//删除数据
			$delids = implode(",", $delids);
			if($Comments->delete($delids)){
				$this->success(L('_DEL_SUCCESS_'));
			} else {
				$this->error(L('_DEL_ERROR_'));
			}
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [commentscheck 评论审核]
	 * @return [type]        [description]
	 */
	public function commentscheck()
	{
		if(IS_POST){
			$uniqid = I('post.data','');
			if(empty($uniqid) || !is_string($uniqid)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			$comments = M('Comments');
			//验证是否存在当前数据
			$where['siteid'] = array('eq',session('siteid'));
			$where['uniqid'] = array('eq',$uniqid);
			if(!$one = $comments->where($where)->find()){
				$this->error(L('_NODATA_'));
			}

			//如果当前的状态是关闭则设置开启，否则关闭
			if($one['status'] == 0){
				$status = 1;
			} else {
				$status = 0;
			}

			if($comments->where($where)->setField('status',$status)){
				$this->success(L('_SHENHE_OK_'));
			} else {
				$this->success(L('_SHENHE_ERROR_'));
			}
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [caiji 数据采集]
	 * @return [type] [description]
	 */
	public function caiji()
	{
		if(IS_POST){
			$postdata = I('post.');
    		if(empty($postdata['url'])){
    			$data['status'] = 0;
    			$data['info'] = '请填写url！';
    			$this->ajaxReturn($data);
    		} else {
    			//判断是否开启分页
    			if(preg_match('/\{\$page\}/', $postdata['url'])){
    				if(is_numeric($postdata['sfenye']) && is_numeric($postdata['efenye']) && $postdata['efenye'] > $postdata['sfenye']){
    					for ($i = $postdata['sfenye']; $i <= $postdata['efenye']; $i++) { 
    						//第一页默认空
    						if($i == 1){
    							$url_arr[] = $postdata['pageoneurl'];
    						} else {
    							$url_arr[] = preg_replace('/\{\$page\}/', $i, $postdata['url']);
    						}
    					}
    				} else {
    					$url_arr[] = $postdata['pageoneurl'];
    				}
    			} else {
    				$url_arr[] = $postdata['url'];
    			}

    			//获取数据
    			$return_content = '';
    			if(!empty($url_arr)){
    				foreach ($url_arr as $key => $value) {
    					//解决编码问题，但必须开启php_mbstring.dll拓展
    					$return_content .= mb_convert_encoding(gethttp($value), 'utf-8', 'GBK,UTF-8,ASCII');
    				}
    			}

	    		$data['content'] = $return_content;
	    		$data['status'] = 1;
	    		$data['info'] = '采集成功！';
	    		$this->ajaxReturn($data);
    		}
		} else {
			$this->iframe = I('get.iframe');
			$this->display();
		}
	}
}
?>
<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 表单向导管理 ]
*/
namespace Kgmanage\Controller;

class FormguideController extends CommonController
{
	/**
	 * [index 列表]
	 */
	public function index()
	{
		$Formguide = D('Formguide');
		$where['siteid'] = array('eq',session('siteid'));
		$count = $Formguide->field('id')->where($where)->count();
		//获取分页
		$page = getPage($count);
		$this->pagelist = $page->show();
		$data = $Formguide->where($where)->relation('FormguideField')->order('sort ASC,id DESC')->limit($page->firstRow,$page->listRows)->select();
		
		//获取数据量
		if(!empty($data)){
			foreach ($data as $key => $value) {
				$data[$key]['countnum'] = M($value['tablename'])->field('id')->count();
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
			$Formguide = D('Formguide');
			if($data = $Formguide->create()){
				//设置站点
				$Formguide->siteid = session('siteid');
				//表名设置为小写
				$Formguide->tablename = strtolower($Formguide->tablename);
				/*
				 *创建模型表逻辑
				 *1、先判断表是否存在
				 *2、判断model.sql是否存在
				*/
				$tables = M()->query("SHOW TABLES");
				$table_name = C('DB_PREFIX') . $Formguide->tablename;
				$tabless = array();
				foreach ($tables as $key => $value) {
					$tabless[] = $value['tables_in_'.strtolower(C('DB_NAME')).''];
				}
				if(in_array($table_name, $tabless)){
					$this->error(L('_TABLENAME_NOACCESS_'));
				}

				//判断sql是否存在
				$Formguide_sql = APP_PATH . MODULE_NAME . "/View/" . CONTROLLER_NAME . "/model.sql";
				if(!file_exists($Formguide_sql)){
					$this->error(L('_FILE_NOEXISTE_'));
				}


				//新增模型
				if($insertid = $Formguide->add()){
					/*
					 *导入sql
					 *2、获取统一模型的sql内容，替换表名称
					 *3、执行sql，创建表
					 *4、在模型字段表中插入相应的默认数据
					*/
					$query = file_get_contents($Formguide_sql);
					$query = str_replace('$table_name',$table_name,$query);
					$query = str_replace('$comment',I('post.name'),$query);
					$query = str_replace('$tableprefix_',C('DB_PREFIX'),$query);
					$query = str_replace('$modelid',$insertid,$query);
					//执行
					M()->execute($query);
					//新增排序
					$Formguide->where("id = {$insertid}")->setField('sort',$insertid);
					$this->success(L('_ADD_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($Formguide->getError());
			}
		} else {
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
			$Formguide = D('Formguide');
			if($data = $Formguide->create()){
				//设置站点id
				$Formguide->siteid = I('post.siteid');

				//表名设置为小写
				$Formguide->tablename = strtolower($Formguide->tablename);
				//获取原始信息
				$Formguide_one = M('Formguide')->field('tablename')->find($Formguide->id);
				/*
				 *创建模型表逻辑
				 *1、先判断表名称是否允许修改
				*/
				$tables = M()->query("SHOW TABLES");
				$table_name = C('DB_PREFIX') . $Formguide->tablename;
				$tabless = array();
				foreach ($tables as $key => $value) {
					if($value['tables_in_'.strtolower(C('DB_NAME')).''] != C('DB_PREFIX') . $Formguide_one['tablename']){
						$tabless[] = $value['tables_in_'.strtolower(C('DB_NAME')).''];
					}
				}
				if(in_array($table_name, $tabless)){
					$this->error(L('_TABLENAME_NOACCESS_'));
				}

				//修改表名
				M()->execute("ALTER TABLE  `".C('DB_PREFIX') . $Formguide_one['tablename']."` RENAME TO `".$table_name."`");
				M()->execute("ALTER TABLE  `".$table_name."` COMMENT '".I('post.name')."'");

				//保存数据
				if($Formguide->save()){
					$this->success(L('_SAVE_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($Formguide->getError());
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
			$Formguide = M('Formguide');
			$where['siteid'] = array('eq',session('siteid'));
			$where['id'] = array('eq',$id);
			$data = $Formguide->where($where)->find();
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

			//保持要删除的表名称数据数组信息
			$tablename_arr = array();
			//验证数据
			$Formguide = M('Formguide');
			foreach ($ids as $key => $value) {
				$data = $Formguide->field('id,tablename')->find($value);
				//保持要删除的表名称数据数组信息
				$tablename_arr[] = $data;
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//删除数据
			if($Formguide->delete($id)){
				/**
				 * 删除数据之后要删除数据表与字段，这里无法删除上传的图片与附件
				*/
				foreach ($tablename_arr as $key => $value) {
					//删除表
					M()->execute("DROP TABLE IF EXISTS `".C('DB_PREFIX') . $value['tablename']."`");

					//删除Formguide_field表中的字段
					M("FormguideField")->where("modelid = ".$value['id']."")->delete();
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

			$Formguide = M('Formguide');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$Formguide->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}


	/**
	 * [fields 字段管理]
	*/
	public function fields()
	{
		$modelid = I('get.id');
		if(!is_numeric($modelid)){
			$this->error(L('_ACCESS_ERROR_'));
		}

		//获取模型数据
		$this->Formguidedata = M('Formguide')->find($modelid);

		//获取字段数据
		$FormguideField = M('FormguideField');
		$where['modelid'] = array('eq',$modelid);
		$where['isshow'] = array('eq',1);
		$count = $FormguideField->field('id')->where($where)->count();
		$page = getPage($count);
		$this->pagelist = $page->show();
		$this->data = $FormguideField->where($where)->order('sort ASC,id DESC')->limit($page->firstRow,$page->listRows)->select();
		
		//获取参数
		$this->parameter = I('get.parameter');

		//设置分页
		$this->linkcate_parameter =  getParameter(I('get.'),$page);

		$this->display();
	}


	/**
	 * [fieldsadd 新增字段]
	 */
	public function fieldsadd()
	{
		//数据提交
		if(IS_POST){
			$FormguideField = D('FormguideField');
			if($data = $FormguideField->create()){
				//验证字段是否存在，如果存在则不允许新增
				$FormguideField_1 = M('FormguideField');
				$where_exist['modelid'] = I('post.modelid');
				$where_exist['field'] = $FormguideField->field;
				if($FormguideField_1->where($where_exist)->find()){
					$this->error('该模型字段已经已经存在！');
				}

				//在模型表中新增字段，---------这里因为execute没有返回值(无法做数据事务回滚)，所以发生错误为了避免新增，多余字段，先执行插入字段--------------
				$tablename = I('post.tablename');
				$default = I('post.default');
				$default =   (empty($default) && $default !== '0') ? '' :  " DEFAULT '".$default."'";
				$remark = I('post.remark');
				$remark =   (empty($remark) && $remark !== '0') ? '' :  " COMMENT '".$remark."'";

				M()->execute("ALTER TABLE  `".C('DB_PREFIX') . $tablename ."` ADD  `".I('post.field')."` ".I('post.field_rule') . $default . $remark);

				//新增模型
				if($insertid = $FormguideField->add()){
					//新增排序
					$FormguideField->where("id = {$insertid}")->setField('sort',$insertid);
					$this->success(L('_ADD_SUCCESS_').$a,U("fields",decode(I('post.linkcate_parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($FormguideField->getError());
			}
		} else {
			$this->tablename = I('get.tablename');
			$this->modelid = I('get.modelid');
			if(!is_numeric($this->modelid)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//获取参数
			$this->linkcate_parameter = I('get.linkcate_parameter');

			$this->display();
		}
	}

	/**
	 * [fieldsedit 字段编辑]
	*/
	public function fieldsedit()
	{
		//提交数据处理
		if(IS_POST){
			$FormguideField = D('FormguideField');
			if($data = $FormguideField->create()){
				//验证字段是否存在，如果存在则不允许编辑
				if($FormguideField->field != I('post.fieldname')){
					$FormguideField_1 = M('FormguideField');
					$where_exist['modelid'] = I('post.modelid');
					$where_exist['field'] = $FormguideField->field;
					if($FormguideField_1->where($where_exist)->find()){
						$this->error('该模型字段已经已经存在！');
					}
				}

				//在模型表中修改字段，---------这里因为execute没有返回值(无法做数据事务回滚)，所以发生错误为了避免新增，多余字段，先执行插入字段--------------
				$tablename = I('post.tablename');
				$default = I('post.default');
				$default =   (empty($default) && $default !== '0') ? '' :  " DEFAULT '".$default."'";
				$remark = I('post.remark');
				$remark =   (empty($remark) && $remark !== '0') ? '' :  " COMMENT '".$remark."'";
				M()->execute("ALTER TABLE  `".C('DB_PREFIX') . $tablename ."` CHANGE  `".I('post.fieldname')."`  `".I('post.field')."` ".I('post.field_rule') . $default . $remark);

				//保存数据
				if($FormguideField->save()){

					$this->success(L('_SAVE_SUCCESS_'),U("fields",decode(I('post.linkcate_parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($FormguideField->getError());
			}
		} else {
			//验证数据
			$id = I('get.id');

			//模型id
			$this->modelid = I('get.modelid');
			if(!is_numeric($id) || !is_numeric($this->modelid)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//获取参数
			$this->linkcate_parameter = I('get.linkcate_parameter');

			//获取表名
			$this->tablename = I('get.tablename');

			//获取数据
			$FormguideField = M('FormguideField');
			$data = $FormguideField->find($id);
			if(!$data){
				$this->error(L('_NODATA_'));
			}
			$this->data = $data;

			$this->display();
		}
	}

	/**
	 * [del 字段删除]
	*/
	public function fieldsdel()
	{
		if(IS_POST){
			//验证数据
			$id = I('post.id');
			$ids = explode(",", $id);

			//没有数据
			if(empty($ids)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//获取模型名称
			$model_tablename = I('post.other');
			if(empty($model_tablename)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//保存要的删除的字段
			$fields_data = array();
			//验证数据
			$FormguideField = M('FormguideField');
			foreach ($ids as $key => $value) {
				$data = $FormguideField->field('id,field')->find($value);
				//保存要的删除的字段
				$fields_data[] = $data;
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//删除数据
			if($FormguideField->delete($id)){
				/**
				 * 删除字段之后要删除数据表相应的字段
				*/
				foreach ($fields_data as $key => $value) {
					M()->execute('ALTER TABLE '. C('DB_PREFIX') . $model_tablename .' DROP column '.$value['field'].'');
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
	 * [fieldssort 字段排序]
	 * @return [type] [description]
	 */
	public function fieldssort()
	{
		if(IS_POST){
			$sort = I('post.sort');
			$sortarr = explode(",", $sort);

			//验证数据
			if(empty($sortarr)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			$FormguideField = M('FormguideField');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$FormguideField->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [datas 内容列表]
	 * @return [type] [description]
	 */
	public function datas()
	{
		$this->modelid = I('get.id');
		if(!is_numeric($this->modelid)){
			$this->error(L('_ACCESS_ERROR_'));
		}

		//获取模块表名
		$Formguide_where['id'] = array('eq',$this->modelid);
		$Formguide_where['siteid'] = array('in',array(0,session('siteid')));
		if(!$model = M('Formguide')->where($Formguide_where)->find()){
			$this->error(L('_ACCESS_ERROR_'));
		}

	/*获取字段*/
		$FormguideField = M('FormguideField');
		$FormguideField_where['modelid'] = array('eq',$this->modelid);
		$FormguideField_where['listshow'] = array('eq',1);
		$fields = $FormguideField->where($FormguideField_where)->order('sort ASC,id DESC')->select();

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
		$table = M($model['tablename']);
		$count = $table->field('id')->count();

		//分页
		$page = getPage($count);
		$this->pagelist = $page->show();
		$data = $table->order('create_time DESC')->limit($page->firstRow,$page->listRows)->select();

		$this->data = $data;

		//获取参数
		$this->parameter = I('get.parameter');

		//设置分页
		$this->linkcate_parameter =  getParameter(I('get.'),$page);

		$this->display();

	}

	/**
	 * [datasdel 数据删除]
	*/
	public function datasdel()
	{
		if(IS_POST){
			$modelid = I('post.other');
			if(!is_numeric($modelid)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//获取模块表名
			$Formguide_where['id'] = array('eq',$modelid);
			$Formguide_where['siteid'] = array('in',array(0,session('siteid')));
			if(!$model = M('Formguide')->where($Formguide_where)->find()){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//验证数据
			$id = I('post.id');
			$ids = explode(",", $id);

			//没有数据
			if(empty($ids)){
				$this->error(L('_ACCESS_ERROR_'));
			}

		/*获取字段*/
			$FormguideField = M('FormguideField');
			$FormguideField_where['modelid'] = array('eq',$modelid);
			$fields = $FormguideField->where($FormguideField_where)->order('sort ASC,id DESC')->select();
		/*end 获取字段*/

			//验证数据
			$table = M($model['tablename']);
			$delids = array();
			$pictures_files_arr = array(); //图片数组
			foreach ($ids as $key => $value) {
				$where['uniqid'] = array('eq',$value);
				$data = $table->where($where)->find();
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
				$delids[] = $data['id'];

				//设置附件与图像
				if(!empty($fields)){
					foreach ($fields as $key => $value) {
						switch ($value['type']) {
							case 'picture':
								$pictures_files_arr[] = unserialize($data[$value['field']]);
								break;
							case 'file':
								$pictures_files_arr[] = unserialize($data[$value['field']]);
								break;
						}
					}
				}
			}

			//删除数据
			$delids = implode(",", $delids);
			if($table->delete($delids)){
				//删除图片或附件数据
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
	 * [datassort 排序]
	 * @return [type] [description]
	 */
	public function datassort()
	{
		if(IS_POST){
			$modelid = I('post.other');
			if(!is_numeric($modelid)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//获取模块表名
			$Formguide_where['id'] = array('eq',$modelid);
			$Formguide_where['siteid'] = array('in',array(0,session('siteid')));
			if(!$model = M('Formguide')->where($Formguide_where)->find()){
				$this->error(L('_ACCESS_ERROR_'));
			}

			$sort = I('post.sort');
			$sortarr = explode(",", $sort);

			//验证数据
			if(empty($sortarr)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			$table = M($model['tablename']);
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
	 * [export 导出数据]
	 * @return [type] [description]
	 */
	public function export()
	{
		$modelid = I('get.modelid');
		$this->modelid = I('get.modelid');
		if(!is_numeric($this->modelid)){
			$this->error(L('_ACCESS_ERROR_'));
		}

		//获取模块表名
		$Formguide_where['id'] = array('eq',$this->modelid);
		$Formguide_where['siteid'] = array('in',array(0,session('siteid')));
		if(!$model = M('Formguide')->where($Formguide_where)->find()){
			$this->error(L('_ACCESS_ERROR_'));
		}

	/*获取字段*/
		$FormguideField = M('FormguideField');
		$FormguideField_where['modelid'] = array('eq',$this->modelid);
		$FormguideField_where['isdetails'] = array('eq',1);
		$fields = $FormguideField->where($FormguideField_where)->order('sort ASC,id DESC')->select();

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
	/*end 获取字段*/	

		//获取出来的字段
		$newfields = "";
		$newname = array();
		if(!empty($fields)){
			foreach ($fields as $key => $value) {
				$newfields .= $value['field'] . ',';
				$newname[] = $value['name'];
			}
		}
		$newfields = substr($newfields, 0, -1);

		//两者不为空
		if(!empty($newfields) && !empty($newname)){
			//获取数据
			$table = M($model['tablename']);
			$data = $table->field($newfields)->select();
			if(empty($data)){
				$this->error(L('_NODATA_'));
			}

			//开始处理数据
			foreach ($data as $key => $value) {
				foreach ($fields as $key_1 => $value_1) {
					switch ($value_1['type']) {
						case 'string':
							if($value_1['field'] == 'guide_ip'){
								//地区
				                $ip = new \Org\Net\IpLocation('UTFWry.dat');
				                $ip_area = $ip->getlocation($value['guide_ip']);

								$data[$key][$value_1['field']] = $data[$key][$value_1['field']] . "(地区：".$ip_area['area'] . " " . $ip_area['country'].")";
							}
							break;
						case 'datetime':
							if(!empty($value[$value_1['field']])){
								$data[$key][$value_1['field']] = date("Y-m-d H:i:s",$data[$key][$value_1['field']]);
							}
							break;
						case 'rangetime':
							$rangetime = unserialize($value[$value_1['field']]);
							
							$range_str = '';
							if(!empty($rangetime[0])){
								$range_str .= date("Y-m-d H:i:s",$rangetime[0]);
							}
							if(!empty($rangetime[1]) && !empty($rangetime[0])){ 
								$range_str .= ' ~ ';
							}
							if(!empty($rangetime[1])){
								$range_str .= date("Y-m-d H:i:s",$rangetime[1]);
							}
							$data[$key][$value_1['field']] = $range_str;
							break;
						case 'bool':
						case 'radio':
						case 'select':
							if(!empty($value_1['extra'])){
								$value_1['extra'] = array_flip($value_1['extra']);
								$data[$key][$value_1['field']] = $value_1['extra'][$data[$key][$value_1['field']]];
                            }
							break;
						case 'checkbox':
							$arr_checkbox = array();
                            $checkbox = unserialize($value[$value_1['field']]);
                            if(!empty($checkbox)){
                                foreach($checkbox as $key_2 => $value_2){
                                    foreach($value_1['extra'] as $key_3 => $value_3){
                                        if($value_3 == $value_2){
                                            $arr_checkbox[] = $key_3;
                                        }
                                    }
                                }
                            }
                            $data[$key][$value_1['field']] = implode(',',$arr_checkbox);
							break;
						case 'linkagemenu':
							$contentlist_linkage = M('Linkagemenu')->find($value[$value_1['field']]);
                            $data[$key][$value_1['field']] = $contentlist_linkage['name']."(".$contentlist_linkage['lettername'].")";
							break;
						case 'picture':
						case 'file':
                            $data[$key][$value_1['field']] = "";
							break;
						case 'uid':
							if($value[$value_1['field']] == 0){
                                $data[$key][$value_1['field']] =  "游客";
                            } else {
                                //获取ucente中心的用户信息
                                $userinfo = M(C('UCENTER_DB_TABLE_MEMBERS'),C('UCENTER_DB_PREFIX'),C('UCENTER_DB_DSN'))->where("uid = " . $value[$value_1['field']])->find();
                                if($userinfo['type'] != 'system'){
                                    $userinfo['username'] = $userinfo['name'];
                                }
                                $data[$key][$value_1['field']] = $userinfo['username']."(".$userinfo['type'].")";
                            }
                            break;
						default:
							$data[$key][$value_1['field']] = "";
							break;
					}
				}
			}

			//导出数据
			exportData($model['tablename'],$newname,$data);
		}
	}

	/**
	 * [datasdetails 内容详细]
	 * @return [type] [description]
	 */
	public function datasdetails()
	{
		$this->modelid = I('get.modelid');
		$this->id = I('get.id');
		if(!is_numeric($this->modelid) || !is_numeric($this->id)){
			$this->error(L('_ACCESS_ERROR_'));
		}

		//获取参数
		$this->linkcate_parameter = I('get.linkcate_parameter');

		//获取模块表名
		$Formguide_where['id'] = array('eq',$this->modelid);
		$Formguide_where['siteid'] = array('in',array(0,session('siteid')));
		if(!$model = M('Formguide')->where($Formguide_where)->find()){
			$this->error(L('_ACCESS_ERROR_'));
		}

	/*获取字段*/
		$FormguideField = M('FormguideField');
		$FormguideField_where['modelid'] = array('eq',$this->modelid);
		$FormguideField_where['isdetails'] = array('eq',1);
		$fields = $FormguideField->where($FormguideField_where)->order('sort ASC,id DESC')->select();

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
		$table = M($model['tablename']);
		$data = $table->find($this->id);

		$this->data = $data;
		$this->display();
	}
}
?>
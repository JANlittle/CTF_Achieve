<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 模型管理 ]
*/
namespace Kgmanage\Controller;

class SitemodelController extends CommonController
{
	/**
	 * [index 列表]
	 */
	public function index()
	{
		$Sitemodel = D('Sitemodel');
		$where['siteid'] = array('eq',session('siteid'));
		$count = $Sitemodel->field('id')->where($where)->count();
		//获取分页
		$page = getPage($count);
		$this->pagelist = $page->show();
		$data = $Sitemodel->field('id,name,type,tablename,sort,status')->where($where)->relation('SitemodelField')->order('sort ASC,id DESC')->limit($page->firstRow,$page->listRows)->select();
		
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
			$Sitemodel = D('Sitemodel');
			if($data = $Sitemodel->create()){
				//设置站点
				$Sitemodel->siteid = session('siteid');
				//表名设置为小写
				$Sitemodel->tablename = strtolower($Sitemodel->tablename);
				/*
				 *创建模型表逻辑
				 *1、先判断表是否存在
				 *2、判断model.sql是否存在
				*/
				$tables = M()->query("SHOW TABLES");
				$table_name = C('DB_PREFIX') . $Sitemodel->tablename;
				$tabless = array();
				foreach ($tables as $key => $value) {
					$tabless[] = $value['tables_in_'.strtolower(C('DB_NAME')).''];
				}
				if(in_array($table_name, $tabless)){
					$this->error(L('_TABLENAME_NOACCESS_'));
				}

				//判断sql是否存在
				$sitemodel_sql = APP_PATH . MODULE_NAME . "/View/" . CONTROLLER_NAME . "/model.sql";
				if(!file_exists($sitemodel_sql)){
					$this->error(L('_FILE_NOEXISTE_'));
				}


				//新增模型
				if($insertid = $Sitemodel->add()){
					/*
					 *导入sql
					 *2、获取统一模型的sql内容，替换表名称
					 *3、执行sql，创建表
					 *4、在模型字段表中插入相应的默认数据
					*/
					$query = file_get_contents($sitemodel_sql);
					$query = str_replace('$table_name',$table_name,$query);
					$query = str_replace('$comment',I('post.name'),$query);
					$query = str_replace('$tableprefix_',C('DB_PREFIX'),$query);
					$query = str_replace('$modelid',$insertid,$query);
					//执行
					M()->execute($query);
					//新增排序
					$Sitemodel->where("id = {$insertid}")->setField('sort',$insertid);
					$this->success(L('_ADD_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($Sitemodel->getError());
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
			$Sitemodel = D('Sitemodel');
			if($data = $Sitemodel->create()){
				//设置站点id
				$Sitemodel->siteid = I('post.siteid');

				//表名设置为小写
				$Sitemodel->tablename = strtolower($Sitemodel->tablename);
				//获取原始信息
				$sitemodel_one = M('Sitemodel')->field('tablename')->find($Sitemodel->id);
				/*
				 *创建模型表逻辑
				 *1、先判断表名称是否允许修改
				*/
				$tables = M()->query("SHOW TABLES");
				$table_name = C('DB_PREFIX') . $Sitemodel->tablename;
				$tabless = array();
				foreach ($tables as $key => $value) {
					if($value['tables_in_'.strtolower(C('DB_NAME')).''] != C('DB_PREFIX') . $sitemodel_one['tablename']){
						$tabless[] = $value['tables_in_'.strtolower(C('DB_NAME')).''];
					}
				}
				if(in_array($table_name, $tabless)){
					$this->error(L('_TABLENAME_NOACCESS_'));
				}

				//修改表名
				M()->execute("ALTER TABLE  `".C('DB_PREFIX') . $sitemodel_one['tablename']."` RENAME TO `".$table_name."`");
				M()->execute("ALTER TABLE  `".$table_name."` COMMENT '".I('post.name')."'");

				//保存数据
				if($Sitemodel->save()){
					$this->success(L('_SAVE_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($Sitemodel->getError());
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
			$Sitemodel = M('Sitemodel');
			$where['siteid'] = array('eq',session('siteid'));
			$where['id'] = array('eq',$id);
			$data = $Sitemodel->field('id,name,siteid,description,tablename,status')->where($where)->find();
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
			$Sitemodel = M('Sitemodel');
			foreach ($ids as $key => $value) {
				$data = $Sitemodel->field('id,tablename')->find($value);
				//保持要删除的表名称数据数组信息
				$tablename_arr[] = $data;
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//删除数据
			if($Sitemodel->delete($id)){
				/**
				 * 删除数据之后要删除数据表与字段，这里无法删除上传的图片与附件
				*/
				foreach ($tablename_arr as $key => $value) {
					//删除表
					M()->execute("DROP TABLE IF EXISTS `".C('DB_PREFIX') . $value['tablename']."`");

					//删除sitemodel_field表中的字段
					M("SitemodelField")->where("modelid = ".$value['id']."")->delete();
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

			$Sitemodel = M('Sitemodel');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$Sitemodel->save($data);
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
		$this->sitemodeldata = M('Sitemodel')->field('id,name,tablename')->find($modelid);

		//获取字段数据
		$SitemodelField = M('SitemodelField');
		$where['modelid'] = array('eq',$modelid);
		$where['isshow'] = array('eq',1);
		$count = $SitemodelField->field('id')->where($where)->count();
		$page = getPage($count);
		$this->pagelist = $page->show();
		$this->data = $SitemodelField->where($where)->order('sort ASC,id DESC')->limit($page->firstRow,$page->listRows)->select();
		
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
			$SitemodelField = D('SitemodelField');
			if($data = $SitemodelField->create()){
				//验证字段是否存在，如果存在则不允许新增
				$SitemodelField_1 = M('SitemodelField');
				$where_exist['modelid'] = I('post.modelid');
				$where_exist['field'] = $SitemodelField->field;
				if($SitemodelField_1->where($where_exist)->find()){
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
				if($insertid = $SitemodelField->add()){
					//新增排序
					$update_data = array('sort'=>$insertid,'listsort'=>$insertid);
                    $SitemodelField->where("id = {$insertid}")->setField($update_data);

					$this->success(L('_ADD_SUCCESS_').$a,U("fields",decode(I('post.linkcate_parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($SitemodelField->getError());
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
			$SitemodelField = D('SitemodelField');
			if($data = $SitemodelField->create()){
				//验证字段是否存在，如果存在则不允许编辑
				if($SitemodelField->field != I('post.fieldname')){
					$SitemodelField_1 = M('SitemodelField');
					$where_exist['modelid'] = I('post.modelid');
					$where_exist['field'] = $SitemodelField->field;
					if($SitemodelField_1->where($where_exist)->find()){
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
				if($SitemodelField->save()){

					$this->success(L('_SAVE_SUCCESS_'),U("fields",decode(I('post.linkcate_parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($SitemodelField->getError());
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
			$SitemodelField = M('SitemodelField');
			$data = $SitemodelField->find($id);
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
			$SitemodelField = M('SitemodelField');
			foreach ($ids as $key => $value) {
				$data = $SitemodelField->field('id,field')->find($value);
				//保存要的删除的字段
				$fields_data[] = $data;
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//删除数据
			if($SitemodelField->delete($id)){
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

			$SitemodelField = M('SitemodelField');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$SitemodelField->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}
}
?>
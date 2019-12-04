<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 系统配置 ]
*/
namespace Kgmanage\Controller;

class SystemController extends CommonController
{
	/**
	 * [index 列表]
	 */
	public function index()
	{
		$config = M('Config');
		$this->group = I('get.group',1);
		$where['group'] = array('eq',$this->group);
		$where['siteid'] = array('eq',session("siteid"));
		$data = $config->field('id,name,title,sort,value,type,group,status,remark')->where($where)->order('sort ASC,id DESC')->select();
		if(!empty($data)){
			foreach ($data as $key => $value) {
				$data[$key]['remark'] = str_replace(array("\r\n", "\r", "\n"),"<br>",$value['remark']);
			}
		}
		$this->data = $data;

		//系统设置禁用设置
		if($this->group == 4 && session('siteid') == 0 && !in_array(session('uid'),C('SUPERADMIN'))){
			$this->show_del_edit = 0;
		} else {
			$this->show_del_edit = 1;
		}

	/*获取url参数*/
		$this->parameter = getParameter(I('get.'));
	/* end 获取url参数*/
	
		$this->display();
	}

	/**
	 * [add 新增]
	 */
	public function add()
	{
		//处理数据
		if(IS_POST){
			$config = D('System');
			if($data = $config->create()){
				//设置站点id
				$config->siteid = session('siteid');

				//验证同一站点不能有相同的名称：如果要添加公共配置，请自行到echo_config数据表修改
				$check_where['siteid'] = array("eq",session('siteid'));
				$check_where['name'] = $config->name;
				if(!!$one = $config->where($check_where)->find()){
					$this->error(L('_NAME_EXISTS_'));
				}

				if($insertid = $config->add()){
					//设置排序
					$config->where("id = {$insertid}")->setField('sort',$insertid);
					$this->success(L("_ADD_SUCCESS_"),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($config->getError());
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
			$config = D('System');
			if($data = $config->create()){
				//设置站点id
				$config->siteid = I('post.siteid');

				//验证同一站点不能有相同的名称：如果要添加公共配置，请自行到echo_config数据表修改
				$check_where['siteid'] = array("eq",$config->siteid);
				$check_where['name'] = $config->name;
				$check_where['id'] = array('neq',$config->id);
				if(!!$one = $config->where($check_where)->find()){
					$this->error(L('_NAME_EXISTS_'));
				}

				if($config->save()){
					$this->success(L('_SAVE_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($config->getError());
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
			$config = M('Config');
			$where['siteid'] = array('eq',session('siteid'));
			$where['id'] = array('eq',$id);
			$data = $config->field('id,siteid,name,title,status,title,value,remark,group,type')->where($where)->find();
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

			//验证数据
			$config = M('Config');
			foreach ($ids as $key => $value) {
				$data = $config->field('id')->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//删除数据
			if($config->delete($id)){
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

			$config = M('Config');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$config->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [setConfig 生成配置文件]
	 */
	public function setConfig()
	{
		if(IS_POST){
			//修改数据
			$update_data = I('post.config');
			$upconfig = M('Config');
			$update_where = array();
			if(!empty($update_data)){
				foreach ($update_data as $key => $value) {
					$update_where = array(
						'id' => $key,
						'value' => $value
					);
					$upconfig->save($update_where);
				}
			}
			//生成配置缓存
			setConfig('ALL_CONFIG'.session("siteid"),array(0,session("siteid")));
			$this->success(L('_SETCONFIG_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

}
?>
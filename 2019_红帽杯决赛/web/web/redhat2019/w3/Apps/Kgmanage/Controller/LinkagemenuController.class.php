<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 联动菜单 ]
*/
namespace Kgmanage\Controller;

class LinkagemenuController extends CommonController
{
	/**
	 * [index 列表]
	 */
	public function index()
	{
		$pid = I('get.pid',0);
		$Linkagemenu = D('Linkagemenu');
		$count = $Linkagemenu->field('id')->where("pid = ".$pid ."")->count();
		//获取分页
		$page = getPage($count);
		$this->pagelist = $page->show();
		$this->data = $Linkagemenu->where("pid = ".$pid ."")->relation(array('linkagetype','linkagemenuparent','linkagemenuson'))->order('sort ASC,id DESC')->limit($page->firstRow,$page->listRows)->select();

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
			$Linkagemenu = D('Linkagemenu');
			if($data = $Linkagemenu->create()){
				//判断联动菜单
				if(!$Linkagemenu->typeid){
					$this->error(L('_MUST_LINKAGE_TYPE_'));
				}
				if($insertid = $Linkagemenu->add()){
					//新增排序
					$Linkagemenu->where("id = {$insertid}")->setField('sort',$insertid);
					$this->success(L('_ADD_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($Linkagemenu->getError());
			}
		} else {

			//获取类型
			$LinkagemenuType = M('LinkagemenuType');
			$LinkagemenuType_data = $LinkagemenuType->field('id,name,pid')->where('status = 1')->order('sort ASC,id DESC')->select();
			//组装select下拉菜单
			$this->linkagemenutype= getSelectedOption($LinkagemenuType_data, 0, 0,'');

			//获取菜单列表
			$Linkagemenu = M('Linkagemenu');
			$where['status'] = array('eq',1);
			if(!empty($LinkagemenuType_data)){
				$where['typeid'] = array('eq',$LinkagemenuType_data[0]['id']);
			}
			$menulist_a = $Linkagemenu->field('id,name,pid')->where($where)->order('sort ASC,id DESC')->select();
			//组装select下拉菜单
			$this->menulist_a = getSelectedOption($menulist_a, 0, 0);

			//获取参数
			$this->parameter = I('get.parameter');

			//获取菜单
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
			$Linkagemenu = D('Linkagemenu');
			if($data = $Linkagemenu->create()){
				//判断联动菜单
				if(!$Linkagemenu->typeid){
					$this->error(L('_MUST_LINKAGE_TYPE_'));
				}
				//保存数据
				if($Linkagemenu->save()){
					$this->success(L('_SAVE_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($Linkagemenu->getError());
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
			$Linkagemenu = M('Linkagemenu');
			$data = $Linkagemenu->field('id,name,description,lettername,typeid,pid,status')->find($id);
			if(!$data){
				$this->error(L('_NODATA_'));
			}
			$this->data = $data;

			//获取菜单列表
			$where['status'] = array('eq',1);
			$where['typeid'] = array('eq',$data['typeid']);
			$menulist_a = $Linkagemenu->field('id,name,pid')->where($where)->order('sort ASC,id DESC')->select();

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

			//获取类型
			$LinkagemenuType = M('LinkagemenuType');
			$LinkagemenuType_data = $LinkagemenuType->field('id,name,pid')->where('status = 1')->order('sort ASC,id DESC')->select();
			//组装select下拉菜单
			$this->linkagemenutype= getSelectedOption($LinkagemenuType_data, 0, $data['typeid'],'');

			//组装select下拉菜单
			$this->menulist_a = getSelectedOption($menulist_a, 0, $data['pid']);
			$this->display();
		}
	}

	/**
	 * [getMenulist description]
	 * @return [type] [description]
	 */
	public function getMenulist()
	{
		if(IS_POST){
			$typeid = I('post.typeid');
			$menuid = I('post.menuid');
			if(!is_numeric($typeid)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//菜单id可有可无
			if(!empty($menuid)){
				if(!is_numeric($menuid)){
					$this->error(L('_ACCESS_ERROR_'));
				}
			}

			//获取菜单列表
			$Linkagemenu = M('Linkagemenu');
			$where['typeid'] = array('eq',$typeid);
			$where['status'] = array('eq',1);
			if(!empty($menuid)){
				$where['id'] = array('neq',$menuid);
			}
			$menulist_a = $Linkagemenu->field('id,name,pid')->where($where)->order('sort ASC,id DESC')->select();
			//组装select下拉菜单
			$menulist = getSelectedOption($menulist_a, 0, 0);
			$data['info'] = '获取菜单成功！';
			$data['data'] = $menulist;
			$data['status'] = 1;
			$this->ajaxReturn($data,'json');
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

			//验证数据
			$Linkagemenu = M('Linkagemenu');
			foreach ($ids as $key => $value) {
				$data = $Linkagemenu->field('id')->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//删除数据时需要删除子类
			$data = $Linkagemenu->field('id,pid')->select();
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
			//删除数据
			if($Linkagemenu->delete($id)){
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

			$Linkagemenu = M('Linkagemenu');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$Linkagemenu->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [menutype 类型列表]
	 */
	public function menutype()
	{
		$pid = I('get.pid',0);
		$LinkagemenuType = M('LinkagemenuType');
		$count = $LinkagemenuType->field('id')->where("pid = ".$pid)->count();
		//获取分页
		$page = getPage($count);
		$this->pagelist = $page->show();
		$data = $LinkagemenuType->field('id,name,sort,status,pid')->where("pid = ".$pid)->order('sort ASC,id DESC')->limit($page->firstRow,$page->listRows)->select();

		//获取参数
		$this->parameter = I('get.parameter');

		//设置分页
		$this->linkcate_parameter =  getParameter(I('get.'),$page);

		$this->data = $data;
		$this->display();
	}

	/**
	 * [menutypeadd 新增类型]
	 */
	public function menutypeadd()
	{
		//数据提交
		if(IS_POST){
			$LinkagemenuType = D('LinkagemenuType');
			if($data = $LinkagemenuType->create()){
				$LinkagemenuType->create_time = time();
				if($insertid = $LinkagemenuType->add()){
					//新增排序
					$LinkagemenuType->where("id = {$insertid}")->setField('sort',$insertid);
					$this->success(L('_ADD_SUCCESS_'),U('menutype',decode(I('post.linkcate_parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($LinkagemenuType->getError());
			}
		} else {

			//获取参数
			$this->linkcate_parameter = I('get.linkcate_parameter');

			$this->display();
		}
	}

	/**
	 * [menutypeedit 编辑类型]
	 */
	public function menutypeedit()
	{
		//提交数据处理
		if(IS_POST){
			$LinkagemenuType = D('LinkagemenuType');
			if($data = $LinkagemenuType->create()){
				$LinkagemenuType->update_time = time();
				//保存数据
				if($LinkagemenuType->save()){
					$this->success(L('_SAVE_SUCCESS_'),U('menutype',decode(I('post.linkcate_parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($LinkagemenuType->getError());
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
			$LinkagemenuType = M('LinkagemenuType');
			$data = $LinkagemenuType->field('id,name,status,description')->find($id);
			if(!$data){
				$this->error(L('_NODATA_'));
			}
			$this->data = $data;
			$this->display();
		}
	}

	/**
	 * [menutypedel 删除类型]
	 */
	public function menutypedel()
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
			$LinkagemenuType = M('LinkagemenuType');
			foreach ($ids as $key => $value) {
				$data = $LinkagemenuType->field('id')->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//验证分类下是否有数据
			$Linkagemenu = M('Linkagemenu');
			$Linkagemenu_where['typeid'] = array('in',$ids);
			if($Linkagemenu->where($Linkagemenu_where)->field('id')->find()){
				$this->error(L('_EXISIST_CATE_ERROR_'));
			}

			//删除数据
			if($LinkagemenuType->delete($id)){
				$this->success(L('_DEL_SUCCESS_'));
			} else {
				$this->error(L('_DEL_ERROR_'));
			}
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [menutypesort 类型排序]
	 */
	public function menutypesort()
	{
		if(IS_POST){
			$sort = I('post.sort');
			$sortarr = explode(",", $sort);

			//验证数据
			if(empty($sortarr)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			$LinkagemenuType = M('LinkagemenuType');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$LinkagemenuType->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}
}
?>
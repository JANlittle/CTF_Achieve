<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 友情链接 ]
*/
namespace Kgmanage\Controller;

class LinkController extends CommonController
{
	/**
	 * [index 列表]
	 */
	public function index()
	{
		$this->catid = I('get.id');
		//获取图片分类信息
		$cat_where['id'] = array('eq',$this->catid);
		$cat_where['siteid'] = array('eq',session('siteid'));
		if(!$this->cate = M('Linkcate')->where($cat_where)->find()){
			$this->error(L('_ACCESS_ERROR_'));
		}

		$link = D('Link');
		$where['siteid'] = array('eq',session('siteid'));
		$where['catid'] = array('eq',$this->catid);
		$count = $link->field('id')->where($where)->count();

		//获取分页
		$page = getPage($count);
		$this->pagelist = $page->show();
		$data = $link->where($where)->relation('linkcate')->order('sort ASC,id DESC')->limit($page->firstRow,$page->listRows)->select();
		
		//处理图片解析
		if(!empty($data)){
			foreach ($data as $key => $value) {
				if(!empty($value['thumb'])){
					$data[$key]['thumb'] = unserialize($value['thumb']);
				}
			}
		}
		$this->data = $data;

		//获取参数
		$this->linkcate_parameter = I('get.linkcate_parameter');

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
			$link = D('Link');
			if($data = $link->create()){
				//设置站点id
				$link->siteid = session('siteid');

				//验证同一站点不能有相同的名称
				/*$check_where['siteid'] = array("eq",session('siteid'));
				$check_where['title'] = $link->title;
				if(!!$one = $link->where($check_where)->find()){
					$this->error(L('_NAME_EXISTS_'));
				}*/

				//获取表单的类型
				$linktype = I('post.linktype');
				//如果是图片就验证上传
				if($linktype == 1){
					//key重新索引解决冲突
					$link->thumb = array_values($link->thumb); 
					$link->thumb = serialize($link->thumb);
				} else {
					$link->thumb = "";
				}
				//执行保存
				$link->create_time = time(); //创建时间
				if($insertId = $link->add()){
					//设置排序
					$link->where('id='.$insertId.'')->setField('sort',$insertId);
					$this->success(L('_ADD_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($link->getError());
			}
		} else {
			//获取分类
			$this->catid = I('get.catid');
			if(!is_numeric($this->catid) ){
				$this->error(L('_ACCESS_ERROR_'));
			}

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
			$link = D('Link');
			if($data = $link->create()){
				//设置站点id
				$link->siteid = I('post.siteid');

				//获取原始数据
				$original_where['siteid'] = array("eq",$link->siteid);
				$original_where['id'] = array('eq',$link->id);
				if(!$original_data = M('Link')->where($original_where)->find()){
					$this->error(L('_ACCESS_ERROR_'));
				}

				//验证同一站点不能有相同的名称
				/*$check_where['siteid'] = array("eq",$link->siteid);
				$check_where['title'] = $link->title;
				$check_where['id'] = array('neq',$link->id);
				if(!!$one = $link->where($check_where)->find()){
					$this->error(L('_NAME_EXISTS_'));
				}*/

				//获取表单的类型
				$linktype = I('post.linktype');

				//如果是图片就验证上传
				if($linktype == 1){
					//key重新索引解决冲突
					$link->thumb = array_values($link->thumb); 
					$link->thumb = serialize($link->thumb);
				} else {
					$link->thumb = "";
				}

				//设置修改时间
				$link->update_time = time();
				if($link->save()){
					if($linktype != 1){
						//删除图片或附件数据
						$pictures_files_arr[] = unserialize($original_data['thumb']);
						delfilefun($pictures_files_arr);
					}
					//跳转
					$this->success(L('_SAVE_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($link->getError());
			}
		} else {
			//验证数据
			$id = I('get.id');
			$this->catid = I('get.catid');
			if(!is_numeric($id) || !is_numeric($this->catid) ){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//获取参数
			$this->parameter = I('get.parameter');

			//获取数据
			$link = M('Link');
			$link_where['siteid'] = array('eq',session('siteid'));
			$link_where['id'] = array('eq',$id);
			$data = $link->where($link_where)->find();
			if(!$data){
				$this->error(L('_NODATA_'));
			}

			//处理图片解析
			if(!empty($data['thumb'])){
				$data['thumb'] = unserialize($data['thumb']);
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
			$link = M('Link');
			foreach ($ids as $key => $value) {
				$data = $link->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}

				//设置附件与图像
				$pictures_files_arr[] = unserialize($data['thumb']);
			}

			//删除数据
			if($link->delete($id)){
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

			$link = M('Link');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$link->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [cate 友情链接分类]
	 */
	public function linkcate()
	{
		$pid = I('get.pid',0);
		$linkcate = D('Linkcate');
		$where['pid'] = array('eq',$pid);
		$where['siteid'] = array('eq',session('siteid'));
		$count = $linkcate->field('id')->where($where)->count();
		//获取分页
		$page = getPage($count);
		$this->pagelist = $page->show();
		$this->data = $linkcate->where($where)->relation(array('links'))->order('sort ASC,id DESC')->limit($page->firstRow,$page->listRows)->select();

		//获取参数
		$this->parameter = I('get.parameter');

		//设置分页
		$this->linkcate_parameter =  getParameter(I('get.'),$page);

		$this->display();
	}


	/**
	 * [add 分类新增]
	 */
	public function linkcateadd()
	{
		//数据提交
		if(IS_POST){
			$linkcate = D('Linkcate');
			if($data = $linkcate->create()){
				//设置站点id
				$linkcate->siteid = session('siteid');

				//验证同一站点不能有相同的名称
				$check_where['siteid'] = array("eq",session('siteid'));
				$check_where['name'] = $linkcate->name;
				if(!!$one = $linkcate->where($check_where)->find()){
					$this->error(L('_NAME_EXISTS_'));
				}

				$linkcate->create_time = time();
				if($insertid = $linkcate->add()){
					//新增排序
					$linkcate->where("id = {$insertid}")->setField('sort',$insertid);
					$this->success(L('_ADD_SUCCESS_'),U('linkcate',decode(I('post.linkcate_parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($linkcate->getError());
			}
		} else {
			//获取参数
			$this->linkcate_parameter = I('get.linkcate_parameter');

			$this->display();
		}
	}

	/**
	 * [edit 分类编辑]
	*/
	public function linkcateedit()
	{
		//提交数据处理
		if(IS_POST){
			$linkcate = D('Linkcate');
			if($data = $linkcate->create()){
				//设置站点id
				$linkcate->siteid = I('post.siteid');

				//验证同一站点不能有相同的名称
				$check_where['siteid'] = array("eq",$linkcate->siteid);
				$check_where['name'] = $linkcate->name;
				$check_where['id'] = array('neq',$linkcate->id);
				if(!!$one = $linkcate->where($check_where)->find()){
					$this->error(L('_NAME_EXISTS_'));
				}

				$linkcate->update_time = time();
				//保存数据
				if($linkcate->save()){
					$this->success(L('_SAVE_SUCCESS_'),U('linkcate',decode(I('post.linkcate_parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($linkcate->getError());
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
			$linkcate = M('Linkcate');
			$where['siteid'] = array('eq',session('siteid'));
			$where['id'] = array('eq',$id);
			$data = $linkcate->where($where)->find();
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
	public function linkcatedel()
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
			$linkcate = M('Linkcate');
			foreach ($ids as $key => $value) {
				$data = $linkcate->field('id')->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//验证分类下是否有数据
			$link = M('Link');
			$link_where['siteid'] = array('eq',session('siteid'));
			$link_where['catid'] = array('in',$ids);
			if($link->where($link_where)->field('id')->find()){
				$this->error(L('_EXISIST_CATE_ERROR_'));
			}

			//删除数据
			if($linkcate->delete($id)){
				$this->success(L('_DEL_SUCCESS_'));
			} else {
				$this->error(L('_DEL_ERROR_'));
			}
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [sort 分类排序]
	 * @return [type] [description]
	 */
	public function linkcatesort()
	{
		if(IS_POST){
			$sort = I('post.sort');
			$sortarr = explode(",", $sort);

			//验证数据
			if(empty($sortarr)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			$linkcate = M('Linkcate');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$linkcate->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

}
?>
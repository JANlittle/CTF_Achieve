<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 大图轮播 ]
*/
namespace Kgmanage\Controller;

class PhotoController extends CommonController
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
		if(!$this->cate = M('Photocate')->where($cat_where)->find()){
			$this->error(L('_ACCESS_ERROR_'));
		}
		
		//获取图片信息		
		$photo = D('Photo');
		$pid = 0;
		$where['pid'] = array('eq',$pid);
		$where['catid'] = array('eq',$this->catid);
		$where['siteid'] = array('eq',session('siteid'));

		$count = $photo->field('id')->where($where)->count();

		//获取分页
		$page = getPage($count);
		$this->pagelist = $page->show();
		$data = $photo->where($where)->order('sort ASC,id DESC')->relation('photocate')->limit($page->firstRow,$page->listRows)->select();

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
			$photo = D('Photo');
			if($data = $photo->create()){
				//设置站点id
				$photo->siteid = session('siteid');

				//验证同一站点不能有相同的名称
				$check_where['siteid'] = array("eq",session('siteid'));
				$check_where['name'] = $photo->name;
				if(!!$one = $photo->where($check_where)->find()){
					$this->error(L('_NAME_EXISTS_'));
				}

				//key重新索引解决冲突
				$photo->thumb = array_values($photo->thumb); 
				$photo->thumb = serialize($photo->thumb);

				//执行保存
				$photo->create_time = time(); //创建时间
				if($insertId = $photo->add()){
					//设置排序
					$photo->where('id='.$insertId.'')->setField('sort',$insertId);
					$this->success(L('_ADD_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($photo->getError());
			}
		} else {
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
			$photo = D('Photo');
			if($data = $photo->create()){
				//获取表单的类型
				$photo->id = I('post.id');
				$photo->siteid = I('post.siteid');

				//验证同一站点不能有相同的名称
				$check_where['siteid'] = array("eq",$photo->siteid);
				$check_where['name'] = $photo->name;
				$check_where['id'] = array('neq',$photo->id);
				if(!!$one = $photo->where($check_where)->find()){
					$this->error(L('_NAME_EXISTS_'));
				}

				//key重新索引解决冲突
				$photo->thumb = array_values($photo->thumb); 
				$photo->thumb = serialize($photo->thumb);

				//设置修改时间
				$photo->update_time = time();
				if($photo->save()){

					//跳转
					$this->success(L('_SAVE_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($photo->getError());
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
			$photo = M('Photo');
			$where_photo['siteid'] = array('eq',session('siteid'));
			$where_photo['id'] = array('eq',$id);
			$data = $photo->where($where_photo)->find();
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
			$photo = M('Photo');
			foreach ($ids as $key => $value) {
				$data = $photo->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}

				//设置附件与图像
				$pictures_files_arr[] = unserialize($data['thumb']);
			}

			//删除数据
			if($photo->delete($id)){
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

			$photo = M('Photo');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$photo->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [cate 分类]
	 */
	public function photocate()
	{
		$pid = I('get.pid',0);
		$photocate = D('Photocate');
		$where['pid'] = array('eq',$pid);
		$where['siteid'] = array('eq',session('siteid'));

		$count = $photocate->field('id')->where($where)->count();
		//获取分页
		$page = getPage($count);
		$this->pagelist = $page->show();
		$this->data = $photocate->where($where)->relation(array('photo'))->order('sort ASC,id DESC')->limit($page->firstRow,$page->listRows)->select();

		//获取参数
		$this->parameter = I('get.parameter');

		//设置分页
		$this->linkcate_parameter =  getParameter(I('get.'),$page);

		$this->display();
	}


	/**
	 * [add 分类新增]
	 */
	public function photocateadd()
	{
		//数据提交
		if(IS_POST){
			$photocate = D('Photocate');
			if($data = $photocate->create()){
				//设置站点id
				$photocate->siteid = session('siteid');

				//验证同一站点不能有相同的名称
				$check_where['siteid'] = array("eq",session('siteid'));
				$check_where['name'] = $photocate->name;
				if(!!$one = $photocate->where($check_where)->find()){
					$this->error(L('_NAME_EXISTS_'));
				}

				$photocate->create_time = time();
				if($insertid = $photocate->add()){
					//新增排序
					$photocate->where("id = {$insertid}")->setField('sort',$insertid);
					$this->success(L('_ADD_SUCCESS_'),U('photocate',decode(I('post.linkcate_parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($photocate->getError());
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
	public function photocateedit()
	{
		//提交数据处理
		if(IS_POST){
			$photocate = D('Photocate');
			if($data = $photocate->create()){
				//设置站点id
				$photocate->siteid = I('post.siteid');

				//验证同一站点不能有相同的名称
				$check_where['siteid'] = array("eq",$photocate->siteid);
				$check_where['name'] = $photocate->name;
				$check_where['id'] = array('neq',$photocate->id);
				if(!!$one = $photocate->where($check_where)->find()){
					$this->error(L('_NAME_EXISTS_'));
				}

				$photocate->update_time = time();
				//保存数据
				if($photocate->save()){
					$this->success(L('_SAVE_SUCCESS_'),U('photocate',decode(I('post.linkcate_parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($photocate->getError());
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
			$photocate = M('photocate');
			$where['siteid'] = array('eq',session('siteid'));
			$where['id'] = array('eq',$id);
			$data = $photocate->where($where)->find();
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
	public function photocatedel()
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
			$photocate = M('Photocate');
			foreach ($ids as $key => $value) {
				$data = $photocate->field('id')->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//验证分类下是否有数据
			$photo = M('Photo');
			$photo_where['siteid'] = array('eq',session('siteid'));
			$photo_where['catid'] = array('in',$ids);
			if($photo->where($photo_where)->field('id')->find()){
				$this->error(L('_EXISIST_CATE_ERROR_'));
			}

			//删除数据
			if($photocate->delete($id)){
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
	public function photocatesort()
	{
		if(IS_POST){
			$sort = I('post.sort');
			$sortarr = explode(",", $sort);

			//验证数据
			if(empty($sortarr)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			$photocate = M('Photocate');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$photocate->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

}
?>
<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 关联链接 ]
*/
namespace Kgmanage\Controller;

class RelatedLinksController extends CommonController
{
	/**
	 * [index 规则列表]
	 */
	public function index()
	{
		$RelatedLinks = M('RelatedLinks');
		$where['siteid'] = array('eq',session('siteid'));
		$count = $RelatedLinks->field('id')->where($where)->count();

		//获取分页
		$page = getPage($count);
		$this->pagelist = $page->show();
		$this->data = $RelatedLinks->where($where)->order('sort ASC,id DESC')->limit($page->firstRow,$page->listRows)->select();
		
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
			$RelatedLinks = D('RelatedLinks');
			if($data = $RelatedLinks->create()){
				//设置站点id
				$RelatedLinks->siteid = session('siteid');

				//验证同一站点不能有相同的名称
				$check_where['siteid'] = array("eq",session('siteid'));
				$check_where['title'] = $RelatedLinks->title;
				if(!!$one = $RelatedLinks->where($check_where)->find()){
					$this->error(L('_NAME_EXISTS_'));
				}

				//执行保存
				$RelatedLinks->create_time = time(); //创建时间
				if($insertId = $RelatedLinks->add()){
					//设置排序
					$RelatedLinks->where('id='.$insertId.'')->setField('sort',$insertId);
					$this->success(L('_ADD_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($RelatedLinks->getError());
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
			$RelatedLinks = D('RelatedLinks');
			if($data = $RelatedLinks->create()){
				//设置站点id
				$RelatedLinks->siteid = I('post.siteid');

				//验证同一站点不能有相同的名称
				$check_where['siteid'] = array("eq",$RelatedLinks->siteid);
				$check_where['title'] = $RelatedLinks->title;
				$check_where['id'] = array('neq',$RelatedLinks->id);
				if(!!$one = $RelatedLinks->where($check_where)->find()){
					$this->error(L('_NAME_EXISTS_'));
				}

				//设置修改时间
				$RelatedLinks->update_time = time();
				if($RelatedLinks->save()){
					//跳转
					$this->success(L('_SAVE_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($RelatedLinks->getError());
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
			$RelatedLinks = M('RelatedLinks');
			$RelatedLinks_where['siteid'] = array('eq',session('siteid'));
			$RelatedLinks_where['id'] = array('eq',$id);
			$data = $RelatedLinks->where($RelatedLinks_where)->find();
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
			$RelatedLinks = M('RelatedLinks');
			foreach ($ids as $key => $value) {
				$data = $RelatedLinks->field('id')->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//删除数据
			if($RelatedLinks->delete($id)){
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

			$RelatedLinks = M('RelatedLinks');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$RelatedLinks->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}
}
?>
<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 投票管理 ]
*/
namespace Kgmanage\Controller;

class VoteController extends CommonController
{
	/**
	 * [index 列表]
	 */
	public function index()
	{
		$Vote = D('Vote');
		$where['siteid'] = array('eq',session('siteid'));
		$count = $Vote->field('id')->where($where)->count();
		$page = getPage($count);
		$this->pagelist = $page->show();
		$data = $Vote->where($where)->relation(array('Voteinfo','Voteoptions'))->limit($page->firstRow,$page->listRows)->order('sort ASC,id DESC')->select();

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
			$Vote = D('Vote');
			if($data = $Vote->create()){

				//设置站点
				$Vote->siteid = session('siteid');

				//创建时间
				$Vote->create_time = time();

				//设置高度宽度
				$Vote->width = (empty($Vote->width) && $Vote->width !== 0) ? 200 : $Vote->width;
				$Vote->height = (empty($Vote->height) && $Vote->height !== 0 ) ? 150 : $Vote->height;

				//key重新索引解决冲突
				$Vote->thumb = array_values($Vote->thumb); 
				$Vote->thumb = serialize($Vote->thumb);

				if($insertid = $Vote->add()){
					//设置排序与唯一标识符
                    $update_data = array('sort'=>$insertid,'uniqid'=>aikehou_uniqid($insertid));
                    $Vote->where("id = ".$insertid."")->setField($update_data);

					$this->success(L('_ADD_SUCCESS_'));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($Vote->getError());
			}
		} else {

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
			$Vote = D('Vote');
			if($data = $Vote->create()){

				//key重新索引解决冲突
				$Vote->thumb = array_values($Vote->thumb); 
				$Vote->thumb = serialize($Vote->thumb);

				//设置高度宽度
				$Vote->width = (empty($Vote->width) && $Vote->width !== 0) ? 200 : $Vote->width;
				$Vote->height = (empty($Vote->height) && $Vote->height !== 0 ) ? 150 : $Vote->height;

				//更新时间
				$Vote->update_time = time();

				//保存数据
				if($Vote->save()){
					$this->success(L('_SAVE_SUCCESS_'));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($Vote->getError());
			}
		} else {
			//验证数据
			$uniqid = I('get.uniqid');
			if(!is_string($uniqid)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//获取参数
			$this->parameter = I('get.parameter');

			//获取数据
			$Vote = M('Vote');
			$where['siteid'] = array('eq',session('siteid'));
			$where['uniqid'] = array('eq',$uniqid);
			$data = $Vote->where($where)->find();
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
			$Vote = D('Vote');
			$delids = array(); //设置主键删除
			foreach ($ids as $key => $value) {
				$where['uniqid'] = array('eq',$value);
				$where['siteid'] = array('eq',session('siteid'));
				$data = $Vote->where($where)->find();
				$delids[] = $data['id'];
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}

				//设置附件与图像
				$pictures_files_arr[] = unserialize($data['thumb']);
			}


			//删除数据
			$new_where['id'] = array('in',$delids);
			$new_where['siteid'] = array('eq',session('siteid'));

			//获取数据
			$vote_data = $Vote->where($new_where)->relation(array('Voteoptions'))->select();
			if(!empty($vote_data)){
				foreach ($vote_data as $key => $value) {
					if(!empty($value['Voteoptions'])){
						foreach ($value['Voteoptions'] as $key_1 => $value_1) {
							$pictures_files_arr_1[] = unserialize($value_1['thumb']);;
						}
					}
				}
			}

			$delids = implode(",", $delids);
			//删除数据
			if($Vote->relation(array('Voteoptions','Voteinfo'))->delete($delids)){
				//删除图片
				delfilefun($pictures_files_arr);

				//删除投票选项图片
				delfilefun($pictures_files_arr_1);

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

			$Vote = M('Vote');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$Vote->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [options 选项]
	 * @return [type] [description]
	 */
	public function options()
	{
		$this->vid = I('get.id');
		$this->type = I('get.type');
		if(!is_numeric($this->vid) || empty($this->vid)){
			$this->error(L('_ACCESS_ERROR_'));
		}

		//获取投票信息
		$vote_where['id'] = array('eq',$this->vid);
		$vote_where['siteid'] = array('eq',session('siteid'));
		if(!$this->vote = M('Vote')->where($vote_where)->find()){
			$this->error(L('_ACCESS_ERROR_'));
		}

		$Voteoptions = D('Voteoptions');
		$where['vid'] = array('eq',$this->vid);
		$where['siteid'] = array('eq',session('siteid'));
		$count = $Voteoptions->field('id')->where($where)->count();
		$page = getPage($count);
		$this->pagelist = $page->show();
		$data = $Voteoptions->where($where)->relation(array('Vote','Voteinfo'))->limit($page->firstRow,$page->listRows)->order('sort ASC,id DESC')->select();

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
		$this->parameter = I('get.parameter');

		//设置分页
		$this->linkcate_parameter =  getParameter(I('get.'),$page);

		$this->display();
	}

	/**
	 * [add 新增]
	 */
	public function optionsadd()
	{
		//数据提交
		if(IS_POST){
			$Voteoptions = D('Voteoptions');
			if($data = $Voteoptions->create()){

				//设置站点
				$Voteoptions->siteid = session('siteid');

				//创建时间
				$Voteoptions->create_time = time();
				
				$type = I('post.type');
				if($type == 1){
					//key重新索引解决冲突
					$Voteoptions->thumb = array_values($Voteoptions->thumb); 
					$Voteoptions->thumb = serialize($Voteoptions->thumb);
				} else {
					$Voteoptions->thumb = "";
				}

				if($insertid = $Voteoptions->add()){
					//设置排序与唯一标识符
                    $update_data = array('sort'=>$insertid,'uniqid'=>aikehou_uniqid($insertid));
                    $Voteoptions->where("id = ".$insertid."")->setField($update_data);

					$this->success(L('_ADD_SUCCESS_'));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($Voteoptions->getError());
			}
		} else {
			$this->vid = I('get.vid');
			if(!is_numeric($this->vid) || empty($this->vid)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//获取参数
			$this->linkcate_parameter = I('get.linkcate_parameter');

			//获取投票信息
			$Vote = M('Vote');
			$where['siteid'] = array('eq',session('siteid'));
			$where['id'] = array('eq',$this->vid);
			if(!$this->data = $Vote->where($where)->find()){
				$this->error(L('_NODATA_'));
			}

			//获取菜单
			$this->display();
		}
	}

	/**
	 * [edit 编辑]
	*/
	public function optionsedit()
	{
		//提交数据处理
		if(IS_POST){
			$Voteoptions = D('Voteoptions');
			if($data = $Voteoptions->create()){
				
				$type = I('post.type');
				if($type == 1){
					$original_data = M('Voteoptions')->find(I('post.id'));

					//key重新索引解决冲突
					$Voteoptions->thumb = array_values($Voteoptions->thumb); 
					$Voteoptions->thumb = serialize($Voteoptions->thumb);
				} else {
					$Voteoptions->thumb = "";
				}

				//更新时间
				$Voteoptions->update_time = time();

				//保存数据
				if($Voteoptions->save()){
					if($type == 1){
						//删除图片或附件数据
						$pictures_files_arr[] = unserialize($original_data['thumb']);
						delfilefun($pictures_files_arr);
					}
					$this->success(L('_SAVE_SUCCESS_'));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($Voteoptions->getError());
			}
		} else {
			//验证数据
			$id = I('get.id');
			$vid = I('get.vid');
			if(!is_numeric($id) || !is_numeric($vid)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//获取参数
			$this->linkcate_parameter = I('get.linkcate_parameter');
			
			//获取数据
			$Voteoptions = D('Voteoptions');
			$where['id'] = array('eq',$id);
			$where['vid'] = array('eq',$vid);
			$where['siteid'] = array('eq',session('siteid'));
			$data = $Voteoptions->where($where)->relation(array('Vote'))->find($id);
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
	public function optionsdel()
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
			$Voteoptions = D('Voteoptions');
			$delids = array();
			foreach ($ids as $key => $value) {
				$where['uniqid'] = array('eq',$value);
				$where['siteid'] = array('eq',session('siteid'));
				$data = $Voteoptions->where($where)->find();
				$delids[] = $data['id'];
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
				//设置附件与图像
				$pictures_files_arr[] = unserialize($data['thumb']);
			}

			$delids = implode(",", $delids);
			//删除数据
			if($Voteoptions->relation(array('Voteinfo'))->delete($delids)){
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
	public function optionssort()
	{
		if(IS_POST){
			$sort = I('post.sort');
			$sortarr = explode(",", $sort);

			//验证数据
			if(empty($sortarr)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			$Voteoptions = M('Voteoptions');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$Voteoptions->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

}
?>
<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 投票 ]
*/
namespace Kgmanage\Controller;

class WechatVoteController extends CommonController
{
	/**
	 * [index 列表]
	 */
	public function index()
	{
		$this->type = 1;
		$WechatVote = D('WechatVote');
		$where['type'] = array('eq',$this->type);
		$count = $WechatVote->field('id')->where($where)->count();
		$page = getPage($count);
		$this->pagelist = $page->show();
		$data = $WechatVote->where($where)->relation(array('WechatVoteinfo','WechatVoteoptions'))->limit($page->firstRow,$page->listRows)->order('sort ASC,id DESC')->select();

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
			$WechatVote = D('WechatVote');
			if($data = $WechatVote->create()){

				//设置高度宽度
				$WechatVote->width = (empty($WechatVote->width) && $WechatVote->width !== 0) ? 360 : $WechatVote->width;
				$WechatVote->height = (empty($WechatVote->height) && $WechatVote->height !== 0 ) ? 200 : $WechatVote->height;

				//key重新索引解决冲突
				$WechatVote->thumb = array_values($WechatVote->thumb); 
				$WechatVote->thumb = serialize($WechatVote->thumb);

				$WechatVote->create_time = time();
				//设置类型
				$WechatVote->type = 1;

				if($insertid = $WechatVote->add()){
					//新增排序
					$WechatVote->where("id = {$insertid}")->setField('sort',$insertid);
					$this->success(L('_ADD_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($WechatVote->getError());
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
			$WechatVote = D('WechatVote');
			if($data = $WechatVote->create()){
				//设置高度宽度
				$WechatVote->width = (empty($WechatVote->width) && $WechatVote->width !== 0) ? 360 : $WechatVote->width;
				$WechatVote->height = (empty($WechatVote->height) && $WechatVote->height !== 0 ) ? 200 : $WechatVote->height;

				//key重新索引解决冲突
				$WechatVote->thumb = array_values($WechatVote->thumb); 
				$WechatVote->thumb = serialize($WechatVote->thumb);

				$WechatVote->update_time = time();
				//保存数据
				if($WechatVote->save()){
					$this->success(L('_SAVE_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($WechatVote->getError());
			}
		} else {
			//验证数据
			$id = I('get.id');
			if(!is_numeric($id)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//获取数据
			$WechatVote = M('WechatVote');
			$data = $WechatVote->find($id);
			if(!$data){
				$this->error(L('_NODATA_'));
			}

			//处理图片解析
			if(!empty($data['thumb'])){
				$data['thumb'] = unserialize($data['thumb']);
			}

			$this->data = $data;

			//获取参数
			$this->parameter = I('get.parameter');

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
			$WechatVote = D('WechatVote');
			foreach ($ids as $key => $value) {
				$data = $WechatVote->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}

				//设置附件与图像
				$pictures_files_arr[] = unserialize($data['thumb']);
			}

			//删除数据
			$new_where['id'] = array('in',$id);
			//获取数据
			$vote_data = $WechatVote->where($new_where)->relation(array('WechatVoteoptions'))->select();
			if(!empty($vote_data)){
				foreach ($vote_data as $key => $value) {
					if(!empty($value['WechatVoteoptions'])){
						foreach ($value['WechatVoteoptions'] as $key_1 => $value_1) {
							$pictures_files_arr_1[] = unserialize($value_1['thumb']);;
						}
					}
				}
			}

			//删除数据
			if($WechatVote->relation(array('WechatVoteoptions','WechatVoteinfo'))->delete($id)){
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

			$WechatVote = M('WechatVote');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$WechatVote->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [index 列表]
	 */
	public function wordindex()
	{
		$this->type = 2;
		$WechatVote = D('WechatVote');
		$where['type'] = array('eq',$this->type);
		$count = $WechatVote->field('id')->where($where)->count();
		$page = getPage($count);
		$this->pagelist = $page->show();
		$this->data = $WechatVote->where($where)->relation(array('WechatVoteinfo','WechatVoteoptions'))->order('sort ASC,id DESC')->limit($page->firstRow,$page->listRows)->select();
	
	/*获取url参数*/
		$this->parameter = getParameter(I('get.'),$page);
	/* end 获取url参数*/

	/*获取url参数*/
		$this->index_parameter = I('get.parameter');
	/* end 获取url参数*/

		$this->display();
	}

	/**
	 * [add 新增]
	 */
	public function wordadd()
	{
		//数据提交
		if(IS_POST){
			$WechatVote = D('WechatVote');
			if($data = $WechatVote->create()){
				//设置内容
				$WechatVote->content = I('post.content','',false);
				$WechatVote->create_time = time();
				//设置类型
				$WechatVote->type = 2;
				if($insertid = $WechatVote->add()){
					//新增排序
					$WechatVote->where("id = {$insertid}")->setField('sort',$insertid);
					$this->success(L('_ADD_SUCCESS_'),U('wordindex',decode(I('post.parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($WechatVote->getError());
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
	public function wordedit()
	{
		//提交数据处理
		if(IS_POST){
			$WechatVote = D('WechatVote');
			if($data = $WechatVote->create()){
				//设置内容
				$WechatVote->content = I('post.content','',false);
				$WechatVote->update_time = time();
				//保存数据
				if($WechatVote->save()){
					$this->success(L('_SAVE_SUCCESS_'),U('wordindex',decode(I('post.parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($WechatVote->getError());
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
			$WechatVote = M('WechatVote');
			$data = $WechatVote->find($id);
			if(!$data){
				$this->error(L('_NODATA_'));
			}
			$this->data = $data;

			$this->display();
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
		if(!is_numeric($this->vid) || empty($this->vid) || !is_numeric($this->type) || empty($this->type)){
			$this->error(L('_ACCESS_ERROR_'));
		}

		//获取投票信息
		$vote_where['id'] = array('eq',$this->vid);
		if(!$this->vote = M('WechatVote')->where($vote_where)->find()){
			$this->error(L('_ACCESS_ERROR_'));
		}

		$WechatVoteoptions = D('WechatVoteoptions');
		$where['type'] = array('eq',$this->type);
		$where['vid'] = array('eq',$this->vid);
		$count = $WechatVoteoptions->field('id')->where($where)->count();
		$page = getPage($count);
		$this->pagelist = $page->show();
		$data = $WechatVoteoptions->where($where)->relation(array('WechatVoteinfo'))->limit($page->firstRow,$page->listRows)->order('sort ASC,id DESC')->select();

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
		$this->parameter = I('get.parameter');
	/* end 获取url参数*/

	/*获取url参数*/
		$this->linkcate_parameter = getParameter(I('get.'),$page);
	/* end 获取url参数*/

		$this->display();
	}

	/**
	 * [add 新增]
	 */
	public function optionsadd()
	{
		//数据提交
		if(IS_POST){
			$WechatVoteoptions = D('WechatVoteoptions');
			if($data = $WechatVoteoptions->create()){
				//key重新索引解决冲突
				$WechatVoteoptions->thumb = array_values($WechatVoteoptions->thumb); 
				$WechatVoteoptions->thumb = serialize($WechatVoteoptions->thumb);

				$WechatVoteoptions->create_time = time();

				if($insertid = $WechatVoteoptions->add()){
					//新增排序
					$WechatVoteoptions->where("id = {$insertid}")->setField('sort',$insertid);
					$this->success(L('_ADD_SUCCESS_'),U('options',decode(I('post.linkcate_parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($WechatVoteoptions->getError());
			}
		} else {
			$this->vid = I('get.vid');
			$this->type = I('get.type');
			if(!is_numeric($this->vid) || empty($this->vid) || !is_numeric($this->type) || empty($this->type)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//获取参数
			$this->linkcate_parameter = I('get.linkcate_parameter');

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
			$WechatVoteoptions = D('WechatVoteoptions');
			if($data = $WechatVoteoptions->create()){

				//key重新索引解决冲突
				$WechatVoteoptions->thumb = array_values($WechatVoteoptions->thumb); 
				$WechatVoteoptions->thumb = serialize($WechatVoteoptions->thumb);

				$WechatVoteoptions->update_time = time();
				//保存数据
				if($WechatVoteoptions->save()){
					$this->success(L('_SAVE_SUCCESS_'),U('options',decode(I('post.linkcate_parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($WechatVoteoptions->getError());
			}
		} else {
			//验证数据
			$id = I('get.id');
			if(!is_numeric($id)){
				$this->error(L('_ACCESS_ERROR_'));
			}
			//获取数据
			$WechatVoteoptions = M('WechatVoteoptions');
			$data = $WechatVoteoptions->find($id);
			if(!$data){
				$this->error(L('_NODATA_'));
			}

			//处理图片解析
			if(!empty($data['thumb'])){
				$data['thumb'] = unserialize($data['thumb']);
			}

			$this->data = $data;

			//获取参数
			$this->linkcate_parameter = I('get.linkcate_parameter');

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
			$WechatVoteoptions = D('WechatVoteoptions');
			foreach ($ids as $key => $value) {
				$data = $WechatVoteoptions->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
				//设置附件与图像
				$pictures_files_arr[] = unserialize($data['thumb']);
			}

			//删除数据
			if($WechatVoteoptions->relation(array('WechatVoteinfo'))->delete($id)){
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

			$WechatVoteoptions = M('WechatVoteoptions');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$WechatVoteoptions->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [votes 投票详情]
	 * @return [type] [description]
	 */
	public function votes()
	{
		$this->vid = I('get.id');
		$this->type = I('get.type');
		if(!is_numeric($this->vid) || empty($this->vid) || !is_numeric($this->type) || empty($this->type)){
			$this->error(L('_ACCESS_ERROR_'));
		}

		//获取投票信息
		$vote_where['id'] = array('eq',$this->vid);
		if(!$this->vote = M('WechatVote')->where($vote_where)->find()){
			$this->error(L('_ACCESS_ERROR_'));
		}

		$WechatVoteinfo = D('WechatVoteinfo');
		$where['type'] = array('eq',$this->type);
		$where['vid'] = array('eq',$this->vid);
		$count = $WechatVoteinfo->field('id')->where($where)->count();
		$page = getPage($count);
		$this->pagelist = $page->show();
		$this->data = $WechatVoteinfo->where($where)->limit($page->firstRow,$page->listRows)->order('id DESC')->select();

	/*获取url参数*/
		$this->parameter = I('get.parameter');
	/* end 获取url参数*/

		$this->display();
	}


	/**
	 * [del 删除]
	 */
	public function votesdel()
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
			$WechatVoteinfo = D('WechatVoteinfo');
			foreach ($ids as $key => $value) {
				$data = $WechatVoteinfo->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//删除数据
			if($WechatVoteinfo->delete($id)){
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
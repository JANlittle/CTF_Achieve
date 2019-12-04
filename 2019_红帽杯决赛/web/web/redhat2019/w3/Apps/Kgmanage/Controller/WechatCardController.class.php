<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 会员卡 ]
*/
namespace Kgmanage\Controller;

class WechatCardController extends CommonController
{
	/**
	 * [index 列表]
	 */
	public function index()
	{
		if(IS_POST){
			$WechatCard = D('WechatCard');
			if($data = $WechatCard->create()){

				$background = I('post.background');

				//key重新索引解决冲突
				$WechatCard->thumb = array_values($WechatCard->thumb); 
				$WechatCard->thumb = serialize($WechatCard->thumb);
				
				//设置内容
				$WechatCard->content = I('post.content','',false);
				if($WechatCard->save()){
					
					$this->success(L('_SAVE_SUCCESS_'));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($WechatCard->getError());
			}
		} else {
			$data = M('WechatCard')->find();

			//处理图片解析
			if(!empty($data)){
				if(!empty($data['thumb'])){
					$data['thumb'] = unserialize($data['thumb']);
				}
			}

		/*获取url参数*/
			$this->parameter = getParameter(I('get.'),$page);
		/* end 获取url参数*/

			$this->data = $data;
			$this->display();
		}
	}


	/**
	 * [member 会员管理]
	 * @return [type] [description]
	 */
	public function member()
	{
		$WechatCardmember = M('WechatCardmember');
		$count = $WechatCardmember->field('id')->count();
		$page = getPage($count);
		$this->pagelist = $page->show();
		$this->data = $WechatCardmember->limit($page->firstRow,$page->listRows)->order('create_time DESC,sort ASC')->select();
		
		/*获取url参数*/
			$this->parameter = getParameter(I('get.'),$page);
		/* end 获取url参数*/

		/*获取url参数*/
			$this->index_parameter = I('get.parameter');
		/* end 获取url参数*/

		$this->display();
	}

	/**
	 * [memberadd 新增]
	 */
	public function memberadd()
	{
		//数据提交
		if(IS_POST){
			$WechatCardmember = D('WechatCardmember');

			if($data = $WechatCardmember->create()){
				$WechatCardmember->create_time = time();

				//设置会员卡号
				$card = M('WechatCard');
                $card = $card->field('qianzhui,length')->find();
                $min = "8".str_repeat("0", $card['length'] - 1);
                $max = "8".str_repeat("9", $card['length'] - 1);
                $WechatCardmember->number = $card['qianzhui'].mt_rand($min,$max);

				if($insertid = $WechatCardmember->add()){
					//新增排序
					$WechatCardmember->where("id = {$insertid}")->setField('sort',$insertid);
					$this->success(L('_ADD_SUCCESS_'),U('member',decode(I('post.parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($WechatCardmember->getError());
			}
		} else {
			//获取参数
			$this->parameter = I('get.parameter');
			$this->display();
		}
	}

	/**
	 * [memberedit 编辑]
	*/
	public function memberedit()
	{
		//提交数据处理
		if(IS_POST){
			$WechatCardmember = D('WechatCardmember');
			if($data = $WechatCardmember->create()){
				//保存数据
				if($WechatCardmember->save()){
					$this->success(L('_SAVE_SUCCESS_'),U('member',decode(I('post.parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($WechatCardmember->getError());
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
			$WechatCardmember = M('WechatCardmember');
			$data = $WechatCardmember->find($id);
			if(!$data){
				$this->error(L('_NODATA_'));
			}
			$this->data = $data;

			$this->display();
		}
	}

	/**
	 * [memberdel 删除]
	 */
	public function memberdel()
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
			$WechatCardmember = M('WechatCardmember');
			foreach ($ids as $key => $value) {
				$data = $WechatCardmember->field('id')->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//删除数据
			if($WechatCardmember->delete($id)){
				$this->success(L('_DEL_SUCCESS_'));
			} else {
				$this->error(L('_DEL_ERROR_'));
			}
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [membersort 排序]
	 * @return [type] [description]
	 */
	public function membersort()
	{
		if(IS_POST){
			$sort = I('post.sort');
			$sortarr = explode(",", $sort);

			//验证数据
			if(empty($sortarr)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			$WechatCardmember = M('WechatCardmember');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$WechatCardmember->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [notice 通知管理]
	 * @return [type] [description]
	 */
	public function notice()
	{
		$WechatCardnotice = M('WechatCardnotice');
		$count = $WechatCardnotice->field('id')->count();
		$page = getPage($count);
		$this->pagelist = $page->show();
		$this->data = $WechatCardnotice->limit($page->firstRow,$page->listRows)->order('create_time DESC,sort ASC')->select();
		
		/*获取url参数*/
			$this->parameter = getParameter(I('get.'),$page);
		/* end 获取url参数*/

		/*获取url参数*/
			$this->index_parameter = I('get.parameter');
		/* end 获取url参数*/

		$this->display();
	}

	/**
	 * [noticeadd 新增]
	 */
	public function noticeadd()
	{
		//数据提交
		if(IS_POST){
			$WechatCardnotice = D('WechatCardnotice');

			if($data = $WechatCardnotice->create()){
				$WechatCardnotice->create_time = time();

				if($insertid = $WechatCardnotice->add()){
					//新增排序
					$WechatCardnotice->where("id = {$insertid}")->setField('sort',$insertid);
					$this->success(L('_ADD_SUCCESS_'),U('notice',decode(I('post.parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($WechatCardnotice->getError());
			}
		} else {
			//获取参数
			$this->parameter = I('get.parameter');
			$this->display();
		}
	}

	/**
	 * [noticeedit 编辑]
	*/
	public function noticeedit()
	{
		//提交数据处理
		if(IS_POST){
			$WechatCardnotice = D('WechatCardnotice');
			if($data = $WechatCardnotice->create()){
				//保存数据
				if($WechatCardnotice->save()){
					$this->success(L('_SAVE_SUCCESS_'),U('notice',decode(I('post.parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($WechatCardnotice->getError());
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
			$WechatCardnotice = M('WechatCardnotice');
			$data = $WechatCardnotice->find($id);
			if(!$data){
				$this->error(L('_NODATA_'));
			}
			$this->data = $data;

			$this->display();
		}
	}

	/**
	 * [noticedel 删除]
	 */
	public function noticedel()
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
			$WechatCardnotice = M('WechatCardnotice');
			foreach ($ids as $key => $value) {
				$data = $WechatCardnotice->field('id')->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//删除数据
			if($WechatCardnotice->delete($id)){
				$this->success(L('_DEL_SUCCESS_'));
			} else {
				$this->error(L('_DEL_ERROR_'));
			}
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [noticesort 排序]
	 * @return [type] [description]
	 */
	public function noticesort()
	{
		if(IS_POST){
			$sort = I('post.sort');
			$sortarr = explode(",", $sort);

			//验证数据
			if(empty($sortarr)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			$WechatCardnotice = M('WechatCardnotice');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$WechatCardnotice->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}
}
?>
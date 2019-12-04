<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 建议意见 ]
*/
namespace Kgmanage\Controller;

class WechatMessageController extends CommonController
{
	/**
	 * [index 列表]
	 */
	public function index()
	{
		$WechatMessage = D('WechatMessage');
		$count = $WechatMessage->field('id')->count();
		$page = getPage($count);
		$this->pagelist = $page->show();
		$data = $WechatMessage->limit($page->firstRow,$page->listRows)->order('create_time DESC,sort ASC')->select();
		
		//处理数据
		if(!empty($data)){
			foreach ($data as $key => $value) {
				if(!empty($value['area'])){
					$data[$key]['area'] = unserialize($value['area']);
				}
			}
		}
		$this->data = $data;
		$this->display();
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
			$WechatMessage = M('WechatMessage');
			foreach ($ids as $key => $value) {
				$data = $WechatMessage->field('id')->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//删除数据
			if($WechatMessage->delete($id)){
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

			$WechatMessage = M('WechatMessage');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$WechatMessage->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}
}
?>
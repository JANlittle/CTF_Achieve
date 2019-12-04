<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 建议留言 ]
*/
namespace Kgmanage\Controller;

class GuestbookController extends CommonController
{
	/**
	 * [index 留言列表]
	 */
	public function index()
	{
		$Guestbook = D('Guestbook');
		if(session('siteid') != 0){
			$where['siteid'] = array('eq',session('siteid'));
		} else {
			$where = array();
		}
		$count = $Guestbook->where($where)->count();

		//获取分页
		$page = getPage($count);
		$this->pagelist = $page->show();
		$data = $Guestbook->where($where)->order('create_time DESC')->relation('ucmembers')->limit($page->firstRow,$page->listRows)->select();
		if(!empty($data)){
			foreach ($data as $key => $value) {
				$data[$key]['area'] = unserialize($value['area']); 
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
			$Guestbook = M('Guestbook');
			foreach ($ids as $key => $value) {
				$data = $Guestbook->field('id')->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//删除数据
			if($Guestbook->delete($id)){
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

			$Guestbook = M('Guestbook');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$Guestbook->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [export 导出数据]
	 * @return [type] [description]
	 */
	public function export()
	{
		$Guestbook = D('Guestbook');
		if(session('siteid') != 0){
			$where['siteid'] = array('eq',session('siteid'));
		} else {
			$where = array();
		}
		$data = $Guestbook->where($where)->field('id,uid,title,content,ip,area,source,create_time')->relation('ucmembers')->select();
		if(empty($data)){
			$this->error(L('_NODATA_'));
		}
		foreach ($data as $key => $value) {
			$data[$key]['create_time'] = date("Y-m-d H:i:s",$value['create_time']);

			//设置地区
			$area = unserialize($value['area']);
			$data[$key]['area'] = $area['area'] . " " . $area['country'];

			//设置用户
			if(!empty($value['ucmembers'])){
				$data[$key]['uid'] = $value['ucmembers']['username'];
			} else {
				$data[$key]['uid'] = '游客';
			}

			//删除ucmembers
			unset($data[$key]['ucmembers']);
		}

		//导出数据
		exportData('Guestbook',array('ID','用户','主题','内容','ip','地区','来源','时间'),$data);
	}

}
?>
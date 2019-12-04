<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 在线报名 ]
*/
namespace Kgmanage\Controller;

class BaomingController extends CommonController
{
	/**
	 * [index 报名列表]
	 */
	public function index()
	{
		$Baoming = M('Baoming');
		if(session('siteid') != 0){
			$where['siteid'] = array('eq',session('siteid'));
		} else {
			$where = array();
		}
		$count = $Baoming->where($where)->count();

		//获取分页
		$page = getPage($count);
		$this->pagelist = $page->show();
		$data = $Baoming->where($where)->order('create_time DESC')->limit($page->firstRow,$page->listRows)->select();
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
			$Baoming = M('Baoming');
			foreach ($ids as $key => $value) {
				$data = $Baoming->field('id')->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//删除数据
			if($Baoming->delete($id)){
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

			$Baoming = M('Baoming');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$Baoming->save($data);
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
		$Baoming = M('Baoming');
		if(session('siteid') != 0){
			$where['siteid'] = array('eq',session('siteid'));
		} else {
			$where = array();
		}
		$data = $Baoming->where($where)->field('id,name,tel,email,qq,ip,area,source,create_time')->select();
		if(empty($data)){
			$this->error(L('_NODATA_'));
		}
		foreach ($data as $key => $value) {
			$data[$key]['create_time'] = date("Y-m-d H:i:s",$value['create_time']);
			$area = unserialize($value['area']);
			$data[$key]['area'] = $area['area'] . " " . $area['country'];
		}

		//导出数据
		exportData('Baoming',array('ID','姓名','联系电话','邮箱','QQ','ip','地区','来源','时间'),$data);
	}

}
?>
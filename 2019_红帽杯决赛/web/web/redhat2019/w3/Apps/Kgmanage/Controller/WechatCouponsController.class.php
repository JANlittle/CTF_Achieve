<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 优惠券 ]
*/
namespace Kgmanage\Controller;

class WechatCouponsController extends CommonController
{
	/**
	 * [index 列表]
	 */
	public function index()
	{
		$WechatCoupons = D('WechatCoupons');
		$count = $WechatCoupons->field('id')->count();
		$page = getPage($count);
		$this->pagelist = $page->show();
		$this->data = $WechatCoupons->relation(array('WechatCouponsmember'))->limit($page->firstRow,$page->listRows)->order('sort ASC,id DESC')->select();

	/*获取url参数*/
		$this->parameter = getParameter(I('get.'),$page);
	/* end 获取url参数*/
	
		$this->display();
	}


	/**
	 * [index 新增] 
	*/
	public function add()
	{
		if(IS_POST){
			$WechatCoupons = D('WechatCoupons');
			if($data = $WechatCoupons->create()){
				//设置内容
				$WechatCoupons->use = I('post.use','',false);
				$WechatCoupons->create_time = time(); //创建时间
				if($insertId = $WechatCoupons->add()){
					//设置排序
					$WechatCoupons->where('id='.$insertId.'')->setField('sort',$insertId);
					$this->success(L('_ADD_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($WechatCoupons->getError());
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
			$WechatCoupons = D('WechatCoupons');
			if($data = $WechatCoupons->create()){
				//设置内容
				$WechatCoupons->use = I('post.use','',false);
				$WechatCoupons->update_time = time(); //创建时间
				//保存数据
				if($WechatCoupons->save()){
					$this->success(L('_SAVE_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($WechatCoupons->getError());
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
			$WechatCoupons = M('WechatCoupons');
			$data = $WechatCoupons->find($id);
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
			$WechatCoupons = D('WechatCoupons');
			foreach ($ids as $key => $value) {
				$data = $WechatCoupons->field('id')->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//删除数据
			if($WechatCoupons->relation(array('WechatCouponsmember'))->delete($id)){
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

			$WechatCoupons = M('WechatCoupons');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$WechatCoupons->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}


	/**
	 * [member 会员管理]
	 * @return [type] [description]
	 */
	public function member()
	{
		$cid = I('get.id');
		if(!is_numeric($cid) || empty($cid)){
			$this->error(L('_ACCESS_ERROR_'));
		}

		//获取当前优惠券信息
		$WechatCoupons = M('WechatCoupons');
		$this->coupons = $WechatCoupons->find($cid);

		//获取成员
		$WechatCouponsmember = M('WechatCouponsmember');
		$where['cid'] = array('eq',$cid);
		$count = $WechatCouponsmember->where($where)->field('id')->count();
		$page = getPage($count);
		$this->pagelist = $page->show();
		$this->data = $WechatCouponsmember->where($where)->limit($page->firstRow,$page->listRows)->order('create_time DESC,sort ASC')->select();

		//获取参数
		$this->parameter = I('get.parameter');

		//设置分页
		$this->linkcate_parameter =  getParameter(I('get.'),$page);

		$this->display();
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
			$WechatCouponsmember = M('WechatCouponsmember');
			foreach ($ids as $key => $value) {
				$data = $WechatCouponsmember->field('id')->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//删除数据
			if($WechatCouponsmember->delete($id)){
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

			$WechatCouponsmember = M('WechatCouponsmember');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$WechatCouponsmember->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [setstatus 确认使用]
	 * @return [type] [description]
	 */
	public function setstatus()
	{
		if(IS_POST){
			$id = I('post.data');
			$WechatCouponsmember = M('WechatCouponsmember');

			$data['id'] = $id;
			$data['status'] = 1;
			$data['usetime'] = time();
			if($WechatCouponsmember->data($data)->save()){
				$this->success(L('_COUPONS_USE_SUCCESS_'));
			} else {
				$this->error(L('_COUPONS_USE_ERROR_'));
			}
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}
}
?>
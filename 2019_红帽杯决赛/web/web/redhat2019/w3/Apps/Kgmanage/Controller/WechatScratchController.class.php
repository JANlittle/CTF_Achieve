<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 刮刮卡 ]
*/
namespace Kgmanage\Controller;

class WechatScratchController extends CommonController
{
	/**
	 * [index 列表]
	 */
	public function index()
	{
		$WechatScratch = D('WechatScratch');
		$count = $WechatScratch->field('id')->count();
		$page = getPage($count);
		$this->pagelist = $page->show();
		$this->data = $WechatScratch->relation(array('WechatScratchmember','WechatScratchproducts'))->limit($page->firstRow,$page->listRows)->order('sort ASC,id DESC')->select();

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
			$WechatScratch = D('WechatScratch');
			if($data = $WechatScratch->create()){
				//设置内容
				$WechatScratch->use = I('post.use','',false);
				$WechatScratch->create_time = time(); //创建时间
				if($insertId = $WechatScratch->add()){
					//设置排序
					$WechatScratch->where('id='.$insertId.'')->setField('sort',$insertId);
					$this->success(L('_ADD_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($WechatScratch->getError());
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
			$WechatScratch = D('WechatScratch');
			if($data = $WechatScratch->create()){
				//设置内容
				$WechatScratch->use = I('post.use','',false);
				$WechatScratch->update_time = time(); //创建时间
				//保存数据
				if($WechatScratch->save()){
					$this->success(L('_SAVE_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($WechatScratch->getError());
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
			$WechatScratch = M('WechatScratch');
			$data = $WechatScratch->find($id);
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

			$delids = array(); //设置主键删除
			//验证数据
			$WechatScratch = D('WechatScratch');
			foreach ($ids as $key => $value) {
				$data = $WechatScratch->find($value);
				$delids[] = $data['id'];
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//删除数据
			$new_where['id'] = array('in',$delids);

			//获取数据
			$scratch_products_data = $WechatScratch->where($new_where)->relation(array('WechatScratchproducts'))->select();
			if(!empty($scratch_products_data)){
				foreach ($scratch_products_data as $key => $value) {
					if(!empty($value['WechatScratchproducts'])){
						foreach ($value['WechatScratchproducts'] as $key_1 => $value_1) {
							$pictures_files_arr_1[] = unserialize($value_1['thumb']);;
						}
					}
				}
			}

			//删除数据
			if($WechatScratch->relation(array('WechatScratchmember','WechatScratchproducts'))->delete($id)){
				//删除奖品图片
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

			$WechatScratch = M('WechatScratch');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$WechatScratch->save($data);
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

		//获取参数
		$this->parameter = I('get.parameter');

		//设置分页
		$this->linkcate_parameter =  getParameter(I('get.'),$page);

		//获取当前优惠券信息
		$WechatScratch = M('WechatScratch');
		$this->scratch = $WechatScratch->find($cid);

		//获取成员
		$WechatScratchmember = M('WechatScratchmember');
		$where['cid'] = array('eq',$cid);
		$count = $WechatScratchmember->where($where)->field('id')->count();
		$page = getPage($count);
		$this->pagelist = $page->show();
		$this->data = $WechatScratchmember->where($where)->limit($page->firstRow,$page->listRows)->order('create_time DESC,sort ASC')->select();
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
			$WechatScratchmember = M('WechatScratchmember');
			foreach ($ids as $key => $value) {
				$data = $WechatScratchmember->field('id')->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//删除数据
			if($WechatScratchmember->delete($id)){
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

			$WechatScratchmember = M('WechatScratchmember');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$WechatScratchmember->save($data);
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
			$WechatScratchmember = M('WechatScratchmember');

			$data['id'] = $id;
			$data['status'] = 1;
			$data['usetime'] = time();
			if($WechatScratchmember->data($data)->save()){
				$this->success('兑奖成功！');
			} else {
				$this->error('兑奖失败！');
			}
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}


	/**
	 * [index 列表]
	 */
	public function products()
	{
		$this->pid = I('get.id');
		if(!is_numeric($this->pid) || empty($this->pid)){
			$this->error(L('_ACCESS_ERROR_'));
		}

		//获取参数
		$this->parameter = I('get.parameter');

		//获取投票
		if(!$this->scratch = M('WechatScratch')->find($this->pid)){
			$this->error(L('_ACCESS_ERROR_'));
		}

		//设置分页
		$this->linkcate_parameter =  getParameter(I('get.'),$page);

		$where['pid'] = array('eq',$this->pid);
		$WechatScratchproducts = D('WechatScratchproducts');
		$count = $WechatScratchproducts->field('id')->where($where)->count();
		$page = getPage($count);
		$this->pagelist = $page->show();
		$data = $WechatScratchproducts->where($where)->limit($page->firstRow,$page->listRows)->order('sort ASC,id DESC')->select();

		//处理图片解析
		if(!empty($data)){
			foreach ($data as $key => $value) {
				if(!empty($value['thumb'])){
					$data[$key]['thumb'] = unserialize($value['thumb']);
				}
			}
		}
		$this->data = $data;

		$this->display();
	}


	/**
	 * [index 新增] 
	*/
	public function productsadd()
	{
		if(IS_POST){
			$WechatScratchproducts = D('WechatScratchproducts');
			if($data = $WechatScratchproducts->create()){

				//key重新索引解决冲突
				$WechatScratchproducts->thumb = array_values($WechatScratchproducts->thumb); 
				$WechatScratchproducts->thumb = serialize($WechatScratchproducts->thumb);

				//设置内容
				$WechatScratchproducts->create_time = time(); //创建时间
				if($insertId = $WechatScratchproducts->add()){
					//设置排序
					$WechatScratchproducts->where('id='.$insertId.'')->setField('sort',$insertId);
					$this->success(L('_ADD_SUCCESS_'),U('products',decode(I('post.linkcate_parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($WechatScratchproducts->getError());
			}
		} else {
			$this->pid = I('get.pid');
			if(!is_numeric($this->pid) || empty($this->pid)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//获取参数
			$this->linkcate_parameter = I('get.linkcate_parameter');

			$this->display();
		}
	}

	/**
	 * [edit 编辑]
	*/
	public function productsedit()
	{
		//提交数据处理
		if(IS_POST){
			$WechatScratchproducts = D('WechatScratchproducts');
			if($data = $WechatScratchproducts->create()){
				//获取表单的类型
				$id = I('post.id');
				$type = I('post.type');
				//key重新索引解决冲突
				$WechatScratchproducts->thumb = array_values($WechatScratchproducts->thumb); 
				$WechatScratchproducts->thumb = serialize($WechatScratchproducts->thumb);

				//设置内容
				$WechatScratchproducts->use = I('post.use','',false);
				$WechatScratchproducts->update_time = time(); //创建时间
				//保存数据
				if($WechatScratchproducts->save()){
					$this->success(L('_SAVE_SUCCESS_'),U('products',decode(I('post.linkcate_parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($WechatScratchproducts->getError());
			}
		} else {
			//验证数据
			$id = I('get.id');
			$this->pid = I('get.pid');
			if(!is_numeric($id) || !is_numeric($this->pid)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//获取参数
			$this->linkcate_parameter = I('get.linkcate_parameter');

			//缩略图配置
			$site_thumbw = C('SITE_SYSTEM_THUMB_WIDTH');
			$site_thumbh = C('SITE_SYSTEM_THUMB_HEIGHT');
			$thumbw = isset($site_thumbw) && !empty($site_thumbw) ? C('SITE_SYSTEM_THUMB_WIDTH') : C('SYSTEM_THUMB_WIDTH');
			$thumbh = isset($site_thumbh) && !empty($site_thumbh) ? C('SITE_SYSTEM_THUMB_HEIGHT') : C('SYSTEM_THUMB_HEIGHT');
			$this->thumbw = I('get.thumbw',$thumbw);
			$this->thumbh = I('get.thumbh',$thumbh);

			//获取数据
			$WechatScratchproducts = M('WechatScratchproducts');
			$data = $WechatScratchproducts->find($id);
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
	public function productsdel()
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
			$WechatScratchproducts = D('WechatScratchproducts');
			foreach ($ids as $key => $value) {
				$data = $WechatScratchproducts->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}

				//设置附件与图像
				$pictures_files_arr[] = unserialize($data['thumb']);
			}

			//获取数据
			$data_list = $WechatScratchproducts->field('thumb,photo')->select($id);

			//删除数据
			if($WechatScratchproducts->delete($id)){
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
	public function productssort()
	{
		if(IS_POST){
			$sort = I('post.sort');
			$sortarr = explode(",", $sort);

			//验证数据
			if(empty($sortarr)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			$WechatScratchproducts = M('WechatScratchproducts');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$WechatScratchproducts->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}
}
?>
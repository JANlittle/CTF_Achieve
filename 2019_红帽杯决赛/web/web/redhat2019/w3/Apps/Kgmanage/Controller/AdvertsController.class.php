<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 广告管理 ]
*/
namespace Kgmanage\Controller;

class AdvertsController extends CommonController
{
	/**
	 * [index 列表]
	 */
	public function index()
	{
		$Adspace = D('Adspace');
		$where['siteid'] = array('eq',session('siteid'));
		$count = $Adspace->field('id')->where($where)->count();
		//获取分页
		$page = getPage($count);
		$this->pagelist = $page->show();
		$data = $Adspace->where($where)->relation('ads')->order('sort ASC,create_time DESC')->limit($page->firstRow,$page->listRows)->select();
		$this->data = $data;

		//获取站点信息
        $nowsite = M('Site')->find(session('siteid'));
        $urlinfo = parse_url($nowsite['url']);

        //设置二级域名
        $this->domain = $urlinfo['host'] ? $urlinfo['host'] : '';

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
			$Adspace = D('Adspace');
			if($data = $Adspace->create()){
				//设置站点
				$Adspace->siteid = session('siteid');
				
				//创建时间
				$Adspace->create_time = time();

				//设置高度宽度
				$Adspace->width = (empty($Adspace->width) && $Adspace->width !== 0) ? 200 : $Adspace->width;
				$Adspace->height = (empty($Adspace->height) && $Adspace->height !== 0 ) ? 150 : $Adspace->height;

				//新增
				if($insertid = $Adspace->add()){
					//设置排序与唯一标识符
                    $update_data = array('sort'=>$insertid,'uniqid'=>aikehou_uniqid($insertid));
                    $Adspace->where("id = ".$insertid."")->setField($update_data);

					$this->success(L('_ADD_SUCCESS_'));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($Adspace->getError());
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
			$Adspace = D('Adspace');
			if($data = $Adspace->create()){
				//设置站点id
				$Adspace->siteid = I('post.siteid');

				//设置高度宽度
				$Adspace->width = (empty($Adspace->width) && $Adspace->width !== 0) ? 200 : $Adspace->width;
				$Adspace->height = (empty($Adspace->height) && $Adspace->height !== 0 ) ? 150 : $Adspace->height;

				//保存数据
				if($Adspace->save()){
					$this->success(L('_SAVE_SUCCESS_'));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($Adspace->getError());
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
			$Adspace = M('Adspace');
			$where['siteid'] = array('eq',session('siteid'));
			$where['uniqid'] = array('eq',$uniqid);
			$data = $Adspace->where($where)->find();
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
			$Adspace = D('Adspace');
			$delids = array();
			foreach ($ids as $key => $value) {
				$where['uniqid'] = array('eq',$value);
				$where['siteid'] = array('eq',session('siteid'));
				$data = $Adspace->where($where)->field('id')->find();
				$delids[] = $data['id'];
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//删除数据
			$new_where['id'] = array('in',$delids);
			$new_where['siteid'] = array('eq',session('siteid'));

			//获取数据
			$adspace_data = $Adspace->where($new_where)->relation(array('ads'))->select();
			if(!empty($adspace_data)){
				foreach ($adspace_data as $key => $value) {
					if(!empty($value['ads'])){
						foreach ($value['ads'] as $key_1 => $value_1) {
							$pictures_files_arr[] = unserialize($value_1['thumb']);;
						}
					}
				}
			}

			$delids = implode(",", $delids);
			//关联删除
			if($Adspace->relation('ads')->delete($delids)){
				//删除投票选项图片
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

			$Adspace = M('Adspace');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$Adspace->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}


	/**
	 * [ads 广告列表]
	*/
	public function ads()
	{
		$aid = I('get.id');
		if(!is_numeric($aid)){
			$this->error(L('_ACCESS_ERROR_'));
		}

		//获取广告位数据
		$this->adspace = M('Adspace')->find($aid);

		//获取字段数据
		$Ads = D('Ads');
		$where['aid'] = array('eq',$aid);
		$where['siteid'] = array('eq',session('siteid'));
		$count = $Ads->field('id')->where($where)->count();
		$page = getPage($count);
		$this->pagelist = $page->show();
		$data = $Ads->where($where)->relation('adspace')->order('sort ASC,create_time DESC')->limit($page->firstRow,$page->listRows)->select();

		//处理图片解析
		if(!empty($data)){
			foreach ($data as $key => $value) {
				if(!empty($value['thumb'])){
					$data[$key]['thumb'] = unserialize($value['thumb']);
				}
			}
		}
		$this->data = $data;

		//获取站点信息
        $nowsite = M('Site')->find(session('siteid'));
        $urlinfo = parse_url($nowsite['url']);

        //设置二级域名
        $this->domain = $urlinfo['host'] ? $urlinfo['host'] : '';

        //获取参数
		$this->parameter = I('get.parameter');

		//设置分页
		$this->linkcate_parameter =  getParameter(I('get.'),$page);

		$this->display();
	}


	/**
	 * [adsadd 新增广告]
	 */
	public function adsadd()
	{
		//数据提交
		if(IS_POST){
			$Ads = D('Ads');
			if($data = $Ads->create()){
				//设置站点
				$Ads->siteid = session('siteid');
				
				//创建时间
				$Ads->create_time = time();

				//如果是用不过去则设置时间为空
				if($Ads->datetype == 1){
					$Ads->starttime = '';
					$Ads->endtime = '';
				}

				//key重新索引解决冲突
				$Ads->thumb = array_values($Ads->thumb); 
				$Ads->thumb = serialize($Ads->thumb);

				//新增
				if($insertid = $Ads->add()){
					//设置排序与唯一标识符
                    $update_data = array('sort'=>$insertid,'uniqid'=>aikehou_uniqid($insertid));
                    $Ads->where("id = ".$insertid."")->setField($update_data);

					$this->success(L('_ADD_SUCCESS_'),U("ads",decode(I('post.linkcate_parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($Ads->getError());
			}
		} else {
			$this->aid = I('get.aid');
			if(!is_numeric($this->aid)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//获取参数
			$this->linkcate_parameter = I('get.linkcate_parameter');

			//获取广告位数据
			$this->adspace = M('Adspace')->find($this->aid);

			$this->display();
		}
	}

	/**
	 * [adsedit 编辑广告]
	*/
	public function adsedit()
	{
		//数据提交
		if(IS_POST){
			$Ads = D('Ads');
			if($data = $Ads->create()){
				$orignal_data = M('Ads')->find(I('post.id'));
				$orignal_data['thumb'] = unserialize($orignal_data['thumb']);
				//如果是用不过去则设置时间为空
				if($Ads->datetype == 1){
					$Ads->starttime = '';
					$Ads->endtime = '';
				}

				//key重新索引解决冲突
				$Ads->thumb = array_values($Ads->thumb); 
				$Ads->thumb = serialize($Ads->thumb);

				//保存
				if($Ads->save()){
					$this->success(L('_SAVE_SUCCESS_'),U("ads",decode(I('post.linkcate_parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($Ads->getError());
			}
		} else {
			$this->aid = I('get.aid');
			$this->id = I('get.id');
			if(!is_numeric($this->aid) || !is_numeric($this->id)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//获取参数
			$this->linkcate_parameter = I('get.linkcate_parameter');

			//获取广告位数据
			$this->adspace = M('Adspace')->find($this->aid);

			//获取广告数据
			$where['siteid'] = array('eq',session('siteid'));
			$where['id'] = array('eq',$this->id);
			$where['aid'] = array('eq',$this->aid);
			if(!$data = M('Ads')->where($where)->find()){
				$this->error(L('_ACCESS_ERROR_'));
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
	 * [adsdel 广告删除]
	*/
	public function adsdel()
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
			$Ads = M('Ads');
			$delids = array();
			foreach ($ids as $key => $value) {
				$where['uniqid'] = array('eq',$value);
				$where['siteid'] = array('eq',session('siteid'));
				$data = $Ads->where($where)->find();
				$delids[] = $data['id'];
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}

				//设置附件与图像
				$pictures_files_arr[] = unserialize($data['thumb']);
			}

			//删除数据
			$delids = implode(",", $delids);
			if($Ads->delete($delids)){

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
	 * [adssort 广告排序]
	 * @return [type] [description]
	 */
	public function adssort()
	{
		if(IS_POST){
			$sort = I('post.sort');
			$sortarr = explode(",", $sort);

			//验证数据
			if(empty($sortarr)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			$Ads = M('Ads');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$Ads->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}
}
?>
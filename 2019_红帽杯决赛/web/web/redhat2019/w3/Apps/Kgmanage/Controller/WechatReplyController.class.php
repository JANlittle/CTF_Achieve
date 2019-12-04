<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 自定义回复 ]
*/
namespace Kgmanage\Controller;

class WechatReplyController extends CommonController
{
	/**
	 * [index 列表]
	 */
	public function index()
	{
		$this->type = 1;
		$WechatReply = D('WechatReply');
		$where['type'] = array('eq',$this->type);
		$count = $WechatReply->field('id')->where($where)->count();
		$page = getPage($count);
		$this->pagelist = $page->show();
		$data = $WechatReply->where($where)->relation('wechatzan')->limit($page->firstRow,$page->listRows)->order('id DESC,sort ASC')->select();

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
			$WechatReply = D('WechatReply');
			if($data = $WechatReply->create()){

				//key重新索引解决冲突
				$WechatReply->thumb = array_values($WechatReply->thumb); 
				$WechatReply->thumb = serialize($WechatReply->thumb);

				$WechatReply->create_time = time();
				//设置类型
				$WechatReply->type = 1;

				if($insertid = $WechatReply->add()){
					//新增排序
					$WechatReply->where("id = {$insertid}")->setField('sort',$insertid);
					$this->success(L('_ADD_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($WechatReply->getError());
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
			$WechatReply = D('WechatReply');
			if($data = $WechatReply->create()){
				//key重新索引解决冲突
				$WechatReply->thumb = array_values($WechatReply->thumb); 
				$WechatReply->thumb = serialize($WechatReply->thumb);

				$WechatReply->update_time = time();
				//保存数据
				if($WechatReply->save()){
					$this->success(L('_SAVE_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($WechatReply->getError());
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
			$WechatReply = M('WechatReply');
			$data = $WechatReply->find($id);
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
			$WechatReply = M('WechatReply');
			foreach ($ids as $key => $value) {
				$data = $WechatReply->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}

				//设置附件与图像
				$pictures_files_arr[] = unserialize($data['thumb']);
			}

			//删除数据
			if($WechatReply->delete($id)){
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
	public function sort()
	{
		if(IS_POST){
			$sort = I('post.sort');
			$sortarr = explode(",", $sort);

			//验证数据
			if(empty($sortarr)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			$WechatReply = M('WechatReply');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$WechatReply->save($data);
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
		$this->type = 3;
		$WechatReply = D('WechatReply');
		$where['type'] = array('eq',$this->type);
		$count = $WechatReply->field('id')->where($where)->count();
		$page = getPage($count);
		$this->pagelist = $page->show();
		$this->data = $WechatReply->field('id,keywords,content,sort')->where($where)->order('sort ASC,id DESC')->limit($page->firstRow,$page->listRows)->select();

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
			$WechatReply = D('WechatReply');
			if($data = $WechatReply->create()){
				//设置内容
				$WechatReply->content = I('post.content','',false);
				$WechatReply->create_time = time();
				//设置类型
				$WechatReply->type = 3;
				if($insertid = $WechatReply->add()){
					//新增排序
					$WechatReply->where("id = {$insertid}")->setField('sort',$insertid);
					$this->success(L('_ADD_SUCCESS_'),U('wordindex',decode(I('post.parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($WechatReply->getError());
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
			$WechatReply = D('WechatReply');
			if($data = $WechatReply->create()){
				//设置内容
				$WechatReply->content = I('post.content','',false);
				$WechatReply->update_time = time();
				//保存数据
				if($WechatReply->save()){
					$this->success(L('_SAVE_SUCCESS_'),U('wordindex',decode(I('post.parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($WechatReply->getError());
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
			$WechatReply = M('WechatReply');
			$data = $WechatReply->find($id);
			if(!$data){
				$this->error(L('_NODATA_'));
			}
			$this->data = $data;

			$this->display();
		}
	}

	/**
	 * [index 列表]
	 */
	public function moreindex()
	{
		$this->type = 2;
		$WechatReply = D('WechatReply');
		$where['type'] = array('eq',$this->type);
		$count = $WechatReply->field('id')->where($where)->count();
		$page = getPage($count);
		$this->pagelist = $page->show();
		$data = $WechatReply->where($where)->order('sort ASC,id DESC')->limit($page->firstRow,$page->listRows)->select();
		
		foreach ($data as $key => $value) {
			$ids = unserialize($value['content']);
			if(is_array($ids)){
				foreach ($ids as $key_1=> $value_1) {
					$idsone = $WechatReply->find($value_1);
					$data[$key]['info'][] = $idsone['title'];
				}
			}
		}
		$this->data = $data;

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
	public function moreadd()
	{
		//数据提交
		if(IS_POST){
			$WechatReply = D('WechatReply');
			$ids = I('post.ids','');
			if($data = $WechatReply->create()){
				//设置内容
				$WechatReply->content = serialize($ids);
				$WechatReply->create_time = time();
				//设置类型
				$WechatReply->type = 2;
				if($insertid = $WechatReply->add()){
					//新增排序
					$WechatReply->where("id = {$insertid}")->setField('sort',$insertid);
					$this->success(L('_ADD_SUCCESS_'),U('moreindex',decode(I('post.parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($WechatReply->getError());
			}
		} else {
			//获取单图文
			$this->type = 1;
			$WechatReply = D('WechatReply');
			$where['type'] = array('eq',$this->type);
			$datalist = $WechatReply->where($where)->order('sort ASC,id DESC')->select();

			//处理图片解析
			if(!empty($datalist)){
				foreach ($datalist as $key => $value) {
					if(!empty($value['thumb'])){
						$datalist[$key]['thumb'] = unserialize($value['thumb']);
					}
				}
			}

			//获取参数
			$this->parameter = I('get.parameter');

			$this->datalist = $datalist;
			//获取菜单
			$this->display();
		}
	}

	/**
	 * [edit 编辑]
	*/
	public function moreedit()
	{
		//提交数据处理
		if(IS_POST){
			$WechatReply = D('WechatReply');
			$ids = I('post.ids','');
			if($data = $WechatReply->create()){
				//设置内容
				$WechatReply->content = serialize($ids);
				$WechatReply->update_time = time();
				//保存数据
				if($WechatReply->save()){
					$this->success(L('_SAVE_SUCCESS_'),U('moreindex',decode(I('post.parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($WechatReply->getError());
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
			$WechatReply = M('WechatReply');
			$data = $WechatReply->find($id);
			if(!$data){
				$this->error(L('_NODATA_'));
			}
			$this->data = $data;

			//获取单图文
			$this->type = 1;
			$WechatReply = D('WechatReply');
			$where['type'] = array('eq',$this->type);
			$datalist = $WechatReply->where($where)->order('sort ASC,id DESC')->select();

			//处理图片解析
			if(!empty($datalist)){
				foreach ($datalist as $key => $value) {
					if(!empty($value['thumb'])){
						$datalist[$key]['thumb'] = unserialize($value['thumb']);
					}
				}
			}

			$this->datalist = $datalist;
			
			//获取当前数据所有图文信息
			$ids = unserialize($data['content']);
			if(!is_array($ids)){
				$ids = array();
			}
			//设置得到的图文数组
			$idsinfo = array();
			foreach ($ids as $key => $value) {
				foreach ($datalist as $key_1 => $value_1) {
					if($value == $value_1['id']){
						$idsinfo[] = $value_1;
					}
				}
			}
			$this->idsinfo = $idsinfo;
			$this->display();
		}
	}

	/**
	 * [send 群发：调用客服接口]
	 * @return [type] [description]
	 */
	public function send()
	{
		if(IS_POST){
			$id = I('post.data');
			$WechatReply = M('WechatReply');
			$info = $WechatReply->find($id);

			$WechatMember = M('WechatMember');
			$memberWhere['openid'] = array('neq','');
			$members = $WechatMember->where($memberWhere)->select();

			//实例化方法
			$WechatAuth = R('Wechat/Wechat/getWechatAuth');
			if($info['type'] != 3){
				$sendData['sendtype']  = 'news';

				if($info['type'] == 1){
    				//设置url地址
		        	if(!preg_match('/(^http:\/\/|https:\/\/)/', $info['url'])){
		        		//如果没有设置跳转地址则直接显示内容
		        		$info['url'] = U("Wechat/Details/index",array('id'=>$info['id']),true,true);
		        	}
		        	//设置缩略图地址
		        	$http = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://';
					
					$thumb = unserialize($info['thumb']);
					$info['thumb'] = $http.I('server.HTTP_HOST').$thumb[0]['thumb'];

					$sendData['content'] = array(
						array($info['title'], $info['description'], $info['url'], $info['thumb']),
					);
				} else if($info['type'] == 2){
					//多图文
    				$ids = unserialize($info['content']);
    				if(empty($ids) || !is_array($ids)){
    					$this->error(L('_NO_CONTENT_'));
    				}

    				//获取多个数据
    				$morenews = array();
					foreach ($ids as $key=> $value) {
						$morenews[] = $WechatReply->find($value);
					}

    				//获取多图文列表
    				$sendData['content'] = array();
    				foreach ($morenews as $key => $value) {
    					//设置url地址
			        	if(!preg_match('/(^http:\/\/|https:\/\/)/', $value['url'])){
			        		//如果没有设置跳转地址则直接显示内容
			        		$value['url'] = U("Wechat/Details/index",array('id'=>$value['id']),true,true);
			        	}
			        	//设置缩略图地址
			        	$http = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://';
						
			        	$thumb = unserialize($value['thumb']);
						$value['thumb'] = $http.I('server.HTTP_HOST').$thumb[0]['thumb'];
			        	$sendData['content'][] = array($value['title'], $value['description'], $value['url'], $value['thumb']);
    				}
				}

			} else {
				$sendData['sendtype'] = 'text';
				$sendData['content'] = $info['content'];
			}

			//发送消息
			if(!empty($members)){
				foreach ($members as $key => $value) {
					$WechatAuth->messageCustomSend($value['openid'], $sendData['content'],$sendData['sendtype']);
				}
			}

			$this->success('数据发送成功！');
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [send 群发：调用群发接口]
	 * @return [type] [description]
	 */
	public function sendGroup()
	{
		if(IS_POST){
			$id = I('post.data');
			$info = M('WechatReply')->find($id);

			$WechatMember = M('WechatMember');
			$memberWhere['openid'] = array('neq','');
			$members = $WechatMember->where($memberWhere)->select();
			$touser = array();
			$countMember = count($members);
			if(!empty($members)){
				foreach ($members as $key => $value) {
					if($countMember <= 1){
						//填写图文消息的接收者，一串OpenID列表，OpenID最少2个，最多10000个
						$touser[] = $value['openid'];
						$touser[] = $value['openid'];
					} else{
						$touser[] = $value['openid'];
					}
				}
			}

			//验证不存在微信用户时
			if(empty($touser)){
				$this->error(L('_WECHATMEMBER_ERROR_'));
			}

			//获取微信用户的openid
			$sendData = array(
				"touser" => $touser,
				"msgtype" => "text",
				"text" => array("content" => $info['content'])
			);

			//实例化方法
			$WechatAuth = R('Wechat/Wechat/getWechatAuth');
			//群发
			$message = $WechatAuth->sendGroup($sendData);
			if(!$message['errcode']){
				$this->success('数据发送成功！');
			} else {
				$this->error('数据发送失败！');
			}

		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}
}
?>
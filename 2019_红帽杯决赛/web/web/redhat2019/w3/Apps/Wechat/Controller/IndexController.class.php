<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 微信 ]
*/
namespace Wechat\Controller;
use Think\Controller;
use Api\Wechat\Wechat;

class IndexController extends Controller {
    public function index(){
	    /* 加载微信SDK */
	    $config = R('Wechat/getConfig');
	    $this->wechat = new Wechat($config['token']);
	    $data = $this->wechat->request();
	    if(!empty($data) && is_array($data)){
	    	$content = $data['MsgType'];
	    	switch ($data['MsgType']) {
	    		case 'text':
	    		/*回复文本消息*/
	    		    $WechatReply = M('WechatReply');
	    			$where['keywords'] = array('eq',$data['Content']);
	    		    $textData = $WechatReply->where($where)->find();
	    			if(!$textData){
	    				$this->wechat->response("没有找到相关消息！",Wechat::MSG_TYPE_TEXT);
	    				exit();
	    			}
	    			if($textData['type'] == 3){
	    				//文本
	    				$this->wechat->response($textData['content'],Wechat::MSG_TYPE_TEXT);
	    			} else if($textData['type'] == 1) {
	    				//单图文
	    				//设置url地址
			        	if(!preg_match('/(^http:\/\/|https:\/\/)/', $textData['url'])){
			        		//如果没有设置跳转地址则直接显示内容
			        		$textData['url'] = U("Details/index",array('id'=>$textData['id']),true,true);
			        	}
			        	//设置缩略图地址
			        	$http = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://';
						
						//设置thumb
					    $thumb = unserialize($textData['thumb']);
						$textData['thumb'] = $http.I('server.HTTP_HOST').$thumb[0]['thumb'];

			        	$content=array(
						    array($textData['title'], $textData['description'], $textData['url'], $textData['thumb']),
						);
					    $this->wechat->response($content,Wechat::MSG_TYPE_NEWS);
	    			} else if($textData['type'] == 2){
	    				//多图文
	    				$ids = unserialize($textData['content']);
	    				if(empty($ids) || !is_array($ids)){
	    					$this->wechat->response("没有找到相关消息！",Wechat::MSG_TYPE_TEXT);
	    					exit();
	    				}

	    				//获取多个数据
	    				$morenews = array();
						foreach ($ids as $key=> $value) {
							$morenews[] = $WechatReply->find($value);
						}

	    				//获取多图文列表
	    				$content = array();
	    				foreach ($morenews as $key => $value) {
	    					//设置url地址
				        	if(!preg_match('/(^http:\/\/|https:\/\/)/', $value['url'])){
				        		//如果没有设置跳转地址则直接显示内容
				        		$value['url'] = U("Details/index",array('id'=>$value['id']),true,true);
				        	}
				        	//设置缩略图地址
				        	$http = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://';
							
							//设置thumb
					    	$thumb = unserialize($value['thumb']);
							$value['thumb'] = $http.I('server.HTTP_HOST').$thumb[0]['thumb'];
				        	$content[] = array($value['title'], $value['description'], $value['url'], $value['thumb']);
	    				}
	    				$this->wechat->response($content,Wechat::MSG_TYPE_NEWS);
	    			}
	    		/*end 回复文本消息*/
	    			break;
	    		case 'image':
	    			//$this->wechat->response($data['MediaId'],Wechat::MSG_TYPE_IMAGE);
	    			break;
	    		case 'voice':
	    			//$this->wechat->response($data['MediaId'],Wechat::MSG_TYPE_VOICE);
	    			break;
	    		case 'video':
	    			//$this->wechat->response(array($data['MediaId'], '视频', '3434'),Wechat::MSG_TYPE_VIDEO);
	    			break;
	    		case 'shortvideo':
	    			//$this->wechat->response(array($data['MediaId'], '视频', '3434'),Wechat::MSG_TYPE_VIDEO);
	    			break;
	    		case 'music':
	    			//$this->wechat->response($content,Wechat::MSG_TYPE_MUSIC);
	    			break;
	    		case 'news':
	    			//$this->wechat->response($content,Wechat::MSG_TYPE_NEWS);
	    			break;
	    		case 'location':
	    			//$this->wechat->response("信息：MsgType：".$data['MsgType'].",Location_X：".$data['Location_X'].",Location_Y：".$data['Location_Y'].",Scale：".$data['Scale'].",Label：".$data['Label'],Wechat::MSG_TYPE_TEXT);
	    			break;
	    		case 'link':
	    			//$this->wechat->response($content,Wechat::MSG_TYPE_LINK);
	    			break;
	    		case 'event':
	    			switch ($data['Event']) {
	    				case 'subscribe':
	    				/*获取用户信息并写入*/
					        //实例化微信接口
							$WechatAuth = R("Wechat/getWechatAuth");
							//获取用户信息
							$userInfo = $WechatAuth->userInfo($data['FromUserName']);
							if(!isset($userInfo['errcode'])){
								//写入数据
								$WechatMember = M("WechatMember");
								if(!$memberOne = $WechatMember->where("openid = '".$data['FromUserName']."'")->find()){
									//新增用户
									$userInfo['reg_time'] = time();
									$insertid = $WechatMember->data($userInfo)->add();
									$WechatMember->where("id = '".$insertid."'")->setField("sort",$insertid);
								} else {
									//更新用户
									$userInfo['id'] = $memberOne['id'];
									$WechatMember->data($userInfo)->save();
								}
							} else {
								//获取不到用户数据（没有权限）
								$this->wechat->response($userInfo['errmsg'],Wechat::MSG_TYPE_TEXT);
								exit();
							}
						/*end 获取用户信息并写入*/

						/*回复关注信息*/
	    					$cacheName = 'WechatWelcome';
					        //获取欢迎语配置
					        $config_welcome = S($cacheName);
					        //空的情况下生成欢迎语缓存
					        if(empty($config_welcome)){
					            //生成微信缓存
					            $cache_data = M('WechatWelcome')->find();
					            S($cacheName, $cache_data);
					        }
					        //缓存设置成配置
					        $welcomeConfig = S($cacheName);
					        if($welcomeConfig['type'] == 1){
					        	$this->wechat->response($welcomeConfig['content'],Wechat::MSG_TYPE_TEXT);
					        } else {
					        	//设置url地址
					        	if(!preg_match('/(^http:\/\/|https:\/\/)/', $welcomeConfig['url'])){
					        		$welcomeConfig['url'] = "";
					        	}
					        	//设置缩略图地址
					        	$http = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://';

					        	//设置thumb
					        	$thumb = unserialize($welcomeConfig['thumb']);
								$welcomeConfig['thumb'] = $http.I('server.HTTP_HOST').$thumb[0]['thumb'];

					        	//关注回复内容
					        	$content=array(
								    array($welcomeConfig['title'], $welcomeConfig['content'], $welcomeConfig['url'], $welcomeConfig['thumb']),
								);
					        	$this->wechat->response($content,Wechat::MSG_TYPE_NEWS);
					        }
					    /*end 回复关注信息*/
			    			break;
			    		case 'SCAN':
			    			//$this->wechat->response($content,Wechat::MSG_EVENT_SCAN);
			    			break;
			    		case 'LOCATION':
			    			//$this->wechat->response($content,Wechat::MSG_EVENT_LOCATION);
			    			break;
			    		case 'CLICK':
			    		/*回复文本消息*/
			    			$WechatReply = M('WechatReply');
			    			$where['keywords'] = array('eq',$data['EventKey']);
			    		    $textData = $WechatReply->where($where)->find();
			    			if(!$textData){
			    				$this->wechat->response("没有找到相关消息！",Wechat::MSG_TYPE_TEXT);
	    						exit();
			    			}
			    			if($textData['type'] == 3){
			    				//文本
			    				$this->wechat->response($textData['content'],Wechat::MSG_TYPE_TEXT);
			    			} else if($textData['type'] == 1) {
			    				//单图文
			    				//设置url地址
					        	if(!preg_match('/(^http:\/\/|https:\/\/)/', $textData['url'])){
					        		//如果没有设置跳转地址则直接显示内容
					        		$textData['url'] = U("Details/index",array('id'=>$textData['id']),true,true);
					        	}
					        	//设置缩略图地址
					        	$http = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://';
								$textData['thumb'] = $http.I('server.HTTP_HOST').$textData['thumb'];
					        	$content=array(
								    array($textData['title'], $textData['description'], $textData['url'], $textData['thumb']),
								);
							    $this->wechat->response($content,Wechat::MSG_TYPE_NEWS);
			    			} else if($textData['type'] == 2){
			    				//多图文
			    				$ids = unserialize($textData['content']);
			    				if(empty($ids) || !is_array($ids)){
			    					$this->wechat->response("没有找到相关消息！",Wechat::MSG_TYPE_TEXT);
			    					exit();
			    				}

			    				//获取多个数据
			    				$morenews = array();
								foreach ($ids as $key=> $value) {
									$morenews[] = $WechatReply->find($value);
								}

			    				//获取多图文列表
			    				$content = array();
			    				foreach ($morenews as $key => $value) {
			    					//设置url地址
						        	if(!preg_match('/(^http:\/\/|https:\/\/)/', $value['url'])){
						        		//如果没有设置跳转地址则直接显示内容
						        		$value['url'] = U("Details/index",array('id'=>$value['id']),true,true);
						        	}
						        	//设置缩略图地址
						        	$http = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://';
									$value['thumb'] = $http.I('server.HTTP_HOST').$value['thumb'];
						        	$content[] = array($value['title'], $value['description'], $value['url'], $value['thumb']);
			    				}
			    				$this->wechat->response($content,Wechat::MSG_TYPE_NEWS);
			    			}
			    		/*end 回复文本消息*/
			    			break;
			    		case 'VIEW':
			    			//$this->wechat->response($content,Wechat::MSG_EVENT_CLICK);
			    			break;
			    		case 'MASSSENDJOBFINISH':
			    			//$this->wechat->response($content,Wechat::MSG_EVENT_MASSSENDJOBFINISH);
			    			break;
	    			}
	    			break;
	    	}
	    }
    }
}
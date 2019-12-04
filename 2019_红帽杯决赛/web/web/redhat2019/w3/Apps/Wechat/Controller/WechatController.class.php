<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 微信函数处理 ]
*/
namespace Wechat\Controller;
use Think\Controller;

class WechatController extends Controller{

    public $WechatAuth;
    public $config;
    public $access_token;

    /**
     * [_initialize 初始化函数]
     */
    public function _initialize()
    {
    /*获取配置*/
        $cache_name = 'WechatConfig';
        //获取微信配置
        $this->config = S($cache_name);
        //空的情况下生成缓存
        if(empty($this->config)){
            //生成微信缓存
            $cache_data = M('WechatPublic')->find();
            S($cache_name, $cache_data);
        }
        //缓存设置成配置
        $this->config = S($cache_name);
    /*end 获取配置*/

    /*设置access_token*/
        //获取access_token
        $config = $this->config;

        //实例化微信接口
        $access_token = S('access_token');
        if(!empty($access_token)){
            $token = $access_token['access_token'];
        } else {
            $token = null;
        }

        if(!is_subclass_of($this->WechatAuth, '\Api\Wechat\WechatAuth')){
            //实例化类
            $this->WechatAuth = new \Api\Wechat\WechatAuth($config['appid'],$config['appsecret'],$token);
        }

        //获取acces_token
        if(empty($access_token)){
            $access_token = $this->WechatAuth->getAccessToken();
            S('access_token',$access_token,$access_token['expires_in']);
        }
        $this->access_token = $access_token;
    /*end 设置access_token*/
    }

    /**
     * [getAccessToken 获取access_token的WechatAuth对象]
     * @return [type] [description]
    */
    public function getAccessToken()
    {
        return $this->access_token;
    }

    /**
     * [getConfig 获取配置]
     * @return [type] [description]
     */
    public function getConfig(){
        return $this->config;
    }

    /**
     * [getWechatAuth 获取微信WechatAuth对象]
     * @return [type] [description]
     */
    public function getWechatAuth(){
        return $this->WechatAuth;
    }

    /**
     * [index 获取签名]
     * @return [type] [description]
     */
    public function getSignPackage(){
        //实例化微信js接口
        $WechatJsapiTicket = S('WechatJsapiTicket');
        if(empty($WechatJsapiTicket)){
            $WechatJsapiTicket = $this->WechatAuth->getJsApiTicket();
            S('WechatJsapiTicket',$WechatJsapiTicket,$WechatJsapiTicket['expires_in']);
        }

        //获取signPackage签名信息
        return $this->WechatAuth->getSignPackage($WechatJsapiTicket['ticket']);
    }

    /**
     * [auth2 授权获取用户信息]
     * @return [type] [description]
     */
    public function auth2($redirect_url)
    {
    	$AuthAccessToken = S('AuthAccessToken');
        //获取AuthAccessToken
        if(empty($AuthAccessToken) || isset($AuthAccessToken['errcode'])){
            $code = I('get.code','');
            $url = $this->WechatAuth->getRequestCodeURL($redirect_url,1);
        	if(empty($code)){
        		header("Location:".$url);
        		exit();
        	}
            $AuthAccessToken = $this->WechatAuth->getAccessToken('code', $code);
            S('AuthAccessToken',$AuthAccessToken,$AuthAccessToken['expires_in']);
        }

        $userInfo = $this->WechatAuth->getUserInfo($AuthAccessToken);
        //验证用户是否存在
        $WechatMember = M("WechatMember");
		if(!$memberOne = $WechatMember->where("openid = '".$userInfo['openid']."'")->find()){
			//新增用户
			$userInfo['reg_time'] = time();
			$insertid = $WechatMember->data($userInfo)->add();
			$WechatMember->where("id = '".$insertid."'")->setField("sort",$insertid);
		} else {
			//更新用户
			$userInfo['id'] = $memberOne['id'];
			$WechatMember->data($userInfo)->save();
		}
		return $userInfo;
    }
}
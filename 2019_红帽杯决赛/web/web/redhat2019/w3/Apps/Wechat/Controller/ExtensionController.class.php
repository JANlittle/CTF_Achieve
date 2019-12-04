<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 微信宣传页 ]
*/
namespace Wechat\Controller;
use Think\Controller;

class ExtensionController extends Controller {
    public function index(){
    	$cacheName = 'WechatExtension';
        //获取配置
        $config = S($cacheName);
        //空的情况下生成缓存
        if(empty($config)){
            //生成微信缓存
            $cache_data = M('WechatExtension')->find();
            S($cacheName, $cache_data);
        }
        //缓存设置成配置
        $data = S($cacheName);

        //处理图片解析
        if(!empty($data)){
            if(!empty($data['thumb'])){
                $data['thumb'] = unserialize($data['thumb']);
            }
        }
        $this->data = $data;
        $this->display();
    }
}
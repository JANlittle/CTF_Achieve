<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 建议意见 ]
*/
namespace Wechat\Controller;
use Think\Controller;

class MessageController extends Controller {
    /**
     * [_initialize 初始化函数]
     */
    public function _initialize()
    {
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
        $this->WechatExtension = S($cacheName);
    }

    /**
     * [index 首页]
     * @return [type] [description]
    */
    public function index(){
        if(IS_POST){
            $data = I('post.data');
            if(empty($data['name'])){
                $this->error('请填写姓名！');
            }
            if(empty($data['contact'])){
                $this->error('请填写正确的联系方式！');
            }
            if(empty($data['content'])){
                $this->error('请填写内容！');
            }

            //设置用户id
            $data['uid'] = session('?opneid') ? session('opneid') : 0;

            //验证是否已经留过建议
            $WechatMessage = M('WechatMessage');
            $where['name'] = array('eq',$data['name']);
            $where['uid'] = array('eq',$data['uid']);
            $where['contact'] = array('eq',$data['contact']);
            if($WechatMessage->where($where)->find()){
                $this->error('您已经留过建议，感谢您的参与！');
            }

            //地区
            $data['ip'] = get_client_ip(); 
            $ip = new \Org\Net\IpLocation('UTFWry.dat');
            $area = $ip->getlocation($data['ip']);
            $data['area'] = serialize($area);

            //设置时间
            $data['create_time'] = time();
            //添加
            if($insertId = $WechatMessage->data($data)->add()){
                $WechatMessage->where("id = {$insertId}")->setField('sort',$insertId);
                $this->success('留言成功，感谢您的参与！');
            } else {
                $this->error('留言失败，请刷新重试！');
            }

        } else {
            //获取用户的信息
            if(!session("?opneid")){
                $userInfo = R('Wechat/auth2',array(U('',http_build_query(I('get.')),false,true)));
                session('opneid',$userInfo['openid']);
            }

            $this->display();
        }
    }
}
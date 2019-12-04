<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 微信内容页 ]
*/
namespace Wechat\Controller;
use Think\Controller;

class DetailsController extends Controller {

    /**
     * [index 内容首页]
     * @return [type] [description]
     */
    public function index(){
        $id = I('get.id','');
        if(empty($id) || !is_numeric($id)){
            exit('参数错误！');
        }
        /*session("opneid",null);
        S('AuthAccessToken',null);
        exit();*/
        //获取用户的信息
        if(!session("?opneid")){
        	$userInfo = R('Wechat/auth2',array(U('',http_build_query(I('get.')),false,true)));
	        session('opneid',$userInfo['openid']);
        }

        //更新浏览次数
        $WechatReply = M('WechatReply');
        $where['id'] = array('eq',$id);
        $WechatReply->where($where)->setInc('count',1);
    	$data  = D('WechatReply')->relation('wechatzan')->find($id);
        //设置是否已经点赞
        if(!empty($data['wechatzan'])){
            foreach ($data['wechatzan'] as $key => $value) {
                if($value['openid'] == session('openid')){
                    $data['zan'] = 1;
                    break;
                }
            }
        } else {
            $data['zan'] = 0;
        }
        $this->data = $data;
        //微信配置
    	$this->wechatConfig = R('Wechat/getConfig');
        //微信签名包
        $this->SignPackage = R("Wechat/getSignPackage");
        $this->display();
    }

    /**
     * [zan 点赞]
     * @return [type] [description]
     */
    public function zan()
    {
        if(IS_POST){
            $WechatZan = M('WechatZan');
            $openid = session('opneid');;
            $id = I('post.data');
            //验证当前用户是否已经点赞
            $where['aid'] = $id;
            $where['openid'] = $openid;
            if($WechatZan->where($where)->find()){
                $this->error('已经点过赞了！');
            }
            //设置新增数据
            $data['openid'] = $openid;
            $data['aid'] = $id;
            $data['create_time'] = time();
            if($WechatZan->data($data)->add()){
                $count = $WechatZan->where("aid = ".$id."")->count();
                $return['info'] = '点赞成功！';
                $return['status'] = 1;
                $return['count'] = $count;
                $this->ajaxReturn($return);
            } else {
                $this->success('点赞失败！');
            }
        } else {
            $this->error('非法操作！');
        }
    }
}
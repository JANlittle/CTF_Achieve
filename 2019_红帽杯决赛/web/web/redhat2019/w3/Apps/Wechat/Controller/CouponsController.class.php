<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 优惠券 ]
*/
namespace Wechat\Controller;
use Think\Controller;

class CouponsController extends Controller {
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
        $this->id = I('get.id');
        if(!is_numeric($this->id) || empty($this->id)){
            $this->error('参数错误！');
        }
        //获取用户的信息
        if(!session("?openid")){
            $userInfo = R('Wechat/auth2',array(U('',http_build_query(I('get.')),false,true)));
            session('openid',$userInfo['openid']);
            session('name',$userInfo['nickname']);
        }
        $Coupons = M('WechatCoupons');
        $this->data = $Coupons->find($this->id);

        //获取优惠券列表
        $WechatCouponsmember = M('WechatCouponsmember');
        $member_where['cid'] = array('eq',$this->id);
        $member_where['openid'] = array('eq',session('openid'));
        $this->sn = $WechatCouponsmember->where($member_where)->select();
        $this->display();
    }

    /**
     * [getCoupons 获取优惠券]
     * @return [type] [description]
     */
    public function getCoupons()
    {
        if(IS_POST){
            $postData = I('post.data');
            //获取优惠券信息
            $Coupons = M('WechatCoupons');
            $onedata = $Coupons->find($postData['id']);

            //对比总共优惠券数量
            $WechatCouponsmember = M('WechatCouponsmember');
            $member_where['cid'] = array('eq',$postData['id']);
            $countAll = $WechatCouponsmember->where($member_where)->count();
            if($onedata['number'] <= $countAll){
                //优惠券数量不足
                $return['info'] = '优惠券数量不足！';
                $return['url'] = '';
                $return['status'] = 0;
                $this->ajaxReturn($return);
            }

            //每个用户获取的最大次数对比
            $member_where['openid'] = array('eq',session('openid'));
            $countMember = $WechatCouponsmember->where($member_where)->count();
            if($onedata['max'] <= $countMember){
                //用户已经获取了上限的优惠券
                $return['info'] = '您获取优惠券次数已经用完！';
                $return['url'] = '';
                $return['status'] = 0;
                $this->ajaxReturn($return);
            }

            //设置优惠券
            $addData['cid'] = $postData['id'];
            $addData['openid'] = session('openid');
            $addData['name'] = session('name');
            $addData['sn'] = uniqid();
            $addData['status'] = 0;
            $addData['create_time'] = time();

            $insertid = $WechatCouponsmember->data($addData)->add();
            //新增排序
            $WechatCouponsmember->where("id = {$insertid}")->setField('sort',$insertid);

            $return['info'] = '获取优惠券成功！';
            $return['url'] = U('show',array('id'=>$postData['id'],'snid'=>$insertid));
            $return['status'] = 1;
            $this->ajaxReturn($return);

        }
    }


    /**
     * [show 显示优惠券信息]
     */
    public function show()
    {
        $id = I('get.id');
        $snid = I('get.snid');
        if(!is_numeric($id) || empty($id) || !is_numeric($snid) || empty($snid)){
            $this->error('参数错误！');
        }

        //获取优惠券信息
        $Coupons = M('WechatCoupons');
        $this->coupons = $Coupons->find($id);

        //获取成员信息
        $WechatCouponsmember = M('WechatCouponsmember');
        $where['cid'] = array('eq',$id);
        $where['id'] = array('eq',$snid);
        $this->data = $WechatCouponsmember->where($where)->find();
        $this->display();
    }
}
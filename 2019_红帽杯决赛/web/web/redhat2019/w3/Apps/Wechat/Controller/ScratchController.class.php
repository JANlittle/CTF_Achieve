<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 刮刮卡 ]
*/
namespace Wechat\Controller;
use Think\Controller;

class ScratchController extends Controller {
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

        $Scratch = M('WechatScratch');
        $scratchdata = $Scratch->find($this->id);
        $this->data = $scratchdata;

        //获取奖品列表
        $prize_where['pid'] = array('eq',$this->id);
        $prize_where['status'] = array('eq',1);
        $price = D('WechatScratchproducts')->where($prize_where)->relation(array('WechatScratchmember'))->order('sort ASC')->select();
        $this->price = $price;

        //获取所有的中奖纪录
        $WechatScratchmember = M('WechatScratchmember');
        $member_where['cid'] = array('eq',$this->id);
        $pricelistall = $WechatScratchmember->where($member_where)->order('create_time DESC')->select();

        //获取当前用户中奖纪录
        $pricelist = array();
        if(!empty($pricelistall)){
            foreach ($pricelistall as $key => $value) {
                if($value['openid'] == session('openid')){
                    $pricelist[$key] = $value;
                }
            }
        }
        $this->pricelist = $pricelist;

        //刮刮卡剩余总数
        $this->prizetotal = $scratchdata['number'] - count($pricelistall);

        //用户抽奖剩余次数
        $this->mypirze_count = $scratchdata['max'] - count($this->pricelist);

        //设置中奖的奖品
        $prize_arr = array();
        $total = $scratchdata['number'];
        if(!empty($price)){
            foreach ($price as $key => $value) {
                //需要扣除已经被中走的奖项
                $prize_arr[] = array('id' => $value['id'],'prize' => $value['title'],'v'=>($value['number'] - count($value['WechatScratchmember'])));
                $total -= intval($value['number']);
            }
            //不中奖概率，需要扣除中未中奖的数量
            $noprize = array();
            if(!empty($pricelistall)){
                foreach ($pricelistall as $key => $value) {
                    if($value['prizeid'] == 0){
                        $noprize[$key] = $value;
                    }
                }
            }
            $total -= count($noprize);
            if($total < 0){
                $total = 0;
            }
            $prize_arr[] = array('id' => 0,'prize' => '未中奖','v'=>$total);
        }

        //获取中奖奖品id
        foreach ($prize_arr as $key => $val) {   
            $prize_arr_v[$val['id']] = $val['v'];   
        }   
        $prizeid = prizeRandom($prize_arr_v); //根据概率获取奖项id
        if($prizeid == 0){
            $this->myprice = array('id'=>0,'title'=>'未中奖','name'=>'没奖品');
        } else {
            $myprice = M('WechatScratchproducts')->find($prizeid);
            $this->myprice = array('id'=>$myprice['id'],'title'=>$myprice['title'],'name'=>$myprice['name']);
        }
        $this->display();
    }

    /**
     * [getScratch 写入奖品数据]
     * @return [type] [description]
     */
    public function getScratch()
    {
        if(IS_POST){
            $postData = I('post.data');
            //获取优惠券信息
            $Scratch = M('WechatScratch');
            $onedata = $Scratch->find($postData['id']);

            //对比总共刮刮卡数量
            $WechatScratchmember = M('WechatScratchmember');
            $member_where['cid'] = array('eq',$postData['id']);
            $countAll = $WechatScratchmember->where($member_where)->count();
            if($onedata['number'] <= $countAll){
                //刮刮卡数量不足
                $return['info'] = '刮刮卡数量不足！';
                $return['url'] = '';
                $return['status'] = 0;
                $this->ajaxReturn($return);
            }

            //每个用户获取的最大次数对比
            $member_where['openid'] = array('eq',session('openid'));
            $countMember = $WechatScratchmember->where($member_where)->count();
            if($onedata['max'] <= $countMember){
                //用户已经获取了上限的刮刮卡
                $return['info'] = '刮卡的次数已经用完！';
                $return['url'] = '';
                $return['status'] = 0;
                $this->ajaxReturn($return);
            }

            //获取奖品信息
            if($postData['prizeid'] == 0){
                $price = array('title'=>'未中奖','name'=>'没奖品');
            } else {
                $prize_where['pid'] = array('eq',$postData['id']);
                $prize_where['id'] = array('eq',$postData['prizeid']);
                $price = M('WechatScratchproducts')->where($prize_where)->find();
            }

            //设置刮刮卡
            $addData['cid'] = $postData['id'];
            $addData['openid'] = session('openid');
            $addData['name'] = session('name');
            $addData['prizeid'] = $postData['prizeid'];
            $addData['prizename'] = $price['name'];
            $addData['prizetitle'] = $price['title'];
            $addData['status'] = 0;
            $addData['create_time'] = time();

            $insertid = $WechatScratchmember->data($addData)->add();
            //新增排序
            $WechatScratchmember->where("id = {$insertid}")->setField('sort',$insertid);

            if($postData['prizeid'] == 0){
                $return['info'] = "<h6>很抱歉！没有中奖，还需继续努力</h6><p class='p_10'>你还有".($onedata['max'] - $countMember - 1)."次机会。</p>";
            } else {
                $return['info'] = "<h6>你的运气太好了！</h6><p class='p_10'>你获得：".$price['title']."，奖品是：".$price['name']."，请联系客服领取。</p>";
            }
            $return['url'] = U('index',array('id'=>$postData['id']));
            $return['status'] = 1;
            $this->ajaxReturn($return);

        }
    }
}
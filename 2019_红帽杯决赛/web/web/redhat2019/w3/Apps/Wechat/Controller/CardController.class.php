<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 会员卡 ]
*/
namespace Wechat\Controller;
use Think\Controller;

class CardController extends Controller {
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
     * [index 会员卡]
     * @return [type] [description]
    */
    public function index(){
        //获取用户的信息
        if(!session("?opneid")){
            $userInfo = R('Wechat/auth2',array(U('',http_build_query(I('get.')),false,true)));
            session('opneid',$userInfo['openid']);
        }

        $WechatCardmember = M('WechatCardmember');
        $where['openid'] = array('eq',session('opneid'));
        $member = $WechatCardmember->where($where)->find();
        if($member && !empty($member['name']) && !empty($member['tel'])){
            redirect(U('showCard'));
        }

        $card = M('WechatCard');
        $card_info = $card->find();
        //处理图片解析
        if(!empty($card_info)){
            if(!empty($card_info['thumb'])){
                $card_info['thumb'] = unserialize($card_info['thumb']);
            }
        }

        $this->card = $card_info;
        $this->display();
    }

    /**
     * [useCard 使用说明]
     * @return [type] [description]
     */
    public function useCard()
    {
        $card = M('WechatCard');
        $this->card = $card->field('name,content')->find();
        $this->display('usecard');
    }

    /**
     * [getCard 获取用户]
     * @return [type] [description]
     */
    public function getCard()
    {
        $WechatCardmember = M('WechatCardmember');
        $where['openid'] = array('eq',session('opneid'));
        $member = $WechatCardmember->where($where)->find();
        if(!$member || empty($member['tel']) || empty($member['name'])){
            redirect(U('memberInfo'));
        } else {
            redirect(U('showCard'));
        }
    }

    /**
     * [showCard 显示会员卡信息]
     * @param  string $value [description]
     */
    public function showCard()
    {
        //获取用户信息
        $WechatCardmember = M('WechatCardmember');
        $where['openid'] = array('eq',session('opneid'));
        $this->member = $WechatCardmember->where($where)->find();
        if(!$this->member){
            redirect(U('index'));
        }

        //获取会员卡信息
        $card = M('WechatCard');
        $card_info = $card->find();
        //处理图片解析
        if(!empty($card_info)){
            if(!empty($card_info['thumb'])){
                $card_info['thumb'] = unserialize($card_info['thumb']);
            }
        }
        $this->card = $card_info;

        //获取通知的条数
        $WechatCardnotice = M('WechatCardnotice');
        $noticewhere['status'] = array('eq',1);
        $this->noticecount = $WechatCardnotice->where($noticewhere)->count();
        $this->display('showcard');
    }

    /**
     * [memberInfo 填写用户信息]
     * @return [type] [description]
     */
    public function memberInfo()
    {
        if(IS_POST){
            $postData = I('post.data');
            $WechatCardmember = M('WechatCardmember');
            $where['openid'] = array('eq',session('opneid'));
            $member = $WechatCardmember->where($where)->find();
            if(!$member){
                //新增
                $addData['name'] = $postData['name'];
                $addData['tel'] = $postData['phone'];
                $addData['openid'] = session('opneid');
                $addData['create_time'] = time();

                $card = M('WechatCard');
                $card = $card->field('qianzhui,length')->find();
                $min = "8".str_repeat("0", $card['length'] - 1);
                $max = "8".str_repeat("9", $card['length'] - 1);
                $addData['number'] = $card['qianzhui'].mt_rand($min,$max);
                $insertid = $WechatCardmember->data($addData)->add();
                //新增排序
                $WechatCardmember->where("id = {$insertid}")->setField('sort',$insertid);
            } else {
                //更新
                $saveData['id'] = $member['id'];
                $saveData['openid'] = session('opneid');
                $saveData['tel'] = $postData['phone'];
                $saveData['name'] = $postData['name'];
                $WechatCardmember->data($saveData)->save();
            }
            //返回数据
            $return['info'] = '信息填入成功！';
            $return['url'] = U("showCard");
            $return['status'] = 1;
            $this->ajaxReturn($return);
        } else {
            $WechatCardmember = M('WechatCardmember');
            $where['openid'] = array('eq',session('opneid'));
            $this->member = $WechatCardmember->where($where)->find();
            $this->display('memberinfo');
        }
    }

    /**
     * [notice description]
     * @return [type] [description]
     */
    public function notice()
    {
        $WechatCardnotice = M('WechatCardnotice');
        $where['status'] = array('eq',1);
        $this->notice = $WechatCardnotice->where($where)->order('create_time DESC')->select();
        $this->display();
    }
}
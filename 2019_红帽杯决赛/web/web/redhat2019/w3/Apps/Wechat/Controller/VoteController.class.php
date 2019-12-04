<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 投票 ]
*/
namespace Wechat\Controller;
use Think\Controller;

class VoteController extends Controller {
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
            $data = I('post.');
            if(!isset($data['voteArr']) || empty($data['voteArr'])){
                $this->error('请选择投票选项！');
            }
            $dataList = array();
            if(!empty($data['voteArr'])){
                foreach ($data['voteArr'] as $key => $value) {
                    $dataList[] = array('openid'=>session('openid'),'name'=>session('name'),'vid'=>$data['vid'],'optionsid'=>$value,'type'=>$data['type'],'votetype'=>$data['votetype'],'votecount'=>$data['votecount'],'create_time'=>time());
                }
            }

            //获取投票信息
            $WechatVote = M('WechatVote');
            $where_vote['id'] = array('eq',$data['vid']);
            $where_vote['type'] = array('eq',$data['type']);
            $where_vote['votetype'] = array('eq',$data['votetype']);
            $onevote = $WechatVote->field('number')->where($where_vote)->find();

            //投票
            $WechatVoteinfo = M('WechatVoteinfo');
            $where_info['openid'] = array('eq',session('openid'));
            $where_info['vid'] = array('eq',$data['vid']);
            $where_info['type'] = array('eq',$data['type']);
            $where_info['votetype'] = array('eq',$data['votetype']);
            
            /*
             *限制投票的次数
             *这里需要验证是单选还是多选
            */
            if($data['votetype'] == 2){
                //多选
                //获取当前用户是第几次投票的（主要判断多选的次数）
                $vote_info = $WechatVoteinfo->where($where_info)->order('create_time DESC')->find();
                if(!$vote_info){
                    $vote_info['votecount'] = 0;
                }
                $ex = $onevote['number'] - $vote_info['votecount'];
            } elseif($data['votetype'] == 1) {
                //单选
                $count = $WechatVoteinfo->where($where_info)->count();
                $ex = $onevote['number'] - $count;
            }
            if($ex <= 0){
                $this->error('投票次数已经用完！');
            }

            //添加
            if($WechatVoteinfo->addAll($dataList)){
                $this->success('投票成功！',U('index',array('id'=>$data['vid'],'type'=>$data['type'],false,true)));
            } else {
                $this->error('投票失败！');
            }

        } else {
            $this->id = I('get.id');
            $this->type = I('get.type');

            if(!is_numeric($this->id) || empty($this->id) || !is_numeric($this->type) || empty($this->type)){
                $this->error('参数错误！');
            }

            //获取用户的信息
            if(!session("?openid")){
                $userInfo = R('Wechat/auth2',array(U('',http_build_query(I('get.')),false,true)));
                session('openid',$userInfo['openid']);
                session('name',$userInfo['nickname']);
            }

            //获取投票信息
            $WechatVote = M('WechatVote');
            $data = $WechatVote->find($this->id);
            //处理图片解析
            if(!empty($data['thumb'])){
                $data['thumb'] = unserialize($data['thumb']);
            }
            $this->data = $data;

            //获取投票选项
            $WechatVoteoptions = D('WechatVoteoptions');
            $options_where['status'] = array('eq',1);
            $options_where['vid'] = array('eq',$this->id);
            $options_where['type'] = array('eq',$this->type);
            $options = $WechatVoteoptions->where($options_where)->relation(array('WechatVoteinfo'))->order('sort ASC,id DESC')->select();

            //处理图片解析
            if(!empty($options)){
                foreach ($options as $key => $value) {
                    if(!empty($value['thumb'])){
                        $options[$key]['thumb'] = unserialize($value['thumb']);
                    }
                }
            }
            $this->options = $options;

            //投票信息
            $WechatVoteinfo = M('WechatVoteinfo');
            
            //投票总人数，不是记录条数
            $where_info_count['vid'] = array('eq',$this->id);
            $where_info_count['type'] = array('eq',$this->type);
            $where_info_count['votetype'] = array('eq',$data['votetype']);
            $person_arr = $WechatVoteinfo->field('id,openid')->where($where_info_count)->group('openid')->select();
            $this->person_count = count($person_arr);

            //投票总数，用于处理百分比
            $this->vote_total_count = $WechatVoteinfo->field('id,openid')->where($where_info_count)->count();

        /*当前用户的投票信息*/
            $where_info['openid'] = array('eq',session('openid'));
            $where_info['vid'] = array('eq',$this->id);
            $where_info['type'] = array('eq',$this->type);
            $where_info['votetype'] = array('eq',$data['votetype']);

            //获取当前用户是第几次投票的（主要判断多选的次数）
            $vote_info = $WechatVoteinfo->where($where_info)->order('create_time DESC')->find();
            if(!$vote_info){
                $vote_info['votecount'] = 0;
            }
            $this->votecount = $vote_info['votecount'] + 1;

            //获取当前用户投票剩余次数
            if($data['votetype'] == 2){
                //多选
                $this->vote_left_number = $data['number'] - $vote_info['votecount'];
            } elseif($data['votetype'] == 1) {
                //单选
                $count = $WechatVoteinfo->where($where_info)->count();
                $this->vote_left_number = $data['number'] - $count;
            }
        /*end 当前用户的投票信息*/    

            $this->display();
        }
    }

    /**
     * [record 投票记录]
     * @return [type] [description]
     */
    public function record()
    {
        $this->id = I('get.id');
        $this->type = I('get.type');
        if(!is_numeric($this->id) || empty($this->id) || !is_numeric($this->type) || empty($this->type)){
            $this->error('参数错误！');
        }

        //获取投票信息
        $WechatVote = M('WechatVote');
        $data = $WechatVote->find($this->id);
        //处理图片解析
        if(!empty($data['thumb'])){
            $data['thumb'] = unserialize($data['thumb']);
        }
        $this->data = $data;

        //投票信息
        $WechatVoteinfo = D('WechatVoteinfo');
        
        //投票总人数，不是记录条数
        $where_info_count['vid'] = array('eq',$this->id);
        $where_info_count['type'] = array('eq',$this->type);
        $where_info_count['votetype'] = array('eq',$data['votetype']);
        $person_arr = $WechatVoteinfo->field('id,openid')->where($where_info_count)->group('openid')->select();
        $this->person_count = count($person_arr);

        //投票总数，用于处理百分比
        //$this->vote_total_count = $WechatVoteinfo->field('id,openid')->where($where_info_count)->count();

    /*当前用户的投票信息*/
        $where_info['openid'] = array('eq',session('openid'));
        $where_info['vid'] = array('eq',$this->id);
        $where_info['type'] = array('eq',$this->type);
        $where_info['votetype'] = array('eq',$data['votetype']);

        //获取当前用户是第几次投票的（主要判断多选的次数）
        $vote_info = $WechatVoteinfo->where($where_info)->order('create_time DESC')->find();
        if(!$vote_info){
            $vote_info['votecount'] = 0;
        }
        $this->votecount = $vote_info['votecount'] + 1;

        //获取当前用户投票剩余次数
        if($data['votetype'] == 2){
            //多选
            $this->vote_left_number = $data['number'] - $vote_info['votecount'];
        } elseif($data['votetype'] == 1) {
            //单选
            $count = $WechatVoteinfo->where($where_info)->count();
            $this->vote_left_number = $data['number'] - $count;
        }

        //获取投票记录信息
        $vote_list = $WechatVoteinfo->where($where_info)->relation(array('WechatVoteoptions'))->order('create_time DESC')->select();
        
        //处理图片解析
        if(!empty($vote_list)){
            foreach ($vote_list as $key => $value) {
                if(!empty($value['WechatVoteoptions'])){
                    if(!empty($value['WechatVoteoptions']['thumb'])){
                        $vote_list[$key]['WechatVoteoptions']['thumb'] = unserialize($value['WechatVoteoptions']['thumb']);
                    }
                }
            }
        }

        $this->vote_list = $vote_list;
    /*end 当前用户的投票信息*/    

        $this->display();
    }
}
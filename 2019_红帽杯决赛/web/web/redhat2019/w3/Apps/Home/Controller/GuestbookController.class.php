<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 留言建议 ]
*/
namespace Home\Controller;
class GuestbookController extends CommonController {

    public function index(){
        $this->seoData = array('title'=>'留言建议','keywords'=>'留言建议','description'=>'留言建议');
        $this->display();
    }

    /**
     * [guestbook 留言建议]
     * @return [type] [description]
     */
    public function guestbook()
    {
        if(IS_POST){

            //验证ip是否被限制
            $this->iplimit();

            //验证是否允许游客登陆
            if(C('ALLOW_USRE_GUESTBOOK')){
                //允许游客留言
                $uid = 0;
            } else {
                //不允许游客留言，则提示登陆
                if(session('?echouid')){
                    $uid = session('echouid');
                } else {
                    //跳转登陆
                    $return_data['status']  = 0;
                    $return_data['url'] = U('Members/login@'.$this->myurl,array('returnurl' => encode(U('index@'.$this->myurl,'',false,true))),false,true);
                    $return_data['info'] = L('_ALLOW_USER_GUESTBOOK_ERROR_');
                    $this->ajaxReturn($return_data);
                }
            }

            //验证标题敏感词汇
            $this->sensitivewords(I('post.title'),L('_GUESTBOOK_TITLE_SENSITIVE_'));
            $this->sensitivewords(I('post.content'),L('_GUESTBOOK_CONTENT_SENSITIVE_'));

            //验证码
            $code = I('post.code');
            if(isset($code) && !empty($code)){
                if(!check_verify($code)){
                    $this->error(L('_VERIFY_ERROR_'));
                }
            }

            //创建数据
            $guestbook = D('Guestbook');
            if($guestbook->create()){
                //验证是否存在数据
                $where['uid'] = array('eq',$uid);
                $where['siteid'] = array('eq',C('SITEID'));
                $where['create_time'] = array('egt',time() - C('GUESTBOOK_LIMIT_TIME'));
                if($one = $guestbook->where($where)->find()){
                    $this->error(L('_GUESTBOOK_EXISTS_',array('time' => (C('GUESTBOOK_LIMIT_TIME') + $one['create_time'] - time())."s")));
                }

                //uid
                $guestbook->uid = $uid;

                //ip
                $guestbook->ip = get_client_ip();

                //地区
                $ip = new \Org\Net\IpLocation('UTFWry.dat');
                $area = $ip->getlocation($guestbook->ip);
                $guestbook->area = serialize($area);

                //事件
                $guestbook->create_time = time();
                $guestbook->siteid = C('SITEID');

                if($inserid = $guestbook->add()){
                    $guestbook->where("id = ".$inserid."")->setField("sort",$inserid);
                    $this->success(L('_GUESTBOOK_SUCCESS_'));
                } else {
                    $this->error(L('_GUESTBOOK_ERROR_'));
                }
            } else {
                $this->error($guestbook->getError());
            }
        } else {
            $this->error(L('_ACCESS_ERROR_'));
        }
    }
    
}
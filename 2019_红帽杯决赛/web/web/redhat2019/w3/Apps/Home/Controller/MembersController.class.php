<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 用户中心 ]
*/
namespace Home\Controller;
class MembersController extends CommonController {

    /**
     * [_initialize 初始化函数]
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->cid = 'members';
        if(session('?echouid')){
            $userinfo = D('UcMembers')->find(session('echouid'));
            if(!empty($userinfo['thumb'])){
                $userinfo['thumb'] = unserialize($userinfo['thumb']);
            }
            if(!empty($userinfo['area'])){
                $userinfo['area'] = unserialize($userinfo['area']);
            }
            //查询当前member信息
            $Member = M('Member');
            $where['uid'] = array('eq',session('echouid'));
            $memberinfo = $Member->where($where)->find();
            if(!empty($memberinfo['thumb'])){
                $userinfo['memberthumb'] = unserialize($memberinfo['thumb']);
            }
            $this->userinfo = $userinfo;
        }
    }

    //用户首页
    public function index(){
        $this->display();
    }

    //修改资料
    public function profile()
    {
        $this->isnologin();
        if(IS_POST){
            $Members = D('UcMembers');
            if($data = $Members->create()){

                //key重新索引解决冲突
                /*$Members->thumb = array_values($Members->thumb); 
                $Members->thumb = serialize($Members->thumb);*/

                //密码不填时不进行验证
                if(!empty($data['password'])){
                    $rules = array(
                        array('password','require','密码必须填写！',0,'regex',2), //密码更新时验证
                        array('password','6,20','密码长度必须是6到20位！',0,'length',2),//密码更新时验证
                        array('checkpassword','password',"两次输入的密码确不一致！",0,'confirm',2) //密码更新时验证
                    );

                    //验证
                    if(!$Members->validate($rules)->create()){
                        $this->error($Members->getError());
                    }

                    //设置密码
                    $Members->password =  sha1(md5($Members->password));
                } else {
                    //删除对象里的密码
                    unset($Members->password);
                }

                //生日
                if(!empty($Members->birthday)){
                    $Members->birthday = strtotime($Members->birthday);
                }

                //保存数据
                if($Members->save()){
                    $this->success('资料修改成功！');
                } else {
                    $this->error('资料修改失败！');
                }
            } else {
                $this->error($Members->getError());
            }
        } else {
            $this->display();
        }
    }

    /**
     * [uploadOne 上传单个文件]
     * @return [type] [description]
     */
    public function uploadOne()
    {
        $this->isnologin();
        if(IS_POST){
            $type = I('post.type','images');
            $saveDir = I('post.savedir','images');
            $thumbw = I('post.thumbw',200);
            $thumbh = I('post.thumbh',200);
            $filesize = I('post.filesize',3072);
            $fileinfo = uploadOne($type,$saveDir,$filesize,$thumbw,$thumbh);
            if($fileinfo['status'] == 1){
                //成功，修改thumb信息
                $thumb[0] = $fileinfo;
                $thumb[0]['type'] = $fileinfo['oringinal_type'];
                $thumb = array_values($thumb);
                $thumb = serialize($thumb);
                
                //当前站点模型
                $member = M('Member');

                //验证当前子站点是否有此用户,若无此用户信息则新增
                $check['uid'] = array('eq',session('echouid'));
                $check['siteid'] = array('eq',C('SITEID'));
                if(!$memberinfo = $member->where($check)->find()){

                    //用户uid
                    $member_data['uid'] = session('echouid');

                    //站点id
                    $member_data['siteid'] = C('SITEID');

                    //创建时间
                    $member_data['create_time'] = time();

                    //排序
                    $member_data['sort'] = session('echouid');

                    $member->data($member_data)->add();

                    //重新获取数据
                    $memberinfo = $member->where($check)->find();
                }

                //解析thumb
                if(!empty($memberinfo['thumb'])){
                    $pictures_files_arr[] = unserialize($memberinfo['thumb']);
                }

                $saveData['thumb'] = $thumb;
                if($member->where($check)->save($saveData)){
                    //删除图片
                    delfilefun($pictures_files_arr);

                    $returnData['status'] = 1;
                    $returnData['info'] = '头像上传成功！';
                    $this->ajaxReturn($returnData);
                } else {
                    $returnData['status'] = 0;
                    $returnData['info'] = '保存头像数据失败！';
                    $this->ajaxReturn($returnData);
                }
            } else {
                //失败，提示信息
                $returnData = $fileinfo;
                $this->ajaxReturn($returnData);
            }
        }
    }

    //验证未登陆
    private function isnologin()
    {
        if(!session('?echouid')){
            $this->redirect('index');
        }
    }

    //验证登陆
    private function islogin()
    {
        if(session('?echouid')){
            $this->redirect('index');
        }
    }

    //用户退出
    public function loginout()
    {
        if(IS_POST){
            $one = D('UcMembers')->find(session('echouid'));
            //同步退出
            if(session('?echouid')){
                session('echouid',null);
            }
            $this->success('退出成功！'.$this->syslogin($one['uid'],'loginout'),U('index'));
        } else {
            $this->error('非法操作！');
        }
    }

    //用户登陆
    public function login()
    {
        $this->islogin();
        if(IS_POST){
            
            //验证ip是否被限制
            $this->iplimit();

            //获取数据
            $username = I('post.username');
            $password = I('post.password');

            //用户中心模型
            $ucmembers = D('UcMembers');
            $where['username'] = array('eq',$username);
            $where['email'] = array('eq',$username);
            $where['mobile'] = array('eq',$username);
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
            $map['password'] = array('eq',sha1(md5($password)));
            $map['type'] = array('eq','system');

            //验证是否存在
            if($ucuserinfo = $ucmembers->where($map)->find()){

                //验证用户中心用户状态
                if(!$ucuserinfo['status']){
                    $this->error('用户已经被禁用！');
                }

                //验证用户中心用户锁定
                if($ucuserinfo['lock']){
                    $this->error('用户已经被锁定！');
                }

                //Ucenter验证成功后，当前站点的用户表插入相应数据，并且登陆
                $this->snslogin_member($ucuserinfo['uid'], $ucmembers);

            } else {
                $this->error('用户名与密码不匹配！');
            }
        } else {

            //获取cookie
            $login_return_url = cookie('login_return_url');

            //登陆跳转地址
            $returnurl = I('get.returnurl','');
            if(empty($login_return_url)){
                if(empty($returnurl)){
                    $returnurl = encode(U('index@'.$this->myurl,'',true,true));
                }
            } else {
                $returnurl = $login_return_url;
            }
            
            $this->returnurl = $returnurl;
            cookie('login_return_url',null);
            cookie('login_return_url',$this->returnurl);

            //判断是否是第三方登录
            $login_type = I('get.type','');
            if(!empty($login_type)){

                //设置小写
                $login_type = strtolower($login_type);

                //验证ip是否被限制
                $this->iplimit();

                //验证当前第三方登录是否开启
                $login_interface = C("THINK_SDK_" . strtoupper($login_type));
                if(!$login_interface['APP_STATUS']){
                    $this->error("{$login_interface['APP_NAME']}已经关闭！");
                }

                //加载ThinkOauth类并实例化一个对象
                import('Api.ThinkSDK.ThinkOauth');

                //实例化一个对象
                $sns = \ThinkOauth::getInstance($login_type);

                //跳转到授权页面
                redirect($sns->getRequestCodeURL());

            } else {
                $this->display();
            }
        }
    }

    /**
     * [callback 登陆回调函数]
     * @return function [description]
     */
    public function callback(){
        
        $type = I('get.type','');
        $code = I('get.code','');
        if(empty($type) || empty($code)){
            $this->error('参数错误');
        }
        
        //加载ThinkOauth类并实例化一个对象
        import('Api.ThinkSDK.ThinkOauth');

        //实例化一个对象
        $sns = \ThinkOauth::getInstance($type);

        //腾讯微博需传递的额外参数
        $extend = null;
        if($type == 'tencent'){
            $extend = array('openid' => I('get.openid'), 'openkey' => I('get.openkey'));
        }

        //请妥善保管这里获取到的Token信息，方便以后API调用
        //调用方法，实例化SDK对象的时候直接作为构造函数的第二个参数传入，如： $qq = ThinkOauth::getInstance('qq', $token);
        $token = $sns->getAccessToken($code , $extend);

        //获取当前登录用户信息
        if(is_array($token)){
            //加载事件
            $event = new \Api\ThinkSDK\TypeEvent();
            $user_info = $event->$type($token);
            $user_info['type'] = strtolower($user_info['type']);

            //去ucenter中验证是否存在，如果不存在则注册用户，如果存在则更新信息
            $ucmembers = D('UcMembers');
            $where['openid'] = array('eq',$token['openid']);
            $where['type'] = array('eq',$user_info['type']);

            //事务处理
            $ucmembers->startTrans();

            //获取ucenter数据
            if($ucuserinfo = $ucmembers->where($where)->find()){

                //存在则更新数据
                
                //验证用户中心用户状态
                if(!$ucuserinfo['status']){
                    $this->error('用户已经被禁用！');
                }

                //验证用户中心用户锁定
                if($ucuserinfo['lock']){
                    $this->error('用户已经被锁定！');
                }

                //验证当前站点用户状态
                //当前站点模型
                $member = M('Member');

                //验证当前子站点是否有此用户,若无此用户信息则新增
                $check['uid'] = array('eq',$ucuserinfo['uid']);
                $check['siteid'] = array('eq',C('SITEID'));

                //获取数据
                $member_user = $member->where($check)->find();

                //验证当前站点用户状态
                if(!$member_user['status']){
                    $this->error('用户已经被禁用！');
                }

                //验证当前站点用户锁定
                if($member_user['lock']){
                    $this->error('用户已经被锁定！');
                }

                //Ucenter验证成功后，当前站点的用户表插入相应数据，并且登陆
                $this->snslogin_member($ucuserinfo['uid'], $ucmembers);

            } else {

                //不存在则新增数据

                //用户信息
                $add_ucuser = $user_info;

                
                //设置头像
                $add_ucuser['thumb'][] = array(
                    'name'=>$add_ucuser['type'],
                    'sort'=>1,
                    'thumb'=>$add_ucuser['head'],
                    'photo'=>$add_ucuser['head'],
                    'type'=>'images',
                    'oringinal_type'=>'images',
                    'location'=>'remote'
                );

                //删除头像
                unset($add_ucuser['head']);
                $add_ucuser['thumb'] = serialize($add_ucuser['thumb']);

                //openid
                $add_ucuser['openid'] =  $token['openid'];

                //token信息
                $add_ucuser['tokeninfo'] = serialize($token);

                //注册ip
                $add_ucuser['regip'] = get_client_ip();

                //注册地区
                $ip = new \Org\Net\IpLocation('UTFWry.dat');
                $area = $ip->getlocation($add_ucuser['regip']);
                $add_ucuser['area'] = serialize($area);

                //注册时间
                $add_ucuser['regdate'] = time();

                if($insertid = $ucmembers->data($add_ucuser)->add()){
                    //更新排序
                    $ucmembers->where("uid = {$insertid}")->setField('sort',$insertid);

                    //Ucenter注册成功后，当前站点的用户表插入相应数据，并且登陆
                    $this->snslogin_member($insertid, $ucmembers);
                }
            }
        }
    }

    /**
     * [snslogin_member 第三方登录处理登陆]
     * @param  [type] $uid       [用户id]
     * @param  [type] $ucmembers [ucenter模型]
     * @return [type]            [description]
     */
    private function snslogin_member($uid, $ucmembers = null){
        if(!is_numeric($uid) || empty($uid)){
            $this->error('参数错误！');
        }

        //当前站点模型
        $member = M('Member');

        //验证当前子站点是否有此用户,若无此用户信息则新增
        $check['uid'] = array('eq',$uid);
        $check['siteid'] = array('eq',C('SITEID'));
        if(!$member_user = $member->where($check)->find()){

            //用户uid
            $member_data['uid'] = $uid;

            //站点id
            $member_data['siteid'] = C('SITEID');

            //创建时间
            $member_data['create_time'] = time();

            //排序
            $member_data['sort'] = $uid;

            $member->data($member_data)->add();
        }

        //重新获取数据
        $member_user = $member->where($check)->find();

        //验证当前站点用户状态
        if(!$member_user['status']){
            $this->error('用户已经被禁用！');
        }

        //验证当前站点用户锁定
        if($member_user['lock']){
            $this->error('用户已经被锁定！');
        }

        //登陆成功，设置session，更新站点数据
        $member_login_data['uid'] = $uid;

        //登陆次数
        $member_login_data['login_count'] = array('exp','login_count+1');

        //最后登陆ip
        $member_login_data['last_login_ip'] = get_client_ip();

        //最后登陆时间
        $member_login_data['last_login_time'] = time();

        //最后登陆地区信息
        $ip = new \Org\Net\IpLocation('UTFWry.dat');
        $member_login_area = $ip->getlocation($member_login_data['last_login_ip']);
        $member_login_data['last_login_area'] = serialize($member_login_area);

        if($member->data($member_login_data)->save()){

            //提交事务
            $ucmembers->commit();

            //设置session
            session('echouid',$uid);

            //记录行为日志
            action_log(2,$uid,'member_login','Member',$uid,C('BASE_LOG'),C("SITEID"));

            //用户中心记录行为日志
            $action_log_data['user_type'] = 2;//行为类型1-系统,2-会员与行为规则表的对应
            $action_log_data['user_id'] = $uid; //用户id
            $action_log_data['action'] = 'member_login'; //行为标识
            $action_log_data['model'] = 'Members'; //行为操作表
            $action_log_data['record_id'] = $uid; //行为记录id
            $action_log_data['siteid'] = C('SITEID'); //行为记录id
            $action_log_data['ip'] = get_client_ip(); //执行ip
            $ucenter_url = parse_url(C('UCENTER_DOMAIN'));
            $ucenter_host = $ucenter_url['host'] ? $ucenter_url['host'] : '';
            $ucenter_log_returndata = gethttp(U("Actionlog/index@{$ucenter_host}",'',true,true), array(), $action_log_data, 'POST');

            //设置跳转地址
            $redirect_url = decode(cookie('login_return_url'));
            cookie('login_return_url',null);

            //同步登陆
            $this->success('恭喜你，登陆成功！'.$this->syslogin($uid,'login'),$redirect_url);

        } else {

            //回滚事务
            $ucmembers->rollback();

            $this->error('登陆失败！');
        }
    }


    /**
     * [register 用户注册]
     * @return [type] [description]
     */
    public function register()
    {
        $this->islogin();
        if(IS_POST){
            //验证ip是否被限制
            $this->iplimit();

            //验证是否开启注册
            if(!C('USER_REGISTER')){
                $this->error(L('系统已经关闭注册！'));
            }

            $ucmembers = D('UcMembers');

            //事务处理
            $ucmembers->startTrans();

            if($data = $ucmembers->create()){
                
                //验证是否存在这个用户
                $where['username|email|mobile'] = array($ucmembers->username,$ucmembers->email,$ucmembers->mobile,'_multi'=>true);
                $where['type'] = array('eq','system');
                if($ucmembers->where($where)->find()){
                    $this->error('已经存在此用户！');
                }

                //注册ip
                $ucmembers->regip = get_client_ip();

                //注册地区
                $ip = new \Org\Net\IpLocation('UTFWry.dat');
                $area = $ip->getlocation($add_ucuser['regip']);
                $ucmembers->area = serialize($area);

                //注册时间
                $ucmembers->regdate = time();

                //账号密码
                $ucmembers->password = sha1(md5($ucmembers->password));

                if($insertid = $ucmembers->add()){
                    $ucmembers->where("uid = {$insertid}")->setField('sort',$insertid);

                    //Ucenter注册成功后，当前站点的用户表插入相应数据
                    $member = M('Member');

                    //用户uid
                    $member_data['uid'] = $insertid;

                    //站点id
                    $member_data['siteid'] = C('SITEID');

                    //创建时间
                    $member_data['create_time'] = time();

                    //排序
                    $member_data['sort'] = $insertid;

                    //新增数据
                    if($member->data($member_data)->add()){

                        //提交事务
                        $ucmembers->commit();

                        //注册成功跳转
                        $this->success('恭喜你，注册成功!',U('login'));

                    } else {

                        //事务回滚
                        $ucmembers->rollback();

                        //注册失败
                        $this->error('注册失败！');

                    }

                } else {

                    //注册失败
                    $this->error('注册失败！');

                }

            } else {
                $this->error($ucmembers->getError());
            }
        } else {
            $this->display();
        }
    }

    /**
     * [forget 忘记密码]
     * @return [type] [description]
     */
    public function forget()
    {
        $this->islogin();
        if(IS_POST){
            //验证ip是否被限制
            $this->iplimit();

            //获取数据
            $data = I('post.');

            //输入用户名
            if($data['type'] == 'username'){
                //验证码
                if(isset($data['code']) && !empty($data['code'])){
                    if(!check_verify($data['code'])){
                        $this->error(L('_VERIFY_ERROR_'));
                    }
                }

                //验证是否存在用户
                $ucmembers = D('UcMembers');
                $where['username'] = array('eq',$data['username']);
                $where['email'] = array('eq',$data['username']);
                $where['mobile'] = array('eq',$data['username']);
                $where['_logic'] = 'or';
                $map['_complex'] = $where;
                $map['type'] = array('eq','system');

                if(!$ucone = $ucmembers->where($map)->find()){
                    $this->error(L('_USER_NOTEXISTS_'));
                }

                //输入用户名完成
                $this->success('输入用户名完成，进行验证身份！',U('forget',array('type'=>'check','user'=>encode($data['username']))));

            //验证身份
            } elseif($data['type'] == 'check') {

                //判断找回密码类型
                if(md5($data['code']) == session('forgetpasswodcode')){
                    $this->success('验证身份完成，进行重置密码！',U('forget',array('type'=>'password','uid'=>$data['uid'])));
                } else {
                    $this->error('验证码错误，请重新获取！');
                }

            //重置密码    
            } elseif($data['type'] == 'password') {
                
                //验证是否已经获取验证码
                if(!session('?forgetpasswodcode')){
                    $this->error(L('_ACCESS_ERROR_'));
                }

                //验证密码是否一致
                if(sha1(md5($data['password'])) != sha1(md5($data['checkpassword']))){
                    $this->error('两次输入的密码确不一致！');
                }

                //修改密码
                $ucmembers = D('UcMembers');
                if(!empty($data['uid'])){
                    $data['uid'] = decode($data['uid']);
                }
                $where['uid'] = array('eq',$data['uid']);
                $where['type'] = array('eq','system');

                //验证是否存在用户
                if(!$this->userinfo = $ucmembers->field('uid,type,username,email,mobile')->where($where)->find()){
                    $this->error(L('_ACCESS_ERROR_'));
                }

                //修改密码
                //先将原始密码重置为空
                $ucmembers->where($where)->setField("password",'');
                if($ucmembers->where($where)->setField("password",sha1(md5($data['password'])))){
                    $this->success('密码重置完成，跳转到完成！',U('forget',array('type'=>'finish')));
                } else {
                    $this->success('密码重置失败！');
                }
            }

        } else {

            //设置默认的类型
            $this->type = I('get.type','username');
            $arr = array('username','check','password','finish');
            if(!in_array($this->type,$arr)){
                $this->type = 'username';
            }

            //验证身份
            if($this->type == 'check'){
                //验证是否存在用户
                $ucmembers = D('UcMembers');
                $this->user = I('get.user','');
                if(!empty($this->user)){
                    $this->user = decode($this->user);
                }
                $where['username'] = array('eq',$this->user);
                $where['email'] = array('eq',$this->user);
                $where['mobile'] = array('eq',$this->user);
                $where['_logic'] = 'or';
                $map['_complex'] = $where;
                $map['type'] = array('eq','system');
                if(!$this->userinfo = $ucmembers->field('uid,type,username,email,mobile')->where($map)->find()){
                    $this->error(L('_ACCESS_ERROR_'));
                }
            }

            //重置密码
            if($this->type == 'password'){
                //验证是否已经获取验证码
                if(!session('?forgetpasswodcode')){
                    $this->error(L('_ACCESS_ERROR_'));
                }

                //验证是否存在用户
                $ucmembers = D('UcMembers');
                $this->uid = I('get.uid','');
                if(!empty($this->uid)){
                    $this->uid = decode($this->uid);
                }
                $map['uid'] = array('eq',$this->uid);
                $map['type'] = array('eq','system');
                if(!$this->userinfo = $ucmembers->field('uid,type,username,email,mobile')->where($map)->find()){
                    $this->error(L('_ACCESS_ERROR_'));
                }
            }

            //完成
            if($this->type == 'finish'){
                //验证是否已经获取验证码
                if(session('?forgetpasswodcode')){
                    session('forgetpasswodcode',null);
                }
            }

            //获取
            $this->display();
        }
    }

    /**
     * [getcode 获取邮件短信验证码]
     * @return [type] [description]
     */
    public function getcode(){
        if(IS_POST){
            //获取数据
            $data  = I('post.');

            //验证用户
            $ucmembers = D('UcMembers');
            if(!empty($data['uid'])){
                $data['uid'] = decode($data['uid']);
            }
            $map['uid'] = array('eq',$data['uid']);
            $map['type'] = array('eq','system');
            if(!$userinfo = $ucmembers->field('uid,type,username,email,mobile')->where($map)->find()){
                $this->error(L('_ACCESS_ERROR_'));
            }

            //判断找回密码类型
            if($data['checktype'] == 'email'){
                //发送邮件接口
                
                //验证是否开启邮件
                if(!C('MAIL_STATUS')){
                    $this->error('邮件功能已经关闭！');
                }

                //邮件标题
                $forget_title = '找回密码 - '.C('BASE_WEBNAME');

                //生成随机码
                $code = randcode(6);
                $forget_msg = "您好：{$userinfo['username']}，您通过邮件在本站找回密码，您的验证码是：{$code}，请及时更改密码！";

                //发送邮件
                $ok = SendMail($userinfo['email'],$forget_title,$forget_msg);

                if($ok){
                    //保存验证码session
                    session('forgetpasswodcode',md5($code));

                    $this->success('邮件发送成功，请注意查收！');
                } else {
                    $this->error('邮件发送失败！');
                }

            } elseif($data['checktype'] == 'mobile') {
                //短息接口

                //加载message短信配置文件
                $message_config = require(CONF_PATH . 'message.php');

                //验证短信接口是否开启
                if(!$message_config['MESSAGE_STATUS']){
                    $this->error('短信功能已经关闭！');
                }

                //调用短信接口
                $message = new \Api\Message\Message($userinfo['mobile'],$message_config);

                //提交
                $data = $message->send();

                //设置返回数据
                if($data['code'] == 2){

                    //将信息写入数据表中
                    $message_data['mobile'] = $data['mobile'];
                    $message_data['code'] = $data['mobile_code'];
                    $message_data['content'] = $data['content'];
                    $message_data['create_time'] = time();
                    $message_data['status'] = 1;
                    M('Message')->add($message_data);

                    //保存验证码session
                    session('forgetpasswodcode',md5($data['mobile_code']));

                    $this->success('短信验证码发送成功，请注意查收！');

                } else {
                     $this->error('短信验证码发送失败！');
                }
            }

        } else {
            $this->error(L('_ACCESS_ERROR_'));
        }
    }


    /**
     * [syslogin 同步登录登出处理函数]
     * @param  [type] $uid  [description]
     * @param  string $type [description]
     * @return [type]       [description]
     */
    private function syslogin($uid,$type = 'login')
    {
        //同步登陆
        $where_syslogin['synlogin'] = array('eq',1);
        $sites = M(C('UCENTER_DB_TABLE_APPLICATIONS'),C('UCENTER_DB_PREFIX'),C('UCENTER_DB_DSN'))->where($where_syslogin)->select();
        $str = '';
        if(!empty($sites)){
            foreach ($sites as $key => $value) {
                $url = parse_url($value['url']);
                if(isset($url['query'])){
                    $str .= '<script type="text/javascript" src="'.$value['url'].'&type='.encode($type).'&data='.encode($uid).'"></script>';
                } else {
                    $str .= '<script type="text/javascript" src="'.$value['url'].'?type='.encode($type).'&data='.encode($uid).'"></script>';
                }
            }
        }
        return $str;
    }

    /**
     * [setSession 设置session函数]
     */
    public function setSession()
    {
        if(IS_POST){
            //验证通信
            define("AUTHKEY","YcWxJpjhMpTIzNDU2NzgO0O0O");
            $authkey = I('post.authkey');
            if(encode($authkey) == AUTHKEY){
                $data =  1;
            } else {
                $data =   0;
            }
            $this->ajaxReturn($data);
        } else {
            $type = decode(I('get.type'));
            $uid = decode(I('get.data'));
            if(!is_numeric($uid) || empty($type)){
                exit('非法操作！');
            }

            //判断是否存在当前数据
            $members = M(C('UCENTER_DB_TABLE_MEMBERS'),C('UCENTER_DB_PREFIX'),C('UCENTER_DB_DSN'));
            if(!$one = $members->find($uid)){
                exit('不存在此用户！');
            }

            //验证类型
            if($type == 'login'){
                if(!session("?echouid")){
                    session('echouid',$uid);
                }
            } else if($type == 'loginout'){
                if(session("?echouid")){
                    session('echouid',null);
                }
            }
        }
    }

    /**
     * [checkLogin 验证是否登陆]
     * @return [type] [description]
     */
    public function checkLogin()
    {
        if(IS_POST){
            //验证ip是否被限制
            $this->iplimit();

            //不允许游客，则提示登陆
            if(!session('?echouid')){
                //请去登陆
                $return_data['status']  = -1;
                $return_data['info'] = '用户未登录请去登陆！';

                //跳转地址
                $returnurl = I('post.url');
                cookie('login_return_url',$returnurl);

                $this->ajaxReturn($return_data);
            } else {
                //请去登陆
                $return_data['status']  = 1;
                $return_data['info'] = '用户已经登陆！';
                $this->ajaxReturn($return_data);
            }
        } else {
            $this->error(L('_ACCESS_ERROR_'));
        }
    }
}
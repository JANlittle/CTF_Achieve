<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 后台登陆类 ]
*/
namespace Kgmanage\Controller;
use Think\Controller;


class LoginController extends Controller
{
	/**
	 * [_initialize 初始化]
	 */
	public function _initialize()
	{
		//获取配置
		$system_config = S('ALL_CONFIG0');
		//空的情况下生成缓存
		if(empty($system_config)){
			//生成缓存，设置默认
			setConfig('ALL_CONFIG0',array(0));
			$system_config = S('ALL_CONFIG0');
		}
		//缓存设置成配置
		C($system_config);
	}
	/**
	 * [index 登录页]
	 * @return [void]
	*/
	public function index()
	{
		if(session('?uid')){
			$this->redirect("Index/index");
		}

		$this->display('login');
	}

	/**
	 * [login 验证登录]
	 * @return [void]
	 */
	public function login()
	{
		if(IS_POST){
			//限制后台IP登录
			$ips = explode('|',C('SYSTEM_IPLIMIT'));
			$client_ip = get_client_ip();
			if(in_array($client_ip,$ips)){
				$this->error(L('_IP_LIMIT_'));
			}

			//验证验证码
			$where['username'] = array('eq',I('post.username'));
			$where['password'] = array('eq',sha1(md5(I('post.password'))));
			$verify = I('post.verify');
			if(!check_verify($verify)){
				$this->error(L('_VERIFY_ERROR_'));
			}

			//获取数据
			$admin = M('Adminuser');
			$adminuser = $admin->where($where)->find();
			//验证是否存在数据
			if(!$adminuser){
				$this->error(L('_USERPWD_ERROR_'));
			}

			//验证是否锁定
			if(!$adminuser['status']){
				$this->error(L('_USERLOCK_'));
			}

			//更新用户数据
			$update['id'] = $adminuser['id'];
			$update['last_login_time'] = time();
			$update['last_login_ip'] = get_client_ip();
			$update['login_count'] = array('exp',"login_count+1");
			if($admin->save($update)){
				//设置session
				session('uid',$adminuser['id']);
				session('username',$adminuser['username']);
				session('udev',$adminuser['dev']);

				//设置用户的
				session('usiteid',$adminuser['siteid']);

				//设置默认站点
				session('siteid',$adminuser['siteid']);

				//执行登陆日志
				action_log(1,$adminuser['id'],'adminuser_login','Adminuser',$adminuser['id'],C('SYSTEM_LOG'),$adminuser['siteid']);
				$this->success(L('_LOGIN_SUCCESS_'),U('Index/index'));
			} else {
				$this->error(L('_UPDATEUSERDATE_ERROR_'));
			}
		} else {
			$this->redirect("index");
		}
	}


	/**
	 * [loginout 用户退出]
	 * @return [type] [description]
	 */
	public function loginout()
	{
		session('uid',null);
		session('username',null);
		session('usiteid',null);
		session('siteid',null);
		$this->redirect("index");
	}

	/**
	 * [verify 验证码]
	 * @return [void]
	 */
	public function verify()
	{
		$verify = new \Think\Verify();
		$verify->imageW = 283;
		$verify->fontttf = '4.ttf'; 
		$verify->entry();
	}
}
?>
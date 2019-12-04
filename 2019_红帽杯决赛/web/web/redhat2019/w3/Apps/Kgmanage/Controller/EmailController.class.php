<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 邮件配置 ]
*/
namespace Kgmanage\Controller;

class EmailController extends CommonController
{
	/**
	 * [index 邮件]
	 */
	public function index(){
		if(IS_POST){
	        $email = D('Email');
	        if($data = $email->create()){
	            if($email->save()){
			        //在本地写入邮件配置文件
			        $content = "<?php\n";
			        $content .= "/*\n";
			        $content .= "*Author:lianlincheng\n";
			        $content .= "*Date:".date("Y-m-d H:i:s")."\n";
			        $content .= "*/\n";
			        $content .= "return array(\n";
			        $content .= "\t'MAIL_ADDRESS'=>'".$data['email']."', // 邮箱地址\n";
			        $content .= "\t'MAIL_SMTP'=>'".$data['smtp']."', // 邮箱SMTP服务器\n";
			        $content .= "\t'MAIL_LOGINNAME'=>'".$data['accout']."', // 邮箱登录帐号\n";
			        $content .= "\t'MAIL_PASSWORD'=>'".$data['password']."', // 邮箱密码\n";
			        $content .= "\t'MAIL_PORT'=>'".$data['port']."', // smtp端口号\n";
			        $content .= "\t'MAIL_FROMUSERNAME'=>'".$data['fromusername']."', // 发件人\n";
			        $content .= "\t'MAIL_MOBAN'=>'".$data['moban']."', // 邮件配置模板\n";
			        $content .= "\t'MAIL_STATUS'=>".$data['status']." // 邮件配置状态\n";
			        $content .= ");\n";
			        $content .= "?>";
			        $ok = file_put_contents(CONF_PATH.'email.php', $content);
			        if($ok){
			        	$this->success(L('_SAVE_SUCCESS_'));
			        } else {
			        	$this->error(L('_SAVE_ERROR_'));
			        }
	            } else {
	                  $this->error(L('_SAVE_ERROR_'));
	            }
	        } else {
	            $this->error($email->getError());
	        }
	    } else {
	        //获取数据
	        $email = M('Email');
	        $this->email = $email->find();
	        $this->display();
	    }
	}

	/**
	 * [emailtest 邮件测试]
	 */
	public function emailtest()
	{
		if(IS_POST){
			$testemail = I('post.ceshi');
			if(empty($testemail)){
				$this->error('请输入正确的Email');
			}
			//加载email配置文件
    		$email = require(CONF_PATH . 'email.php');
    		C($email); //设置配置邮件

    		//验证是否开启邮件
    		if(!C('MAIL_STATUS')){
    			$this->error('邮件功能已经关闭！');
    		}

    		//发送邮件
			$ok = SendMail($testemail,'这是一封测试邮件！',C('MAIL_MOBAN'));
			if($ok){
				$this->success('邮件发送成功！');
			} else {
				$this->error('邮件发送失败！');
			}
		} else {
			$this->error(L('_ACESS_ERROR_'));
		}
	}
}
?>
<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 短信接口 ]
*/
namespace Kgmanage\Controller;

class MessageController extends CommonController
{
	/**
	 * [index 列表]
	 */
	public function index()
	{
		/*搜索条件*/
		$searchData = I('get.');
		$condition = array();
		if(!empty($searchData)){
			//mobile
			isset($searchData['mobile']) ? $condition['mobile'] = array('like',"%".$searchData['mobile']."%") : '';
			//status
			isset($searchData['status']) ? $condition['status'] = array('eq',$searchData['status']) : '';

			//create_time
			isset($searchData['starttime']) ? $starttime = array('egt',$searchData['starttime']) : $starttime = '';
			isset($searchData['endtime']) ? $endtime = array('elt',$searchData['endtime']) : $endtime = '';
			if(!empty($starttime) || !empty($endtime)){
				$create_time = array();
				if(!empty($starttime)){
					$create_time[] = $starttime;
				}
				if(!empty($endtime)){
					$create_time[] = $endtime;
				}
				$condition['create_time'] = $create_time;
			}
		}
		//设置where
		if(!empty($condition)){
			$where = $condition;
		} else {
			$where = array();
		}
	/*end 搜索条件*/
		$Message = M('Message');
		$count = $Message->field('id')->where($where)->count();

		//获取分页
		$page = getPage($count);
		$this->pagelist = $page->show();
		$this->data = $Message->field('id,code,status,mobile,content,create_time')->where($where)->order('id DESC')->limit($page->firstRow,$page->listRows)->select();

	/*获取url参数*/
		$this->parameter = getParameter(I('get.'),$page);
	/* end 获取url参数*/

		$this->display();
	}

	/**
	 * [search 搜索]
	 */
	public function search()
	{
		if(IS_POST){
			$data = I('post.');
			//处理时间
			if(!empty($data['starttime'])){
				$data['starttime'] = strtotime($data['starttime']);
			}
			if(!empty($data['endtime'])){
				$data['endtime'] = strtotime($data['endtime']);
			}
			//加载自定义函数库
			load('myfunction',APP_PATH.'Common/Common');
			$data = clearEmptyData($data);

			//直接跳转
			$this->success(L('_SEARCHING_'),U("index",$data));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}


	/**
	 * [del 删除]
	 */
	public function del()
	{
		if(IS_POST){
			//验证数据
			$id = I('post.id');
			$ids = explode(",", $id);

			//没有数据
			if(empty($ids)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//验证数据
			$Message = M('Message');
			foreach ($ids as $key => $value) {
				$data = $Message->field('id')->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//删除数据
			if($Message->delete($id)){
				$this->success(L('_DEL_SUCCESS_'));
			} else {
				$this->error(L('_DEL_ERROR_'));
			}
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [setMessage 设置短信]
	 */
	function setmessage(){
		if(IS_POST){
	        $MessageSet = D('MessageSet');
	        if($data = $MessageSet->create()){
	            if($MessageSet->save()){
			        //在本地写入邮件配置文件
			        $content = "<?php\n";
			        $content .= "/*\n";
			        $content .= "*Author:lianlincheng\n";
			        $content .= "*Date:".date("Y-m-d H:i:s")."\n";
			        $content .= "*/\n";
			        $content .= "return array(\n";
			        $content .= "\t'MESSAGE_ACCOUT'=>'".$data['accout']."', // 账号\n";
			        $content .= "\t'MESSAGE_PASSWORD'=>'".$data['password']."', // 密码\n";
			        $content .= "\t'MESSAGE_CODELENTH'=>".$data['codelength'].", // 验证码长度\n";
			        $content .= "\t'MESSAGE_TYPE'=>".$data['type'].", // 类型\n";
			        $content .= "\t'MESSAGE_APPLYNAME'=>'".$data['applyname']."', // 提供商\n";
			        $content .= "\t'MESSAGE_APPLYURL'=>'".$data['applyurl']."', // 提供商url\n";
			        $content .= "\t'MESSAGE_CONTENT'=>'".$data['content']."', // 短信模板\n";
			        $content .= "\t'MESSAGE_STATUS'=>".$data['status'].", // 状态\n";
			        $content .= ");\n";
			        $content .= "?>";
			        $ok = file_put_contents(CONF_PATH.'message.php', $content);
			        if($ok){
			        	$this->success(L('_SAVE_SUCCESS_'));
			        } else {
			        	$this->error(L('_SAVE_ERROR_'));
			        }
	            } else {
	                  $this->error(L('_SAVE_ERROR_'));
	            }
	        } else {
	            $this->error($MessageSet->getError());
	        }
	    } else {
	        //获取参数
			$this->parameter = I('get.parameter');
			
	        //获取数据
	        $MessageSet = M('MessageSet');
	        $this->data = $MessageSet->find();
	        $this->display();
	    }
	}


	/**
	 * [messagetest 测试]
	 */
	public function messagetest()
	{
		if(IS_POST){

			//获取手机
			$tel = I('post.ceshi');
			if(empty($tel)){
				$this->error('请输入正确的手机号码！');
			}

			//加载message短信配置文件
    		$message_config = require(CONF_PATH . 'message.php');

    		//验证短信接口是否开启
    		if(!$message_config['MESSAGE_STATUS']){
    			$this->error('短信功能已经关闭！');
    		}

			//调用短信接口
			$message = new \Api\Message\Message($tel,$message_config);

			//提交
			$data = $message->send();
			/*if($data['code'] == 2){
				session('mobile',$data['mobile']);
				session('mobile_code',$data['mobile_code']);
			}*/

			//设置返回数据
			$data['info'] = $data['msg'];
			if($data['code'] == 2){
				$data['status'] = 1;
				//将信息写入数据表中
				$message_data['mobile'] = $data['mobile'];
				$message_data['code'] = $data['mobile_code'];
				$message_data['content'] = $data['content'];
				$message_data['create_time'] = time();
				$message_data['status'] = 1;
				M('Message')->add($message_data);
			} else {
				$data['status'] = 0;
			}
			$this->ajaxReturn($data);
		} else {
			$this->error(L('_ACESS_ERROR_'));
		}
	}
}
?>
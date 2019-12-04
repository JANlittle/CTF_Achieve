<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 短信接口类 ]
*/
namespace Api\Message;
class Message
{
	private $target = 'http://106.ihuyi.cn/webservice/sms.php?method=Submit';
	private $codelength = 6; //验证码长度
	private $mobile; //手机号
	private $password; //密码
	private $account; //账号
	private $content; //模板内容：您的验证码是：{$code}。请不要把验证码泄露给其他人。
	private $code; //验证码
	private $type; //随机字符串类型

	/**
	 * [__construct 初始化]
	 */
	public function __construct($mobile = '',$config = array()){
		//获取当前手机
		$this->mobile = $mobile;

		//账号
		$this->account = $config['MESSAGE_ACCOUT'];

		//密码
		$this->password = $config['MESSAGE_PASSWORD'];
		
		//验证码长度
		if(is_numeric($config['MESSAGE_CODELENTH']) && $config['MESSAGE_CODELENTH'] > 0){
			$this->codelength = $config['MESSAGE_CODELENTH'];
		}

		//类型
		$this->type = $config['MESSAGE_TYPE'];

		//模板
		$this->content = $config['MESSAGE_CONTENT'];

		//产生随机验证码
		$this->code = $this->random($this->codelength,$this->type);

		//解析短信模板
		$this->content = str_replace('{$code}', $this->code, $this->content);
	}

	/**
	 * [send 发送]
	 * @return [type] [description]
	 */
	public function send()
	{

		$post_data = "account=".$this->account."&password=".$this->password."&mobile=".$this->mobile."&content=".rawurlencode($this->content);
		//密码可以使用明文密码或使用32位MD5加密
		$return_data =  $this->xml_to_array($this->Post($post_data, $this->target));

		//返回数据code：返回值为2时，表示提交成功，smsid：消息ID，msg：提交结果描述
		$data = $return_data['SubmitResult'];
		$data['mobile'] = $this->mobile;
		$data['content'] = $this->content;
		$data['mobile_code'] = $this->code;
		return $data;
	}

	/**
	 * [Post 发送信息到服务器获取信息]
	 * @param [string] $curlPost [发送的数据]
	 * @param [string] $url      [接口地址]
	 */
	private function Post($curlPost,$url){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_NOBODY, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
		$return_str = curl_exec($curl);
		curl_close($curl);
		return $return_str;
	}

	/**
	 * [xml_to_array 返回的xml数据转成字符串]
	 * @param  [type] $xml [xml数据]
	 * @return [array]     [返回数组]
	 */
	private function xml_to_array($xml){
		$reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
		if(preg_match_all($reg, $xml, $matches)){
			$count = count($matches[0]);
			for($i = 0; $i < $count; $i++){
			$subxml= $matches[2][$i];
			$key = $matches[1][$i];
				if(preg_match( $reg, $subxml )){
					$arr[$key] = $this->xml_to_array( $subxml );
				}else{
					$arr[$key] = $subxml;
				}
			}
		}
		return $arr;
	}


	/**
	 * [random 随机函数]
	 * @param  integer $length  [验证码长度]
	 * @param  integer $numeric [数字|字符串]
	 * @return [string]         [返回hash]
	 */
	private function random($length = 6 , $numeric = 0) {
		if($numeric) {
			$hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
		} else {
			$hash = '';
			$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
			$max = strlen($chars) - 1;
			for($i = 0; $i < $length; $i++) {
				$hash .= $chars[mt_rand(0, $max)];
			}
		}
		return $hash;
	}
}
?>
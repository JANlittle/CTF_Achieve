<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 微信公众号 ]
*/
namespace Kgmanage\Controller;

class WechatPublicController extends CommonController
{
	/**
	 * [index 微信配置]
	 */
	public function index()
	{
		if(IS_POST){
			$WechatPublic = D('WechatPublic');
			if($WechatPublic->create()){
				if($saveid = $WechatPublic->save()){
					//生成微信缓存
					$cache_data = M('WechatPublic')->find();
					$cache_name = 'WechatConfig';
					$previous_cache = S($cache_name);
					if(!empty($previous_cache)){
						S($cache_name ,null);
					}
					//设置缓存
					S($cache_name, $cache_data);
					$this->success(L('_SAVE_SUCCESS_'));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($WechatPublic->getError());
			}
		} else {
			$this->data = M('WechatPublic')->find();

			//获取当前域名
			$this->urlinfo = parse_url(U('','',false,true));

			$this->display();
		}
	}
}
?>
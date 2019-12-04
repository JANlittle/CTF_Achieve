<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 微信宣传 ]
*/
namespace Kgmanage\Controller;

class WechatExtensionController extends CommonController
{
	/**
	 * [index 宣传页]
	 */
	public function index()
	{
		if(IS_POST){
			$WechatExtension = D('WechatExtension');
			if($data = $WechatExtension->create()){

				//key重新索引解决冲突
				$WechatExtension->thumb = array_values($WechatExtension->thumb); 
				$WechatExtension->thumb = serialize($WechatExtension->thumb);

				if($WechatExtension->save()){
					//生成微信缓存
					$cache_data = M('WechatExtension')->find();
					$cache_name = 'WechatExtension';
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
				$this->error($WechatExtension->getError());
			}
		} else {

			$data = M('WechatExtension')->find();

			//处理图片解析
			if(!empty($data)){
				if(!empty($data['thumb'])){
					$data['thumb'] = unserialize($data['thumb']);
				}
			}

			$this->data = $data;
			$this->display();
		}
	}
}
?>
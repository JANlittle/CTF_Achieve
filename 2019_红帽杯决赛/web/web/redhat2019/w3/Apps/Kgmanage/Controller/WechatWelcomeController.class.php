<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 微信欢迎语 ]
*/
namespace Kgmanage\Controller;

class WechatWelcomeController extends CommonController
{
	/**
	 * [index 欢迎语]
	 */
	public function index()
	{
		if(IS_POST){
			$WechatWelcome = D('WechatWelcome');
			if($data = $WechatWelcome->create()){

				$type = I('post.type');
				if($type == 2){
					//key重新索引解决冲突
					$WechatWelcome->thumb = array_values($WechatWelcome->thumb); 
					$WechatWelcome->thumb = serialize($WechatWelcome->thumb);
				} else {
					//获取原始数据
					$original_where['id'] = array('eq',$WechatWelcome->id);
					if(!$original_data = M('WechatWelcome')->where($original_where)->find()){
						$this->error(L('_ACCESS_ERROR_'));
					}
					$WechatWelcome->thumb = "";
				}

				//设置内容
				$WechatWelcome->content = I('post.content','',false);
				if($WechatWelcome->save()){
					if($type != 2){
						//删除图片或附件数据
						$pictures_files_arr[] = unserialize($original_data['thumb']);
						delfilefun($pictures_files_arr);
					}

					//生成微信缓存
					$cache_data = M('WechatWelcome')->find();
					$cache_name = 'WechatWelcome';
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
				$this->error($WechatWelcome->getError());
			}
		} else {
			$data = M('WechatWelcome')->find();
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
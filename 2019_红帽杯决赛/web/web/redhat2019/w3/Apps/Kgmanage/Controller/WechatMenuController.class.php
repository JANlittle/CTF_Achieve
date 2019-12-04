<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 自定义菜单 ]
*/
namespace Kgmanage\Controller;

class WechatMenuController extends CommonController
{
	/**
	 * [index 列表]
	 */
	public function index()
	{
		$WechatMenu = D('WechatMenu');
		$count = $WechatMenu->field('id')->count();
		$data = $WechatMenu->order('sort ASC,id DESC')->select();
		$this->data = unlimitedForLayer($data);
		$this->typedata = array(
			'none'=>'无事件的一级菜单',
			'click'=>'点击推事件',
			'view'=>'跳转URL',
			'scancode_push'=>'扫码推事件',
			'scancode_waitmsg'=>'扫码带提示',
			'pic_sysphoto'=>'弹出系统拍照发图',
			'pic_photo_or_album'=>'弹出拍照或者相册发图',
			'pic_weixin'=>'弹出微信相册发图器',
			'location_select'=>'弹出地理位置选择器'
		);
		$this->display();
	}

	/**
	 * [create 生成自定义菜单]
	 * @return [type]        [description]
	 */
	public function create()
	{
		//这里需要用到Wechat高级接口
		$WechatMenu = M('WechatMenu');
		$data = $WechatMenu->order('sort ASC,id DESC')->select();
		$data = unlimitedForLayer($data);

		//设置菜单数组
		$arr = array();
		foreach ($data as $key => $value) {
			$arr_a = array();
			if(!empty($value['child'])){
				$arr_a['name'] = $value['name'];
				foreach ($value['child'] as $key_c => $value_c) {
					if($value_c['type'] == 'view'){
						$arr_a['sub_button'][] = array("type"=>$value_c['type'],"name"=>$value_c['name'],"url"=>$value_c['url']);
					} else{
						$arr_a['sub_button'][] = array("type"=>$value_c['type'],"name"=>$value_c['name'],"key"=>$value_c['key']);
					}
				}
			} else {
				if($value['type'] == 'view'){
					$arr_a = array("type"=>$value['type'],"name"=>$value['name'],"url"=>$value['url']);
				} else{
					$arr_a = array("type"=>$value['type'],"name"=>$value['name'],"key"=>$value['key']);
				}
			}
			$arr[] = $arr_a;
		}

		//获取微信配置信息
		$cache_name = 'WechatConfig';
        $config = S($cache_name);
        if(empty($config)){
            $cache_data = M('WechatPublic')->find();
            S($cache_name, $cache_data);
        }
        $config = S($cache_name);

        //实例化微信接口
        $access_token = S('access_token');
        if(!empty($access_token)){
        	$token = $access_token['access_token'];
        } else {
        	$token = null;
        }
		$WechatAuth = new \Api\Wechat\WechatAuth($config['appid'],$config['appsecret'],$token);
		
		//获取acces_token
		if(empty($access_token)){
			$access_token = $WechatAuth->getAccessToken();
			$access_token_err = array(
				'40001' => 'AppID错误！',
				'40125' => 'AppSecret错误！',
				'40013' => '不合法的Appid！',
				'45009' => '获取access_token超过了限制的次数！'
			);
			if(isset($access_token['errcode'])) {
				$this->error($access_token_err[$access_token['errcode']]);
			} else {
				S('access_token',$access_token,$access_token['expires_in']);
			}
		}

		//设置菜单
		$msgdata = $WechatAuth->menuCreate($arr);

		//返回信息
		$error_arr = array(
			'40015' => '不合法的菜单类型',
			'40016' => '不合法的按钮个数',
			'40017' => '不合法的按钮个数',
			'40018' => '不合法的按钮名字长度',
			'40019' => '不合法的按钮KEY长度',
			'40020' => '不合法的按钮URL长度',
			'40021' => '不合法的菜单版本号',
			'40022' => '不合法的子菜单级数',
			'40023' => '不合法的子菜单按钮个数',
			'40024' => '不合法的子菜单按钮类型',
			'40025' => '不合法的子菜单按钮名字长度',
			'40026' => '不合法的子菜单按钮KEY长度',
			'40027' => '不合法的子菜单按钮URL长度',
			'40028' => '不合法的自定义菜单使用用户'
		);
		if($msgdata['errcode'] > 0){
			$this->error($error_arr[$msgdata['errcode']]);
		} else if($msgdata['errcode'] == 0) {
			$this->success('设置菜单成功！');
		}
	}

	/**
	 * [add 新增]
	 */
	public function add()
	{
		//数据提交
		if(IS_POST){
			//默认菜单key数组
			$key_arr = array(
				'scancode_waitmsg' => 'rselfmenu_0_0', //扫码带提示
				'scancode_push' => 'rselfmenu_0_1', //扫码推事件
				'pic_sysphoto' => 'rselfmenu_1_0', //系统拍照发图
				'pic_photo_or_album' => 'rselfmenu_1_1', //拍照或者相册发图
				'pic_weixin' => 'rselfmenu_1_2', //微信相册发图
				'location_select' => 'rselfmenu_2_0', //发送位置
				'none' => '', //默认为空
				'click' => I('post.key'), //click事件设置的key
				'view' => '', //设置超链接为空
			);
			$type = I('post.type');

			$WechatMenu = D('WechatMenu');
			if($data = $WechatMenu->create()){
				$WechatMenu->create_time = time();
				//设置key
				$WechatMenu->key = $key_arr[$type];
				if($insertid = $WechatMenu->add()){
					//新增排序
					$WechatMenu->where("id = {$insertid}")->setField('sort',$insertid);
					$this->success(L('_ADD_SUCCESS_'),U('index'));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($WechatMenu->getError());
			}
		} else {
			//获取菜单列表
			$WechatMenu = M('WechatMenu');
			$menulist_a = $WechatMenu->where('pid = 0')->order('sort ASC,id DESC')->select();
			//组装select下拉菜单
			$this->menulist_a = getSelectedOption($menulist_a, 0, 0);

			//获取菜单
			$this->display();
		}
	}

	/**
	 * [edit 编辑]
	*/
	public function edit()
	{
		//提交数据处理
		if(IS_POST){
			//默认菜单key数组
			$key_arr = array(
				'scancode_waitmsg' => 'rselfmenu_0_0', //扫码带提示
				'scancode_push' => 'rselfmenu_0_1', //扫码推事件
				'pic_sysphoto' => 'rselfmenu_1_0', //系统拍照发图
				'pic_photo_or_album' => 'rselfmenu_1_1', //拍照或者相册发图
				'pic_weixin' => 'rselfmenu_1_2', //微信相册发图
				'location_select' => 'rselfmenu_2_0', //发送位置
				'none' => '', //默认为空
				'click' => I('post.key'), //click事件设置的key
				'view' => '', //设置超链接为空
			);
			$type = I('post.type');
			$WechatMenu = D('WechatMenu');
			if($data = $WechatMenu->create()){
				$WechatMenu->update_time = time();
				//设置key
				$WechatMenu->key = $key_arr[$type];
				//保存数据
				if($WechatMenu->save()){
					$this->success(L('_SAVE_SUCCESS_'),U('index'));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($WechatMenu->getError());
			}
		} else {
			//验证数据
			$id = I('get.id');
			if(!is_numeric($id)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//获取数据
			$WechatMenu = M('WechatMenu');
			$data = $WechatMenu->find($id);
			if(!$data){
				$this->error(L('_NODATA_'));
			}
			$this->data = $data;

			//获取菜单列表
			$menulist_a = $WechatMenu->where('pid = 0')->order('sort ASC,id DESC')->select();

			//编辑菜单需要清楚自身菜单以及子类菜单
			$menulist_child = getChilds($menulist_a,$data['id']);
			//获取当前菜单以及所有子类id
			$idss = array();
			if(!empty($menulist_child)){
				foreach ($menulist_child as $key => $value) {
					$idss[] = $value['id'];
				}
			}
			//将父类id压入数组中
			array_push($idss,$data['id']);

			//删除数据中当前id包括子类id的数据
			if(!empty($menulist_a)){
				foreach ($menulist_a as $key => $value) {
					if(in_array($value['id'],$idss)){
						unset($menulist_a[$key]);
					}
				}
			}

			//组装select下拉菜单
			$this->menulist_a = getSelectedOption($menulist_a, 0, $data['pid']);
			$this->display();
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
			$WechatMenu = M('WechatMenu');
			foreach ($ids as $key => $value) {
				$data = $WechatMenu->field('id')->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//删除数据时需要删除子类
			$data = $WechatMenu->field('id,pid')->select();
			$arr = array();
			foreach ($ids as $key => $value) {
				//获取子类id数组
				$arr[] = getChilds($data,$value);
			}

			//取出id
			$idss = array();
			if(!empty($arr)){
				foreach ($arr as $key => $value) {
					foreach ($value as $key_1 => $value_1) {
						$idss[] = $value_1['id'];
					}
				}
			}

			//合并父级id与找到的子类id
			$ids = array_unique(array_merge($ids,$idss));
			$id = implode(',',$ids);

			//删除数据
			if($WechatMenu->delete($id)){
				$this->success(L('_DEL_SUCCESS_'));
			} else {
				$this->error(L('_DEL_ERROR_'));
			}
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [sort 排序]
	 * @return [type] [description]
	 */
	public function sort()
	{
		if(IS_POST){
			$sort = I('post.sort');
			$sortarr = explode(",", $sort);

			//验证数据
			if(empty($sortarr)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			$WechatMenu = M('WechatMenu');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$WechatMenu->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}
}
?>
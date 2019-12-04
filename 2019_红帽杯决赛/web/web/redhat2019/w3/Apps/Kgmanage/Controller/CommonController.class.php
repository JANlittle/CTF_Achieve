<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 后台公共类 ]
*/
namespace Kgmanage\Controller;
use Think\Controller;


class CommonController extends Controller
{
	/*
	* 公共类自动执函数
	*/
	public function _initialize()
	{
		//不存在session直接退出
		if(!session("?uid")){
			$this->redirect("Login/index");
		}

		//下面开始做权限
		if(!authCheck(MODULE_NAME . "/" . CONTROLLER_NAME . "/" . ACTION_NAME, session("uid"))){
			$this->error(L('_NOAUTH_'));
		}

		//获取站点
		$this->getSite();

		//获取菜单
		$this->getMenus();

		//获取配置
		$system_config = S('ALL_CONFIG'.session("siteid"));
		//空的情况下生成缓存
		if(empty($system_config)){
			//生成缓存
			setConfig('ALL_CONFIG'.session("siteid"),array(0,session("siteid")));
			$system_config = S('ALL_CONFIG'.session("siteid"));
		}
		//缓存设置成配置
		C($system_config);
	}

	/**
	 * [getSite 获取站点信息]
	 */
	protected function getSite()
	{
		//获取全部站点
		$site = M('Site');
		$where['status'] = array('eq',1);
		$this->site = $site->where($where)->order('id ASC')->select();
	}

	/**
	 * [setSite 设置默认站点]
	 */
	public function setSite()
	{
		if(IS_POST){
			$siteid = I('post.data');
			session('siteid',$siteid);
			$site = M('Site');
			$where['id'] = array('eq',$siteid);
			$where['status'] = array('eq',1);
			$siteinfo = $site->where($where)->find();
			empty($siteinfo) ? $siteinfo['name'] = '公共设置' : '';
			$this->success(sprintf(L('_SITE_SUCCESS_'),$siteinfo['name']));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [getMenus 获取后台菜单]
	 */
	protected function getMenus(){
		//获取开发身份
		$adminuser_where['id'] = session("uid");
		$adminuser_one = M('Adminuser')->field('id,dev')->where($adminuser_where)->find();
		//如果不是开发者那么隐藏开发者选项的菜单，如果是开发者则忽略
		if(!$adminuser_one['dev']){
			$dev_where['dev'] = array('eq',0);

			//设置不同的条件
			$all_where['dev'] = $dev_where['dev'];
			$main_wehre['dev'] = $dev_where['dev'];
			$nomain_wehre['dev'] = $dev_where['dev'];
		}

	/*获取当前站点的条件*/
			$all_where['siteid'] = array('in',array(0,session("siteid")));
			$main_wehre['siteid'] = array('in',array(0,session("siteid")));
			$nomain_wehre['siteid'] = array('in',array(0,session("siteid")));
	/*end 获取当前站点的条件*/


		//获取后台菜单
		$adminmenud = D('Adminmenu');
		$all_where['status'] = array('eq',1);
		$menu_data_list = $adminmenud->where($all_where)->relation('rulemodle')->order('sort ASC')->select();

		//处理数据
		if(!empty($menu_data_list)){
			foreach ($menu_data_list as $key => $value) {
				if(!empty($value['parameter'])){
					$menu_data_list[$key]['parameter'] = htmlspecialchars_decode($value['parameter']);
				}
			}
		}

		//顶级菜单
		$main_wehre['status'] = array('eq',1);
		$main_wehre['pid'] = array('eq',0);
		$menu['main'] = $adminmenud->where($main_wehre)->order('sort ASC')->select();

	/*设置主菜单的url地址为第一个有权限的子菜单的url*/
		//获取不是顶级父类
		$nomain_wehre['status'] = array('eq',1);
		$nomain_wehre['pid'] = array('neq',0);
		$menu['nomain'] = $adminmenud->where($nomain_wehre)->order('sort ASC')->select();

		//获取有权限的子类菜单
		if(!empty($menu['nomain'])){
			foreach ($menu['nomain'] as $key => $value) {
				//验证权限
				if(!authCheck(MODULE_NAME . "/" . $value['url'], session("uid"))){
					unset($menu['nomain'][$key]);
				}
			}
		}
		//合并有权限的子类菜单与顶级菜单（主要是为了设置主菜单的url，解决当前主菜单默认的url没有权限而子菜单有权限不显示的问题）
		$menu['main'] = array_merge($menu['main'],$menu['nomain']);
		$menu['main'] = unlimitedForLayer($menu['main']);

		//设置主菜单的url地址为第一个有权限的子菜单的url
		if(!empty($menu['main'])){
			foreach ($menu['main'] as $key => $value) {
				//如果顶级菜单有子菜单，那么将子菜单的第一个url赋值给主菜单
				if(count($value['child'])){
					$menu['main'][$key]['url'] = $value['child'][0]['url'];
					$menu['main'][$key]['parameter'] = htmlspecialchars_decode($value['child'][0]['parameter']);
				}
			}
		}
	/*end 设置主菜单的url地址为第一个有权限的子菜单的url*/

	/*获取当前url的顶级主菜单id（用于高亮选中显示主菜单）*/
		$current = $adminmenud->field('id,pid,name')->where('url = "' . CONTROLLER_NAME . "/" . ACTION_NAME .'"')->find();
		if($current['pid'] != 0){
			$parents = getParents($menu_data_list,$current['id']);
			if(!empty($parents)){
				foreach ($parents as $key => $value) {
					if($value['pid'] != 0){
						unset($parents[$key]);
					}
				}
			}
			$parents = array_values($parents);
			$currentid = $parents[0]['id'];
		} else {
			$currentid = $current['id'];
		}
	/* end 获取当前url的顶级主菜单id（用于高亮选中显示主菜单）*/

		//设置高亮主菜单
		if(!empty($menu['main'])){
			foreach ($menu['main'] as $key => $value) {
				//设置高亮class
				if($value['id'] == $currentid){
					$menu['main'][$key]['class'] = 'current';
				} else {
					$menu['main'][$key]['class'] = "";
				}

				//验证权限
				if(!authCheck(MODULE_NAME . "/" . $value['url'], session("uid"))){
					unset($menu['main'][$key]);
				}
			}
		}

		//重新索引
		$menu['main'] = array_values($menu['main']);
		$this->menumain = $menu['main'];

	/*通过查找到的父类id来获取子菜单*/
		$menu['child'] = array();
		//通过父类的id获取子类的菜单
		$child = getChilds($menu_data_list,$currentid);
		if(!empty($child)){
			foreach ($child as $key => $value) {
				//如果当前的菜单则添加childon样式，包括父类也添加
				if($current['id'] == $value['id']){
					$child[$key]['class'] = 'childon';

					//父类加上childon选中样式
					foreach ($child as $key_1 => $value_1) {
						if($value['pid'] == $value_1['id']){
							$child[$key_1]['class'] = 'childon';
						}
					}
				} else {
					$child[$key]['class'] = '';
				}

				//验证权限
				if(!authCheck(MODULE_NAME . "/" . $value['url'], session("uid"))){
					unset($child[$key]);
				}
			}
		}

		//将菜单无限极整理
		$child = unlimitedForLayer($child,'child',$currentid);
		if(!empty($child)){
			//进行模块分组
			foreach ($child as $key => $value) {
				$menu['child'][$value['modlename']][] = $value;
			}
		}
		$this->menuchild = $menu['child'];
	/*end 通过查找到的父类id来获取子菜单*/
	}

	/**
	 * [_empty 空操作]
	 * @return [bool]
	 */
	public function _empty()
	{
		$this->redirect("Index/index");
	}
}
?>
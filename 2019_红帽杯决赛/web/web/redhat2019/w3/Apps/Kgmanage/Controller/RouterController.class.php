<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ URL路由 ]
*/
namespace Kgmanage\Controller;

class RouterController extends CommonController
{
	/**
	 * [index 列表]
	 */
	public function index()
	{
		$Router = D('Router');
		$count = $Router->field('id')->count();
		$data = $Router->order('sort ASC,id DESC')->select();
		$this->data = unlimitedForLayer($data);
		$this->display();
	}

	/**
	 * [add 新增]
	 */
	public function add()
	{
		//数据提交
		if(IS_POST){
			$Router = D('Router');
			if($data = $Router->create()){
				//验证表达式、路由地址不能为空
				$pid = I('post.pid');
				if($pid != 0){
					$expression = I('post.expression');
					$routerurl = I('post.routerurl');
					if(empty($expression)){
						$this->error(L('_ROUTEREXP_MUST_'));
					}
					if(empty($routerurl)){
						$this->error(L('_ROUTERURL_MUST_'));
					}
				}
			
				$Router->create_time = time();
				if($insertid = $Router->add()){
					//新增排序
					$Router->where("id = {$insertid}")->setField('sort',$insertid);
					$this->success(L('_ADD_SUCCESS_'),U('index'));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($Router->getError());
			}
		} else {
			//获取菜单列表
			$Router = M('Router');
			$where['status'] = array('eq',1);
			$where['pid'] = array('eq',0);
			$menulist_a = $Router->where($where)->order('sort ASC,id DESC')->select();
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
			$Router = D('Router');
			if($data = $Router->create()){
				//验证表达式、路由地址不能为空
				$pid = I('post.pid');
				if($pid != 0){
					$expression = I('post.expression');
					$routerurl = I('post.routerurl');
					if(empty($expression)){
						$this->error(L('_ROUTEREXP_MUST_'));
					}
					if(empty($routerurl)){
						$this->error(L('_ROUTERURL_MUST_'));
					}
				}

				$Router->update_time = time();
				//保存数据
				if($Router->save()){
					$this->success(L('_SAVE_SUCCESS_'),U('index'));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($Router->getError());
			}
		} else {
			//验证数据
			$id = I('get.id');
			if(!is_numeric($id)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//获取数据
			$Router = M('Router');
			$data = $Router->find($id);
			if(!$data){
				$this->error(L('_NODATA_'));
			}
			$this->data = $data;

			//获取菜单列表
			$where['status'] = array('eq',1);
			$where['pid'] = array('eq',0);
			$menulist_a = $Router->where($where)->order('sort ASC,id DESC')->select();

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
			$Router = M('Router');
			foreach ($ids as $key => $value) {
				$data = $Router->field('id')->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//删除数据时需要删除子类
			$data = $Router->field('id,pid')->select();
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
			if($Router->delete($id)){
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

			$Router = M('Router');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$Router->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [create 生成接口配置文件]
	 */
	public function create()
	{
		if(IS_POST){
			$Router = D('Router');
			$where['status'] = array('eq',1);
			$data = $Router->where($where)->order('sort ASC,id DESC')->select();
			$data = unlimitedForLayer($data);

			$str = "<?php\n\r";
			$str .= "/*\n\r";
			$str .= "* Author: [ Copy Lian ]\n\r";
			$str .= "* Date: [ ".date("Y.m.d")." ]\n\r";
			$str .= "* Description [ 路由配置文件 ]\n\r";
			$str .= "*/\n\r\n\r";
			$str .= "return array(\n\r";

			$str .= "\t" . "//开启全局路由\n\r";
			$str .= "\t" . "'URL_ROUTER_ON' => true,\n\n\r";
			$str .= "\t" . "//全局路由规则：全局路由是针对所有分组项目\n\r";
			$str .= "\t'URL_ROUTE_RULES' => array(\n\r";
			//写入数据
			if(!empty($data)){			
				if(!empty($data)){
					foreach ($data as $key => $value) {
						$str .= "\n\r";
						$str .= "\t\t" . "//".$value['name']."\n\r";
						if(!empty($value['child'])){
							foreach ($value['child'] as $key => $child) {
								$str .= "\t\t" . "'".htmlspecialchars_decode($child['expression'])."' => '" . htmlspecialchars_decode($child['routerurl']) . "',\n\r";
							}
						}
					}
				}
			}
			$str .= "\t),\n\r";

			$str .= ");\n\r?>";

			$ok = file_put_contents(CONF_PATH . "router.php", $str);
			if($ok){
				$this->success(L('_SETROUTER_SUCCESS_'));
			} else {
				$this->error(L('_SETROUTER_ERROR_'));
			}
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}
}
?>
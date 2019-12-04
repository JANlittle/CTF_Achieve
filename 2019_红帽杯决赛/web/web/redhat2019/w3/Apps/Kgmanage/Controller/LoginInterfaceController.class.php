<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 登录接口 ]
*/
namespace Kgmanage\Controller;

class LoginInterfaceController extends CommonController
{
	/**
	 * [index 列表]
	 */
	public function index()
	{
		$pid = I('get.pid',0);
		$Logininterface = D('Logininterface');

		$where['siteid'] = array('eq',session('siteid'));
		$count = $Logininterface->where($where)->field('id')->count();
		//获取分页
		$page = getPage($count);
		$this->pagelist = $page->show();
		$this->data = $Logininterface->where($where)->order('sort ASC,id DESC')->limit($page->firstRow,$page->listRows)->select();
		
	/*获取url参数*/
		$this->parameter = getParameter(I('get.'),$page);
	/* end 获取url参数*/
	
		$this->display();
	}

	/**
	 * [add 新增]
	 */
	public function add()
	{
		//数据提交
		if(IS_POST){
			$Logininterface = D('Logininterface');
			if($data = $Logininterface->create()){
				$Logininterface->siteid = session('siteid');
				if($insertid = $Logininterface->add()){
					//新增排序
					$Logininterface->where("id = {$insertid}")->setField('sort',$insertid);
					$this->success(L('_ADD_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($Logininterface->getError());
			}
		} else {
			//获取参数
			$this->parameter = I('get.parameter');
			
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
			$Logininterface = D('Logininterface');
			if($data = $Logininterface->create()){
				//保存数据
				if($Logininterface->save()){
					$this->success(L('_SAVE_SUCCESS_'),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($Logininterface->getError());
			}
		} else {
			//验证数据
			$id = I('get.id');
			if(!is_numeric($id)){
				$this->error(L('_ACCESS_ERROR_'));
			}

			//获取参数
			$this->parameter = I('get.parameter');

			//获取数据
			$Logininterface = M('Logininterface');
			$where['siteid'] = array('eq',session('siteid'));
			$where['id'] = array('eq',$id);
			$data = $Logininterface->where($where)->find();
			if(!$data){
				$this->error(L('_NODATA_'));
			}
			$this->data = $data;

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
			$Logininterface = M('Logininterface');
			foreach ($ids as $key => $value) {
				$data = $Logininterface->field('id')->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}
			//删除数据
			if($Logininterface->delete($id)){
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

			$Logininterface = M('Logininterface');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$Logininterface->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [setConfig 生成接口配置文件]
	 */
	public function setConfig()
	{
		if(IS_POST){
			$where['siteid'] = array('eq',session('siteid'));
			$data = M('Logininterface')->where($where)->select();
			$str = "<?php\n\r";
			$str .= "/*\n\r";
			$str .= "* Author: [ Copy Lian ]\n\r";
			$str .= "* Date: [ ".date("Y.m.d")." ]\n\r";
			$str .= "* Description [ 站点id为：".session('siteid')." 的登录接口配置文件 ]\n\r";
			$str .= "*/\n\r\n\r";
			$str .= "return array(\n\r";

			//写入数据
			if(!empty($data)){
				foreach ($data as $key => $value) {
					$str .= "\t//" . $value['name'] . "配置\n\r";
					$str .= "\t'THINK_SDK_" . strtoupper($value['typename']) . "' => array(\n\r";
					$str .= "\t\t" . "'APP_KEY' => '" . $value['appkey'] . "', //应用注册成功后分配的 APP ID\n\r";
					$str .= "\t\t" . "'APP_SECRET' => '" . $value['appsecret'] . "', //应用注册成功后分配的KEY\n\r";
					$str .= "\t\t" . "'APP_STATUS' => " . $value['status'] . ", //应用状态\n\r";
					$str .= "\t\t" . "'APP_NAME' => '" . $value['name'] . "', //应用名称\n\r";
					$str .= "\t\t" . "'CALLBACK' => '" . $value['callbak'] . "', //应用回调地址\n\r";
					$str .= "\t),\n\r\n\r";
				}
			}

			$str .= ");\n\r?>";
			$ok = file_put_contents(CONF_PATH . "logininterface_site_".$value['siteid'].".php", $str);
			if($ok){
				$this->success(L('_SETCONFIG_SUCCESS_'));
			} else {
				$this->error(L('_SETCONFIG_ERROR_'));
			}
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

}
?>
<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 支付接口 ]
*/
namespace Kgmanage\Controller;

class PayController extends CommonController
{
	/**
	 * [index 列表]
	 */
	public function index()
	{
		$pay = M('Pay');
		$data = $pay->order('sort ASC,id DESC')->select();

		//解析配置
		if(!empty($data)){
			foreach ($data as $key => $value) {
				if(!empty($value['config'])){
					$data_conf = explode("|", $value['config']);
					if(!empty($data_conf)){
						foreach ($data_conf as $key_1 => $value_1) {
							list($data_key,$data_value) = explode(":",$value_1);
							$data[$key]['configs'][$data_key] = $data_value;
						}
					}
				}
			}
		}
        $this->data = $data;
		$this->display();
	}


	/**
	 * [set 配置]
	 */
	public function set()
	{
		if(IS_POST){
			$pay = D('Pay');
			if($pay->create()){
				//设置配置
				$config = I('post.config');
				$config_arr = array();
				foreach ($config as $key => $value) {
					$config_arr[] = $key.":".$value;
				}
				$pay->config = implode("|",$config_arr);

				//保存
				if($pay->save()){
					$this->success(L('_SAVE_SUCCESS_'));
				} else {
					$this->error(L('_SAVE_ERROR_'));
				}
			} else {
				$this->error($pay->getError());
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

			$pay = M('Pay');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$pay->save($data);
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
			$data = M('Pay')->select();
			//解析配置
			if(!empty($data)){
				foreach ($data as $key => $value) {
					if(!empty($value['config'])){
						$data_conf = explode("|", $value['config']);
						if(!empty($data_conf)){
							foreach ($data_conf as $key_1 => $value_1) {
								list($data_key,$data_value) = explode(":",$value_1);
								$data[$key]['configs'][$data_key] = $data_value;
							}
						}
					}
				}
			}

			$str = "<?php\n\r";
			$str .= "/*\n\r";
			$str .= "* Author: [ Copy Lian ]\n\r";
			$str .= "* Date: [ ".date("Y.m.d")." ]\n\r";
			$str .= "* Description [ 支付接口配置文件 ]\n\r";
			$str .= "*/\n\r\n\r";
			$str .= "return array(\n\r";
			//写入数据
			if(!empty($data)){
				foreach ($data as $key => $value) {
					$str .= "\t'PAY_INTERFACE_" . strtoupper($value['typename']) . "' => array(\n\r";
					$str .= "\t\t" . "'PAYTYPE' => '" . strtolower($value['typename']) . "',\n\r";
					$str .= "\t\t" . "'PAYNAME' => '" . $value['payname'] . "',\n\r";
					//设置配置
					if(!empty($value['configs'])){
						foreach ($value['configs'] as $key_1 => $value_1) {
							$str .= "\t\t" . "'" . strtoupper($key_1) . "' => '" . $value_1 . "',\n\r";
						}
					}
					$str .= "\t\t" . "'STATUS' => " . $value['status'] . ",\n\r";
					$str .= "\t\t" . "'DESCRIPTION' => '" . $value['description'] . "',\n\r";
					$str .= "\t),\n\r\n\r";
				}
			}

			$str .= ");\n\r?>";
			$ok = file_put_contents(CONF_PATH . 'pay.php', $str);
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
<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 表单向导控制器 ]
*/
namespace Home\Controller;
class FormguideController extends CommonController {

    /**
     * 表单列表
    */
    public function index(){
        //获取表单向导信息
    	$Formguide = M('Formguide');
    	$where['siteid'] = array('eq',C('SITEID'));
    	$where['status'] = array('eq',1);
    	$this->data = $Formguide->where($where)->select();

        $this->seoData = array('title'=>'表单向导','keywords'=>'表单向导','description'=>'表单向导');
        $this->display();
    }

    /**
     * [guide 表单向导]
     * @return [type] [description]
     */
    public function guide()
    {
    	if(IS_POST){
    		//验证ip是否被限制
            $this->iplimit();

    		$modelid = I('post.modelid');
    		if(!is_numeric($modelid)){
    			$this->error(L('_ACCESS_ERROR_'));
    		}

    		//获取表单
			$Formguideinfo = M('Formguide')->find($modelid);
			$tablename = $Formguideinfo['tablename'];
			$model = M($tablename);

			//验证是否允许游客登陆
            if($Formguideinfo['guest']){
                //允许游客
                $uid = 0;
            } else {
                //不允许游客，则提示登陆
                if(session('?echouid')){
                    $uid = session('echouid');
                } else {
                    //请去登陆
                    $return_data['status']  = -1;
                    $return_data['info'] = '用户未登录请去登陆！';

                    //跳转地址
		            $returnurl = encode(U("guide@$this->myurl",array('id'=>$modelid),false,true));
		            cookie('login_return_url',$returnurl);

                    $this->ajaxReturn($return_data);
                }
            }

			//获取字段
			$FormguideField = M('FormguideField');
			$FormguideField_where['modelid'] = array('eq',$modelid);
			$FormguideField_where['status'] = array('eq',1);
			$fields = $FormguideField->where($FormguideField_where)->order('sort ASC,id DESC')->select();

			//自动完成、自动验证
			$validate = array();
			$auto = array();
			foreach ($fields as $key => $value) {
				if(!empty($value['validate'])){
					//解析数组字符串
					eval("\$validate_arr = ".str_replace("field",$value['field'],$value['validate']).";");
					if(is_array($validate_arr)){
						foreach ($validate_arr as $key_1 => $value_1) {
							$validate[] = $value_1;
						}
					}
				}

				if(!empty($value['auto'])){
					//解析数组字符串
					eval("\$auto_arr = ".str_replace("field",$value['field'],$value['auto']).";");
					if(is_array($auto_arr)){
						foreach ($auto_arr as $key_1 => $value_1) {
							$auto[] = $value_1;
						}
					}
				}
			}

			//验证
			if($model->validate($validate)->create()){
				//自动完成
				$data = $model->auto($auto)->create();

				//字段类型数组
				$field_type = array();
				foreach ($fields as $key => $value) {
					//处理checkbox重复的值
					if($value['type'] == 'checkbox'){
						$data[$value['field']] = array_unique($data[$value['field']]);
					}

					//处理相应字段下不同类型的数据
					$field_type[$value['field']] = $value['type'];

					//判断file、picture、thumb类型如果没有数据时
					if($value['type'] == 'picture' || $value['type'] == 'file'){
						if(!isset($data[$value['field']])){
							$data[$value['field']] = array();
						}
					}
				}

				//开始处理
				if(!empty($data)){
					foreach ($data as $key => $value) {
						//如果时间
						switch ($field_type[$key]) {
							case 'datetime':
								$data[$key] = strtotime($value);
								break;
							case 'rangetime':
								if(!empty($value)){
									foreach ($value as $key_1 => $value_1) {
										$value[$key_1] = strtotime($value_1);
									}
								}
								$data[$key] = serialize($value);
								break;
							case 'checkbox':
								$data[$key] = serialize($value);
								break;
							case 'password':
								$password = I('post.'.$key);
								if(empty($password)){
									unset($data[$key]);
								}
								break;
							case 'picture':
								$value = array_values($value); //key重新索引解决冲突
								$data[$key] = serialize($value);
								break;
							case 'file':
								$value = array_values($value);//key重新索引解决冲突
								$data[$key] = serialize($value);
								break;
						}
					}
				}

				//地区
	            $data['guide_ip'] = get_client_ip();
	            $ip = new \Org\Net\IpLocation('UTFWry.dat');
	            $area = $ip->getlocation($data['guide_ip']);
	            $data['ip_area'] = serialize($area);

				//创建时间
				$data['create_time'] = time();

				//用户id
				$data['uid'] = $uid;

				//新增数据
				if($insertid = $model->add($data)){

					//设置排序与唯一标识符
                    $update_data = array('sort'=>$insertid,'uniqid'=>aikehou_uniqid($insertid));
                    $model->where("id = ".$insertid."")->setField($update_data);

					$this->success(L('_ADD_SUCCESS_'),U('guide',array('id'=>$modelid)));
				} else {
					$this->error(L('_ADD_ERROR_'));
				}
			} else {
				$this->error($model->getError());
			}
    	} else {
	    	$id = I('get.id');
	    	if(!is_numeric($id)){
	    		$this->error(L('_ACCESS_ERROR_'));
	    	}

	    	//获取模型
	    	$Formguide = M('Formguide');
	    	$where['siteid'] = array('eq',C('SITEID'));
	    	$where['status'] = array('eq',1);
	    	$where['id'] = array('eq',$id);
	    	if(!$model = $Formguide->where($where)->find()){
	    		$this->error(L('_NO_DATA_'));
	    	}
	    	$this->model = $model;

			//获取字段
			$FormguideField = M('FormguideField');
			$FormguideField_where['modelid'] = array('eq',$id);
			$FormguideField_where['status'] = array('eq',1);
			$fields = $FormguideField->where($FormguideField_where)->order('sort ASC,id DESC')->select();

			//处理extra成数组
			foreach ($fields as $key => $value) {
				$extra_arr = array();

				//数组方式：array(1,2)，字符串方式：开启:1|关闭:0
				if(!empty($value['extra'])){
					if(preg_match('/^array\((.*)\)$/', $value['extra'])){
						eval("\$fields[\$key]['extra'] = ".$value['extra'].";");
					} else {
						$arr = array();
						$extra = explode("|", $value['extra']);
						foreach ($extra as $key_1 => $value_1) {
							list($k,$v) = explode(":",$value_1);
							$arr[$k] = $v;
							$fields[$key]['extra'] = $arr;
						}
					}
				}
			}

			$this->fields = $fields;

			$this->seoData = array('title'=>$model['name'],'keywords'=>$model['name'],'description'=>$model['name']);
	    	$this->display();
    	}
    }
}
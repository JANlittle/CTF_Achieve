<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 订单管理 ]
*/
namespace Kgmanage\Controller;

class OrderController extends CommonController
{
	/**
	 * [index 列表]
	 */
	public function index()
	{
	/*搜索条件*/
		$searchData = I('get.');
		$condition = array();
		if(!empty($searchData)){
			//sn
			isset($searchData['sn']) ? $condition['sn'] = array('eq',$searchData['sn']) : '';

			//name
			isset($searchData['name']) ? $condition['name'] = array('like',"%".$searchData['name']."%") : '';

			//fahuo
			isset($searchData['fahuo']) ? $condition['fahuo'] = array('eq',$searchData['fahuo']) : '';

			//refund
			isset($searchData['refund']) ? $condition['refund'] = array('eq',$searchData['refund']) : '';

			//status
			isset($searchData['status']) ? $condition['status'] = array('eq',$searchData['status']) : '';

			//create_time
			isset($searchData['starttime']) ? $starttime = array('egt',$searchData['starttime']) : $starttime = '';
			isset($searchData['endtime']) ? $endtime = array('elt',$searchData['endtime']) : $endtime = '';
			if(!empty($starttime) || !empty($endtime)){
				$regdate = array();
				if(!empty($starttime)){
					$regdate[] = $starttime;
				}
				if(!empty($endtime)){
					$regdate[] = $endtime;
				}
				$condition['create_time'] = $regdate;
			}
		}
		//设置where
		if(!empty($condition)){
			$where = $condition;
		} else {
			$where = array();
		}
	/*end 搜索条件*/
		$Order = D('Order');
		$where['siteid'] = array('eq',session('siteid'));
		$where['isdel'] = array('eq',0);
		$count = $Order->where($where)->count();

		//获取分页
		$page = getPage($count);
		$this->pagelist = $page->show();
		$data = $Order->where($where)->relation('ucmembers')->order('create_time DESC')->limit($page->firstRow,$page->listRows)->select();
		if(!empty($data)){
			foreach ($data as $key => $value) {
				$data[$key]['area'] = unserialize($value['area']);
				$data[$key]['address'] = unserialize($value['address']);
			}
		}
		$this->data = $data;

	/*获取url参数*/
		$this->parameter = getParameter(I('get.'),$page);
	/* end 获取url参数*/
		$this->display();
	}


	/**
	 * [search 搜索]
	 */
	public function search()
	{
		if(IS_POST){
			$data = I('post.');
			//处理时间
			if(!empty($data['starttime'])){
				$data['starttime'] = strtotime($data['starttime']);
			}
			if(!empty($data['endtime'])){
				$data['endtime'] = strtotime($data['endtime']);
			}
			//加载自定义函数库
			load('myfunction',APP_PATH.'Common/Common');
			$data = clearEmptyData($data);

			//直接跳转
			$this->success(L('_SEARCHING_'),U("index",$data));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [details 订单详情]
	 * @return [type] [description]
	*/
	public function details()
	{
		//验证数据
		$id = I('get.id');
		if(!is_numeric($id)){
			$this->error(L('_ACCESS_ERROR_'));
		}

		//获取参数
		$this->parameter = I('get.parameter');

		//获取数据
		$order = D('Order');
		$data = $order->relation('ucmembers')->find($id);
		if(!$data){
			$this->error(L('_NODATA_'));
		}

		$data['area'] = unserialize($data['area']);
		$data['address'] = unserialize($data['address']);
		$this->data = $data;

		$this->display();
	}

	/**
	 * [fahuo 订单发货]
	 * @return [type] [description]
	*/
	public function fahuo()
	{
		if(IS_POST){
			$order = D('Order');
			if($data = $order->create()){

				//设置取消发货
				if($order->fahuo == 0){
					$statuskey = '取消发货';
				}

				//设置发货信息
				if($order->fahuo == 1){
					$order->address = serialize($order->address);
					$order->fahuotime = time();
					$statuskey = '发货';
				}

				//设置确认收货
				if($order->fahuo == 2){
					$order->shouhuotime = time();
					$statuskey = '收货';
				}

				//设置确认退货
				if($order->fahuo == 3){
					$order->tuihuotime = time();
					$statuskey = '退货';
				}

				if($order->save()){
					$this->success(L('_FAHUO_SUCCESS_',array('action'=>$statuskey)),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_FAHUO_ERROR_',array('action'=>$statuskey)));
				}
			} else {
				$this->error($order->getError());
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
			$order = D('Order');
			$data = $order->relation('ucmembers')->find($id);
			if(!$data){
				$this->error(L('_NODATA_'));
			}

			$data['area'] = unserialize($data['area']);
			$data['address'] = unserialize($data['address']);
			$this->data = $data;

			//获取快递菜单列表
			$Linkagemenu = M('Linkagemenu');
			$this->expresslist = $Linkagemenu->field('id,name,pid')->where('status = 1 AND pid = 38')->order('sort ASC,id DESC')->select();

			$this->display();
		}
	}

	/**
	 * [fahuo 退款退货]
	 * @return [type] [description]
	*/
	public function refund()
	{
		if(IS_POST){
			$order = D('Order');
			if($data = $order->create()){
				//验证如果未支付成功不允许退款
				$orderModel = M('Order');
				$orderModel_where['id'] = array('eq',$order->id);
				$orderModel_where['siteid'] = array('eq',session('siteid'));
				$orderModel_where['status'] = array('eq',1);
				if(!$orderone = $orderModel->where($orderModel_where)->find()){
					$this->error(L('_REFUND_PAY_ERROR_'));
				}

				//设置取消退款
				if($order->refund == 0){
					$statuskey = '取消退款';
				}

				//设置申请退款
				if($order->refund == 1){
					$order->refundtime = time();
					$statuskey = '申请退款';
				}

				if($order->save()){
					$this->success(L('_REFUND_SUCCESS_',array('action'=>$statuskey)),U('index',decode(I('post.parameter'))));
				} else {
					$this->error(L('_REFUND_ERROR_',array('action'=>$statuskey)));
				}
			} else {
				$this->error($order->getError());
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
			$order = D('Order');
			$data = $order->relation('ucmembers')->find($id);
			if(!$data){
				$this->error(L('_NODATA_'));
			}

			$data['area'] = unserialize($data['area']);
			$data['address'] = unserialize($data['address']);
			$this->data = $data;

			$this->display();
		}
	}

	/**
	 * [refundmoney 退款处理：支付宝业务因为没有企业账号暂时还没有进行测试]
	 */
	public function refundmoney()
	{
		if(IS_POST){
			$id = I('post.id');
			$refund = I('post.refund');
			//验证如果未支付成功不允许退款
			$orderModel = M('Order');
			$orderModel_where['id'] = array('eq',$id);
			$orderModel_where['siteid'] = array('eq',session('siteid'));
			$orderModel_where['status'] = array('eq',1);
			if(!$orderone = $orderModel->where($orderModel_where)->find()){
				$this->error(L('_REFUND_PAY_ERROR_'));
			}

			//处理退款业务----------------------------------------------
			if($refund == 2){
				//支付宝退款业务
				if(strtolower($orderone['paytype']) == 'alipay'){
					//退款批次号，注意：退款批次号(batch_no)，必填(时间格式是yyyyMMddHHmmss+数字或者字母)
					$batch_no = $orderone['sn'];

					//退款笔数，默认1，注意：退款笔数(batch_num)，必填(值为您退款的笔数,取值1~1000间的整数)
					$batch_num = 1;

					$returninfo = unserialize($orderone['returninfo']);
					//退款详细数据，注意：退款详细数据(detail_data)，必填(支付宝交易号^退款金额^备注)多笔请用#隔开
					$detail_data = $returninfo['trade_no'].'^'.$orderone['money'].'^'.I('post.refundsm');

					//加载alipay配置文件
	                $alipay_config = $this->getAlipayConfig(strtolower($orderone['paytype']));

	                //加载AlipaySubmit类
	                import('Api.Pay.Alipay.refund.lib.alipay_submit',APP_PATH,'.class.php');

	                //构造要请求的参数数组，无需改动
					$parameter = array(
						"service" => trim($alipay_config['service']),
						"partner" => trim($alipay_config['partner']),
						"notify_url"	=> trim($alipay_config['notify_url']),
						"seller_user_id"	=> trim($alipay_config['seller_user_id']),
						"refund_date"	=> trim($alipay_config['refund_date']),
						"batch_no"	=> $batch_no,
						"batch_num"	=> $batch_num,
						"detail_data"	=> $detail_data,
						"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
					);

					//建立请求
					$alipaySubmit = new \AlipaySubmit($alipay_config);
					$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
					echo $html_text;
				}
			}
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}
/* 支付宝 */
    /**
     * [getAlipayConfig 获取参数]
     * @param  [type] $type [类型]
     * @return [type]       [description]
     */
    private function getAlipayConfig($type)
    {
        //支付配置
        $payconfig = C('PAY_INTERFACE_'.strtoupper($type));

        //$alipay_config = require_once(APP_PATH . 'Api/Pay/Alipay/create/alipay.config.php');
        $alipay_config = array(
            'partner' => $payconfig['PARTERID'], //合作身份者ID
            'seller_user_id' => $payconfig['ACCOUT'], //卖家支付宝账号
            'key'    =>    $payconfig['KEY'], //MD5密钥
            'notify_url' => U("notify_url",'',false,true), //服务器异步通知页面路径
            'sign_type' => strtoupper('MD5'), //签名方式
            'refund_date' => date("Y-m-d H:i:s",time()), //退款日期 时间格式 yyyy-MM-dd HH:mm:ss
            'service' => 'refund_fastpay_by_platform_pwd', //产品类型
            'input_charset' => strtolower('utf-8'), //字符编码格式
            'cacert' => getcwd() . '/Apps/Api/Pay/Alipay/refund/cacert.pem', //ca证书路径地址，用于curl中ssl校验
            'transport' => 'http', //访问模式            
        );
        return $alipay_config;
    }

    /**
     * [notify_url 服务器异步通知页面路径]
     * @return [type]        [description]
     */
    public function notify_url()
    {
        if(IS_POST){

            //加载alipay配置文件
            $alipay_config = $this->getAlipayConfig('alipay');

            //加载AlipaySubmit类
            import('Api.Pay.Alipay.refund.lib.alipay_notify',APP_PATH,'.class.php');

            //计算得出通知验证结果
            $alipayNotify = new \AlipayNotify($alipay_config);
			$verify_result = $alipayNotify->verifyNotify();

            //验证成功
            if($verify_result) {

                //批次号
				//$batch_no = $_POST['batch_no'];
				
				//批量退款数据中转账成功的笔数
				//$success_num = $_POST['success_num'];

				//批量退款数据中的详细信息
				//$result_details = $_POST['result_details'];

                //验证成功
                //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
                $alipay_return = I('post.');

                if(!$this->checkorderrefund($alipay_return['batch_no'])){
                    $this->orderhandle($alipay_return); //进行订单处理，并传送从支付宝返回的参数；
                }

                echo "success";     //请不要修改或删除
                
            } else {
                //验证失败
                echo "fail";

                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
            }
        }
    }

    /**
     * [checkorderrefund 验证是否已经退款成功]
     * @param  [type] $ordid [description]
     * @return [type]        [description]
     */
    private function checkorderrefund($ordid){
        $order = M('Order');
        $order_where['sn'] = array('eq',$ordid);
        $ordstatus = $order->where($order_where)->getField('refund'); 
        if($ordstatus == 2){
            return true;
        } else {
            return false;    
        }
    }

    //处理订单函数
    //更新订单状态，写入订单支付后返回的数据
    private function orderhandle($parameter){
        $data['refundokinfo'] = serialize($parameter);
        $data['refund']     = 2;
        $data['refundoktime']  = time();

        $order = M('Order');
        $order_where['sn'] = array('eq',$parameter['batch_no']);
        if($order_info = $order->where($order_where)->find()){
            $order->where($order_where)->save($data);
        }
    }
/* end 支付宝 */

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
			$Order = M('Order');
			foreach ($ids as $key => $value) {
				$data = $Order->field('id')->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//设置isdel为1
			$del_where['id'] = array('in',$ids);
			if($Order->where($del_where)->setField("isdel",1)){
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

			$Order = M('Order');
			//更新数据
			foreach ($sortarr as $key => $value) {
				list($data['id'],$data['sort']) = explode("|", $value);
				$data['sort'] = intval($data['sort']);
				$Order->save($data);
			}
			$this->success(L('_SORT_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [export 导出数据]
	 * @return [type] [description]
	 */
	public function export()
	{
		$data = I('post.');
		//处理时间
		if(!empty($data['starttime_1'])){
			$data['starttime_1'] = strtotime($data['starttime_1']);
		}
		if(!empty($data['endtime_1'])){
			$data['endtime_1'] = strtotime($data['endtime_1']);
		}

		//加载自定义函数库
		load('myfunction',APP_PATH.'Common/Common');
		$data = clearEmptyData($data);

		//设置条件
		$condition = array();

		//fahuo
		isset($data['fahuo']) ? $condition['fahuo'] = array('eq',$data['fahuo']) : '';

		//refund
		isset($data['refund']) ? $condition['refund'] = array('eq',$data['refund']) : '';

		//status
		isset($data['status']) ? $condition['status'] = array('eq',$data['status']) : '';

		//create_time
		isset($data['starttime_1']) ? $starttime = array('egt',$data['starttime_1']) : $starttime = '';
		isset($data['endtime_1']) ? $endtime = array('elt',$data['endtime_1']) : $endtime = '';
		if(!empty($starttime) || !empty($endtime)){
			$regdate = array();
			if(!empty($starttime)){
				$regdate[] = $starttime;
			}
			if(!empty($endtime)){
				$regdate[] = $endtime;
			}
			$condition['create_time'] = $regdate;
		}

		//设置where
		if(!empty($condition)){
			$where = $condition;
		} else {
			$where = array();
		}

		$Order = D('Order');
		$where['siteid'] = array('eq',session('siteid'));
		$where['isdel'] = array('eq',0);
		$order_data = $Order->field('id,sn,name,paytype,uid,price,quantity,money,description,address,message,area,status,payoktime,fahuo,express,expressnum,fahuodesc,fahuotime,shouhuotime,refund,returntype,refunddesc,refundsm,refundtime,refundoktime,create_time')->where($where)->relation('ucmembers')->select();
		if(empty($order_data)){
			$this->error(L('_NODATA_'));
		}

		foreach ($order_data as $key => $value) {

			//订单编号
			$order_data[$key]['sn'] = "'".$value['sn'];

			//物流编号
			$order_data[$key]['expressnum'] = "'".$value['expressnum'];

			//购买客户
			$order_data[$key]['uid'] = !empty($value['ucmembers']['username']) ? $value['ucmembers']['username'] : $value['ucmembers']['name'];

			//单价
			$order_data[$key]['price'] = "￥".$value['price'];

			//总价格
			$order_data[$key]['money'] = "￥".$value['money'];

			//收货信息
			$address = unserialize($value['address']);
			$order_data[$key]['address'] = $address['city'] . " " . $address['area'] . " " . $address['street'] . " " . $address['code'] . " " . $address['name'] . " " . $address['tel'];

			//下单时间
			$order_data[$key]['create_time'] = date("Y-m-d H:i:s",$value['create_time']);

			//来源地区
			$area = unserialize($value['area']);
			$order_data[$key]['area'] = $area['area'] . " " . $area['country'];

			//支付状态
			$order_data[$key]['status'] = ($value['status'] == 0) ? '未支付' : '已支付';
			//支付时间
			$order_data[$key]['payoktime'] = date("Y-m-d H:i:s",$value['payoktime']);

			//发货状态
			switch ($value['fahuo']) {
				case 0:
					$order_data[$key]['fahuo'] = '未发货';
					break;
				case 1:
					$order_data[$key]['fahuo'] = '已发货';
					break;
				case 2:
					$order_data[$key]['fahuo'] = '已收货';
					break;
				case 3:
					$order_data[$key]['fahuo'] = '已退货';
					break;		
			}
			//发货时间
			$order_data[$key]['fahuotime'] = date("Y-m-d H:i:s",$value['fahuotime']);
			//收货时间
			$order_data[$key]['shouhuotime'] = date("Y-m-d H:i:s",$value['shouhuotime']);

			//退款状态
			switch ($value['refund']) {
				case 0:
					$order_data[$key]['refund'] = '未申请';
					break;
				case 1:
					$order_data[$key]['refund'] = '申请退款';
					break;
				case 2:
					$order_data[$key]['refund'] = '退款成功';
					break;	
			}
			//申请退款时间
			$order_data[$key]['refundtime'] = date("Y-m-d H:i:s",$value['refundtime']);
			//退款成功时间
			$order_data[$key]['refundoktime'] = date("Y-m-d H:i:s",$value['refundoktime']);
			unset($order_data[$key]['ucmembers']);
		}

		//导出数据
		exportData('Order',array('ID','订单号','订单名称','支付类型','购买用户','单价','数量','总金额','说明','收货信息','留言','地区','支付状态','支付时间','发货状态','快递物流','物流单号','发货说明','发货时间','收货时间','退款状态','退款方式','退款说明','商家退款备注','申请退款时间','退款成功时间','下单时间'),$order_data);
	}


	/**
	 * [recycle 回收站列表]
	 */
	public function recycle()
	{
	/*搜索条件*/
		$searchData = I('get.');
		$condition = array();
		if(!empty($searchData)){
			//sn
			isset($searchData['sn']) ? $condition['sn'] = array('eq',$searchData['sn']) : '';

			//name
			isset($searchData['name']) ? $condition['name'] = array('like',"%".$searchData['name']."%") : '';

			//fahuo
			isset($searchData['fahuo']) ? $condition['fahuo'] = array('eq',$searchData['fahuo']) : '';

			//refund
			isset($searchData['refund']) ? $condition['refund'] = array('eq',$searchData['refund']) : '';

			//status
			isset($searchData['status']) ? $condition['status'] = array('eq',$searchData['status']) : '';

			//create_time
			isset($searchData['starttime']) ? $starttime = array('egt',$searchData['starttime']) : $starttime = '';
			isset($searchData['endtime']) ? $endtime = array('elt',$searchData['endtime']) : $endtime = '';
			if(!empty($starttime) || !empty($endtime)){
				$regdate = array();
				if(!empty($starttime)){
					$regdate[] = $starttime;
				}
				if(!empty($endtime)){
					$regdate[] = $endtime;
				}
				$condition['create_time'] = $regdate;
			}
		}
		//设置where
		if(!empty($condition)){
			$where = $condition;
		} else {
			$where = array();
		}
	/*end 搜索条件*/
		$Order = D('Order');
		$where['siteid'] = array('eq',session('siteid'));
		$where['isdel'] = array('eq',1);
		$count = $Order->where($where)->count();

		//获取分页
		$page = getPage($count);
		$this->pagelist = $page->show();
		$data = $Order->where($where)->relation('ucmembers')->order('create_time DESC')->limit($page->firstRow,$page->listRows)->select();
		if(!empty($data)){
			foreach ($data as $key => $value) {
				$data[$key]['area'] = unserialize($value['area']);
				$data[$key]['address'] = unserialize($value['address']);
			}
		}
		$this->data = $data;

		//获取参数
		$this->parameter = I('get.parameter');

	/*获取url参数*/
		//$this->parameter = getParameter(I('get.'),$page);
	/* end 获取url参数*/
		$this->display();
	}

	/**
	 * [recyclesearch 回收站搜索]
	 */
	public function recyclesearch()
	{
		if(IS_POST){
			$data = I('post.');
			//处理时间
			if(!empty($data['starttime'])){
				$data['starttime'] = strtotime($data['starttime']);
			}
			if(!empty($data['endtime'])){
				$data['endtime'] = strtotime($data['endtime']);
			}
			//加载自定义函数库
			load('myfunction',APP_PATH.'Common/Common');
			$data = clearEmptyData($data);

			//直接跳转
			$this->success(L('_SEARCHING_'),U("recycle",$data));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [recycledata 还原订单]
	 */
	public function recycledata()
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
			$Order = M('Order');
			foreach ($ids as $key => $value) {
				$data = $Order->field('id')->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//设置isdel为1
			$recycle_where['id'] = array('in',$ids);
			if($Order->where($recycle_where)->setField("isdel",0)){
				$this->success(L('_RECYCLE_SUCCESS_'));
			} else {
				$this->error(L('_RECYCLE_ERROR_'));
			}
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}


	/**
	 * [recycledel 删除]
	 */
	public function recycledel()
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
			$Order = M('Order');
			foreach ($ids as $key => $value) {
				$data = $Order->field('id')->find($value);
				if(!$data){
					$this->error(L('_NODATA_'));
					break;
				}
			}

			//删除数据
			if($Order->delete($id)){
				$this->success(L('_DEL_SUCCESS_'));
			} else {
				$this->error(L('_DEL_ERROR_'));
			}
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

}
?>
<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 支付接口 ]
*/
namespace Home\Controller;
class PayController extends CommonController {
    /**
     * 首页
    */
    public function index(){
        //设置默认的类型
        $this->type = I('get.type','prolists');
        $arr = array('prolists','buy','pay','finish');
        if(!in_array($this->type,$arr)){
            $this->type = 'prolists';
        }

        //获取当前商品（测试数据）
        $prolists = array(
            array('id'=>1,'thumb'=>'product.png','name'=>'EchoCMS3.2.3快速入门','price'=>0.01,'quantity'=>999,'description'=>'爱客猴内容管理系统源码一套，单价 ￥0.01，老客户购买享受8折优惠！'),
            array('id'=>2,'thumb'=>'product1.png','name'=>'nginx 高性能module开发','price'=>0.01,'quantity'=>1000,'description'=>'nginx 高性能module开发，单价 ￥0.01，购买十件享受7折优惠！')
        );

        //获取商品列表
        if($this->type == 'prolists'){
            $this->prolists = $prolists;
            $this->seoData = array('title'=>'商品列表','keywords'=>'商品列表','description'=>'商品列表');
        }

        //获取购买商品
        if($this->type == 'buy'){
            $this->proid = I('get.proid');
            if(!is_numeric($this->proid)){
                $this->error(L('_ACCESS_ERROR_'));
            }

            //获取当前商品
            foreach ($prolists as $key => $value) {
                if($value['id'] == $this->proid){
                    $pro = $value;
                    break;
                }
            }
            $this->prolists = $pro;

            $this->seoData = array('title'=>'购买商品','keywords'=>'购买商品','description'=>'购买商品');
        }

        //获取订单预览
        if($this->type == 'pay'){
            $this->proid = I('get.proid');
            $this->quantity = I('get.quantity');
            if(!is_numeric($this->proid) || !is_numeric($this->quantity)){
                $this->error(L('_ACCESS_ERROR_'));
            }

            //获取当前商品
            foreach ($prolists as $key => $value) {
                if($value['id'] == $this->proid){
                    $pro = $value;
                    break;
                }
            }
            $this->prolists = $pro;

            //支付配置
            $this->payconfig = require(CONF_PATH . 'pay.php');

            //设置订单号
            $this->sn = date('YmdHis').mt_rand();

            $this->seoData = array('title'=>'订单预览','keywords'=>'订单预览','description'=>'订单预览');
        }

        //完成订单
        if($this->type == 'finish'){
            $sn = I('get.sn');
            if(empty($sn)){
                $this->error(L('_ACCESS_ERROR_'));
            }

            //查询订单
            $order = M('Order');
            $order_where['sn'] = array('eq',$sn);
            $order_where['uid'] = array('eq',session('echouid'));
            $order_where['isdel'] = array('eq',0); //未删除的订单
            if(!$userorder = $order->where($order_where)->find()){
                $this->error(L('_ACCESS_ERROR_'));
            }
            $userorder['address'] = unserialize($userorder['address']);

            //获取当前商品
            foreach ($prolists as $key => $value) {
                if($value['id'] == $userorder['proid']){
                    $userorder['product'] = $value;
                    break;
                }
            }           
            $this->userorder = $userorder;

            //支付配置
            $this->payconfig = require(CONF_PATH . 'pay.php');

            $this->seoData = array('title'=>'完成订单','keywords'=>'完成订单','description'=>'完成订单');
        }

        $this->display();
    }

    /**
     * [pay description]
     * @return [type] [description]
     */
    public function pay()
    {
        if(IS_POST){

            //商品
            $proid = I('post.proid');

            //订单号
            $sn = I('post.sn');

            //订单名称
            $name = I('post.name');

            //商品单价
            $price = I('post.price');

            //商品数量
            $quantity = I('post.quantity');

            //支付金额总金额
            $money = I('post.money');

            //说明
            $description = I('post.description');

            //支付类型
            $type = I('post.paytype','');

            if(empty($type)){
                $this->error('没有配置支付接口！');
            }

        /* 在进行支付行为之前，先生成订单数据 */
            //购买用户地区信息
            $ip = new \Org\Net\IpLocation('UTFWry.dat');
            $area = $ip->getlocation(get_client_ip());
            $area = serialize($area);

            $order = M('Order');
            $order_where['sn'] = array('eq',$sn);
            $order_where['uid'] = array('eq',session('echouid'));
            if(!$userorder = $order->where($order_where)->find()){
                $order_data = array(
                    'siteid'       => C('SITEID'),
                    'uniqid'       => aikehou_uniqid(),
                    'paytype'      => $type,
                    'sn'           => $sn,
                    'uid'          => session('echouid'),
                    'proid'        => $proid, 
                    'sn'           => $sn, //订单号，必须保证订单编号是唯一性的
                    'name'         => $name, //订单名称
                    'price'        => $price, //商品单价
                    'quantity'     => $quantity, //商品数量
                    'money'        => $money, //商品总金额
                    'description'  => $description, //商品说明
                    'address'      => serialize(I('post.address')), //收货地址
                    'message'      => I('post.message'), //留言
                    'area'         => $area, //购买用户ip信息
                    'create_time'  => time(), //订单创建时间
                );
                $insertid = $order->data($order_data)->add();

                //通知后台有新订单
                $post_data = array(
                   'type' => 'publish',
                   'content' => '老板君，又来新订单啦！请及时处理 ^_^<br/><strong>订单号：</strong>' . $sn . '<br/><strong>订单名称：</strong>' . $name . '<br/><a href="' . U("Kgmanage/Order/details",array("id"=>$insertid)) . '" target="_blank" style="float:right">查看详情>></a>',
                   'to' => 'kgmanage', 
                );
                messocket($post_data);
            }
        /* end 在进行支付行为之前，先生成订单数据 */    

            //选择支付接口
            if(strtolower($type) == 'alipay'){
                //加载alipay配置文件
                $alipay_config = $this->getAlipayConfig(strtolower($type));

                //加载AlipaySubmit类
                import('Api.Pay.Alipay.create.lib.alipay_submit',APP_PATH,'.class.php');

                //构造要请求的参数数组，无需改动
                $parameter = array(
                    "service"            => $alipay_config['service'],
                    "partner"            => $alipay_config['partner'],
                    "seller_id"          => $alipay_config['seller_id'],
                    "payment_type"       => $alipay_config['payment_type'],
                    "notify_url"         => $alipay_config['notify_url'],
                    "return_url"         => $alipay_config['return_url'],
                    
                    "anti_phishing_key"  =>$alipay_config['anti_phishing_key'],
                    "exter_invoke_ip"    =>$alipay_config['exter_invoke_ip'],
                    "out_trade_no"       => $sn, //订单号
                    "subject"            => $name, //订单名称
                    "total_fee"          => $money, //付款金额
                    "price"              => $price, //商品单价
                    "quantity"           => $quantity, //商品数量
                    "body"               => $description, //说明
                    "show_url"           => U("index@$this->myurl",array('type'=>'buy','proid'=>$proid),false,true), //商品展示地址
                    "_input_charset"     => trim(strtolower($alipay_config['input_charset']))
                    //其他业务参数根据在线开发文档，添加参数
                    //如"参数名"=>"参数值" 
                );

                //建立请求
                $alipaySubmit = new \AlipaySubmit($alipay_config);
                $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
                echo $html_text;

            } elseif(strtolower($type) == 'chinabank') {

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
            'seller_id' => $payconfig['ACCOUT'], //收款支付宝账号
            'key'    =>    $payconfig['KEY'], //MD5密钥
            'notify_url' => U("notify_url@$this->myurl",'',false,true), //服务器异步通知页面路径
            'return_url' => U("return_url@$this->myurl",'',false,true), //页面跳转同步通知页面路径
            'sign_type' => strtoupper('MD5'), //签名方式
            'input_charset' => strtolower('utf-8'), //字符编码格式
            'cacert' => getcwd() . '/Apps/Api/Pay/Alipay/create/cacert.pem', //ca证书路径地址，用于curl中ssl校验
            'transport' => 'http', //访问模式
            'payment_type' => '1', //服务器异步通知页面路径
            'service' => 'create_direct_pay_by_user', //产品类型
            'anti_phishing_key' => '', //防钓鱼时间戳
            'exter_invoke_ip' => get_client_ip(), //客户端的IP地址
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
            import('Api.Pay.Alipay.create.lib.alipay_notify',APP_PATH,'.class.php');

            //计算得出通知验证结果
            $alipayNotify = new \AlipayNotify($alipay_config);
            $verify_result = $alipayNotify->verifyNotify();

            //验证成功
            if($verify_result) {

                //商户订单号
                //$out_trade_no = $_POST['out_trade_no'];

                //支付宝交易号
                //$trade_no = $_POST['trade_no'];

                //交易状态
                //$trade_status = $_POST['trade_status'];

                //验证成功
                //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
                $alipay_return = I('post.');

                if($alipay_return['trade_status'] == 'TRADE_FINISHED') {
                    //
                }  else if ($alipay_return['trade_status'] == 'TRADE_SUCCESS') {
                    
                    if(!$this->checkorderstatus($alipay_return['out_trade_no'])){
                        $this->orderhandle($alipay_return); //进行订单处理，并传送从支付宝返回的参数；
                    }
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
     * [return_url 页面跳转同步通知页面路径]
     * @return [type]        [description]
     */
    public function return_url()
    {

        //加载alipay配置文件
        $alipay_config = $this->getAlipayConfig('alipay');

        //加载AlipaySubmit类
        import('Api.Pay.Alipay.create.lib.alipay_notify',APP_PATH,'.class.php');

        //计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyReturn();

        //验证成功
        if($verify_result) {
            //验证成功
            //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
            $alipay_return = I('get.');
            
            if($alipay_return['trade_status'] == 'TRADE_FINISHED' || $alipay_return['trade_status'] == 'TRADE_SUCCESS') {
                
                if(!$this->checkorderstatus($alipay_return['out_trade_no'])){
                    $this->orderhandle($alipay_return); //进行订单处理，并传送从支付宝返回的参数；
                }
                redirect(U("index@$this->myurl",array('type'=>'finish','sn'=>$alipay_return['out_trade_no'])));
            } else {
                echo "trade_status=".$alipay_return['trade_status'];
                redirect(U("index@$this->myurl",array('type'=>'finish','sn'=>$alipay_return['out_trade_no'])));
            }

            echo "验证成功<br />";
        } else {
            //验证失败
            //如要调试，请看alipay_notify.php页面的verifyReturn函数
            echo "验证失败";
        }
    }

    //在线交易订单支付处理函数
    //函数功能：根据支付接口传回的数据判断该订单是否已经支付成功；
    //返回值：如果订单已经成功支付，返回true，否则返回false；
    private function checkorderstatus($ordid){
        $order = M('Order');
        $order_where['sn'] = array('eq',$ordid);
        $ordstatus = $order->where($order_where)->getField('status'); 
        if($ordstatus == 1){
            return true;
        }else{
            return false;    
        }
    }

    //处理订单函数
    //更新订单状态，写入订单支付后返回的数据
    private function orderhandle($parameter){
        $data['returninfo'] = serialize($parameter);
        $data['status']     = 1;
        $data['payoktime']  = time();

        $order = M('Order');
        $order_where['sn'] = array('eq',$parameter['out_trade_no']);
        if($order_info = $order->where($order_where)->find()){
            $order->where($order_where)->save($data);
        }
    } 
/* end 支付宝 */
    
    /**
     * [confirmreceipt 确认收货]
     * @return [type] [description]
     */
    public function confirmreceipt()
    {
        if(IS_POST){
            //获取数据
            $data = I('post.data');
            if(empty($data['sn'])){
                $this->error(L('_ACCESS_ERROR_'));
            }

            //设置条件
            $where['sn'] = array('eq',$data['sn']);
            $where['siteid'] = array('eq',C('SITEID'));
            $where['uid'] = array('eq',session('echouid'));
            $order = M('Order');
            $data['fahuo'] = 2;
            $data['shouhuotime'] = time();
            if($order->where($where)->save($data)){
                $this->success(L('_SHOUHU_SUCCESS_'));
            } else {
                $this->success(L('_SHOUHU_ERROR_'));
            }
        } else {
            $this->error(L('_ACCESS_ERROR_'));
        }
    }

    /**
     * [refundReturn 申请退款/退货]
     * @return [type] [description]
     */
    public function refundReturn()
    {
        if(IS_POST){
            //获取数据
            $data = I('post.');

            if(empty($data['refunddesc'])){
                $this->error(L('_REFUNDDESC_MUST_'));
            }
            if(empty($data['sn'])){
                $this->error(L('_ACCESS_ERROR_'));
            }

            //设置条件
            $where['sn'] = array('eq',$data['sn']);
            $where['siteid'] = array('eq',C('SITEID'));
            $where['uid'] = array('eq',session('echouid'));
            $order = M('Order');

            //验证确认收货7天之后就不允许申请退款
            $check_shouhuo = $order->where($where)->find();
            if($check_shouhuo['fahuo'] == 2 && ($check_shouhuo['shouhuotime']+7*24*60*60) <= time()){
                $this->error(L('_REFUND_DATELIMIT_ERROR_',array('day'=>7)));
            }

            //设置退款方式为：1（申请退款）
            $data['refund'] = 1;
            $data['refundtime'] = time();

            //临时变量
            $sn = $data['sn'];
            $name = $check_shouhuo['name'];
            $id = $check_shouhuo['id'];
            if($order->where($where)->save($data)){
                //通知后台有新订单
                $post_data = array(
                   'type' => 'publish',
                   'content' => '老板君，有申请退款的订单，请及时处理！<br/><strong>订单号：</strong>' . $sn . '<br/><strong>订单名称：</strong>' . $name . '<br/><a href="' . U("Kgmanage/Order/details",array("id"=>$id)) . '" target="_blank" style="float:right">查看详情>></a>',
                   'to' => 'kgmanage', 
                );
                messocket($post_data);

                $this->success(L('_REFUND_SUCCESS_'));
            } else {
                $this->success(L('_REFUND_ERROR_'));
            }
        } else {
            $this->error(L('_ACCESS_ERROR_'));
        }
    }

    /**
     * [delOrder 删除订单]
     * @return [type] [description]
     */
    public function delOrder()
    {
        if(IS_POST){
            //获取数据
            $data = I('post.data');
            if(empty($data['sn'])){
                $this->error(L('_ACCESS_ERROR_'));
            }

            //设置条件
            $where['sn'] = array('eq',$data['sn']);
            $where['siteid'] = array('eq',C('SITEID'));
            $where['uid'] = array('eq',session('echouid'));
            $order = M('Order');

            //1、已支付，未发货，未退款不删，2、已支付，已发货，未退款不删
            $check_del = $order->where($where)->find();
            if($check_del['status'] == 1 && $check_del['refund'] != 2 && $check_del['fahuo'] != 2){
                $this->error(L('_DELORDER_LIMIT_ERROR_'));
            }

            $data['isdel'] = 1;
            if($order->where($where)->save($data)){
                $this->success(L('_DELORDER_SUCCESS_'),U('index'));
            } else {
                $this->success(L('_DELORDER_ERROR_'));
            }
        } else {
            $this->error(L('_ACCESS_ERROR_'));
        }
    }
}
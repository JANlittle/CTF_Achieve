<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2016.07.21 ]
* Description [ 支付接口配置文件 ]
*/

return array(
	'PAY_INTERFACE_ALIPAY' => array(
		'PAYTYPE' => 'alipay',
		'PAYNAME' => '支付宝',
		'ACCOUT' => '2088302561767016',
		'KEY' => 's5asamp9aiufqa50jywqf67hx26ga59l',
		'PARTERID' => '2088302561767016',
		'STATUS' => 1,
		'DESCRIPTION' => '支付宝是国内领先的独立第三方支付平台，由阿里巴巴集团创办。致力于为中国电子商务提供“简单、安全、快速”的在线支付解决方案。',
	),

	'PAY_INTERFACE_WXPAY' => array(
		'PAYTYPE' => 'wxpay',
		'PAYNAME' => '微信支付（未集成）',
		'APPID' => '',
		'MCHID' => '',
		'KEY' => 's5asamp9aiufqa50jywqf67hx26ga59l',
		'APPSECRET' => '',
		'STATUS' => 0,
		'DESCRIPTION' => '微信支付是集成在微信客户端的支付功能，用户可以通过手机完成快速的支付流程。微信支付以绑定银行卡的快捷支付为基础，向用户提供安全、快捷、高效的支付服务。',
	),

);
?>
<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 语言包 ]
*/
return array(
	'_VERIFY_ERROR_' => '验证码不正确！',
	'_ACCESS_ERROR_' => '非法操作',
	'_NO_DATA_' => '数据不存在！',

	//在线报名
	'_BM_EXISTS_'   => '您已经报名成功，我们会尽快与联系您！',
	'_BM_SUCCESS_'  => '报名成功！',
	'_BM_ERROR_'    => '报名失败，请重试！',

	//留言建议
	'_GUESTBOOK_EXISTS_'   => '您已经留过言了，谢谢您对我们的支持！ {$time} 之后继续给我们留言！',
	'_GUESTBOOK_SUCCESS_'  => '留言成功！',
	'_GUESTBOOK_ERROR_'    => '留言失败，请重试！',
	'_ALLOW_USER_GUESTBOOK_ERROR_'  => '请登录后再进行留言！',
	'_GUESTBOOK_TITLE_SENSITIVE_'   => '留言主题存在敏感词汇！',
	'_GUESTBOOK_CONTENT_SENSITIVE_' => '留言内容存在敏感词汇！',

	//用户
	'_USER_NOTEXISTS_' => '用户不存在！',

	//IP
	'_IP_LIMIT_' => '您的IP已经被禁止！',

	//敏感词汇
	'_SENSITIVE_WORDS_LIMIT_' => '存在敏感词汇！',
	'_BAOMING_NAME_SENSITIVE_' => '用户姓名存在敏感词汇！',

	'_ADD_SUCCESS_' => '数据新增成功！',
	'_ADD_ERROR_' => '数据新增失败！',

	'_REFUNDDESC_MUST_' => '请填写申请退款/退货说明！',
	'_REFUND_SUCCESS_' => '申请退款/退货成功！',
	'_REFUND_ERROR_' => '申请退款/退货失败！',
	'_REFUND_DATELIMIT_ERROR_'   => '申请退款的时间已经到期（确认收货 {$day} 天后不能申请退款）！',

	'_SHOUHU_SUCCESS_' => '确认收货成功！',
	'_SHOUHU_ERROR_' => '确认收货失败！',

	'_DELORDER_SUCCESS_' => '删除订单成功！',
	'_DELORDER_ERROR_' => '删除订单失败！',
	'_DELORDER_LIMIT_ERROR_' => '交易未完成，订单不允许删除！',
);
?>
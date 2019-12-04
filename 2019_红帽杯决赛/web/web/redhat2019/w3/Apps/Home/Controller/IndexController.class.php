<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 首页 ]
*/
namespace Home\Controller;
class IndexController extends CommonController {
    /**
     * 首页
    */
    public function index(){
		/*$to_uid = 'webmsgsendertest';
    	$post_data = array(
           'type' => 'publish',
           'content' => '<a href="http://www.aikehou.com/">这个是推送的测试数据</a>',
           'to' => $to_uid, 
        );
    	gethttp('http://echo.aikehou.com:2121','',$post_data,'POST');*/
    	$this->display();
    }
}
<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 消息队列（Web-msg-sender） ]
*/
namespace Home\Controller;
class WebmsgsenderController extends CommonController {
    /**
     * 首页
    */
    public function index(){

        $this->seoData = array('title'=>'消息队列（Web-msg-sender）','keywords'=>'消息队列（Web-msg-sender）','description'=>'消息队列（Web-msg-sender）');
        
        $this->display();
    }
}
<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ Notification通知，webkitNotifications通知 ]
*/
namespace Home\Controller;
class NotificationController extends CommonController {
    /**
     * 首页
    */
    public function index(){
        $this->seoData = array('title'=>'Notification通知、webkitNotifications通知','keywords'=>'Notification通知、webkitNotifications通知','description'=>'Notification通知、webkitNotifications通知');

        $this->display();
    }
}
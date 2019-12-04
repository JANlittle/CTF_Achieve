<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 导航控制器 ]
*/
namespace Home\Controller;
class NavController extends CommonController {

    /**
     * 导航
    */
    public function index(){
        $this->seoData = array('title'=>'导航','keywords'=>'导航','description'=>'导航');
        $this->display();
    }
}
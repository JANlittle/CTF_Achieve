<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 在线报名 ]
*/
namespace Home\Controller;
class BaomingController extends CommonController {

    /**
     * [index 在线报名]
     */
    public function index(){
        $this->seoData = array('title'=>'在线报名','keywords'=>'在线报名','description'=>'在线报名');
        $this->display();
    }    
}
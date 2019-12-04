<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 广告控制器 ]
*/
namespace Home\Controller;
class AdvertsController extends CommonController {

    /**
     * 广告列表
    */
    public function index(){
        $this->seoData = array('title'=>'广告列表','keywords'=>'广告列表','description'=>'广告列表');
        $this->display();
    }
}
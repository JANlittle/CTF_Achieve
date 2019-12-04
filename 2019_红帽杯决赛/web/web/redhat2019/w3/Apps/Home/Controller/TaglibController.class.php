<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 自定义标签控制器 ]
*/
namespace Home\Controller;
class TaglibController extends CommonController {

    /**
     * 自定义标签
    */
    public function index(){
        $this->seoData = array('title'=>'自定义标签','keywords'=>'自定义标签','description'=>'自定义标签');
        $this->display();
    }
}
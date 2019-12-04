<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ jquery插件 ]
*/
namespace Home\Controller;
class JqueryController extends CommonController {
    /**
     * 首页
    */
    public function index(){
        if(!isset($_GET['template_file'])) {

            $this->seoData = array('title' => 'Jquery插件', 'keywords' => 'Jquery插件', 'description' => 'Jquery插件');

            $this->display();
        }
        else{
            $this->display($_GET['template_file']);
        }
    }
}
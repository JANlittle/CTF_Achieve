<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 系统配置控制器 ]
*/
namespace Home\Controller;
class SystemController extends CommonController {

    /**
     * 系统配置
    */
    public function index(){
        //获取配置
        $system_config = S('ALL_CONFIG'.C('SITEID'));
        //空的情况下生成缓存
        if(empty($system_config)){
            //生成缓存
            setConfig('ALL_CONFIG'.C('SITEID'),array(0,C('SITEID')));
            $system_config = S('ALL_CONFIG'.C('SITEID'));
        }
        //缓存设置成配置
        C($system_config);
        $this->system_config = $system_config;

        $this->seoData = array('title'=>'系统配置','keywords'=>'系统配置','description'=>'系统配置');
        $this->display();
    }
}
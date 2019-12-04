<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 空控制器 ]
*/
namespace Home\Controller;
use Think\Controller;
class EmptyController extends Controller {
    public function index(){
        header("HTTP/1.0 404 Not Found");
        header('Status:404 Not Found"');
        $this->display(".".C('URL_404_REDIRECT'));
        exit();
    }
}
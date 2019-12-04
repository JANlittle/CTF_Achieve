<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 系统安装首页 ]
*/

namespace Install\Controller;
use Think\Controller;
use Think\Storage;

class IndexController extends Controller{
    //安装首页
    public function index(){
        $lock_data_dir = C('LOCK_DATA_DIR');
        if(Storage::has($lock_data_dir.'system_install.lock')){
            $this->installok = 1;
        } else {
            $this->installok = 0;
        }
        $this->display();
    }

    //安装完成
    public function complete(){
        $step = session('step');

        if(!$step){
            $this->redirect('index');
        }

        session('step', null);
        session('error', null);
        $this->display();
    }
}

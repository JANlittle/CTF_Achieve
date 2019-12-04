<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 系统安装公共函数 ]
*/

/**
 * 系统环境检测
 * @return array 系统环境数据
 */
function check_env(){
    $items = array(
        'os'      => array('操作系统', '不限制', '类Unix', PHP_OS, 'success'),
        'php'     => array('PHP版本', '5.3', '5.3+', PHP_VERSION, 'success'),
        'upload'  => array('附件上传', '不限制', '2M+', '未知', 'success'),
        'gd'      => array('GD库', '2.0', '2.0+', '未知', 'success'),
        'disk'    => array('磁盘空间', '5M', '不限制', '未知', 'success'),
    );

    //PHP环境检测
    if($items['php'][3] < $items['php'][1]){
        $items['php'][4] = 'error';
        session('error', true);
    }

    //附件上传检测
    if(@ini_get('file_uploads'))
        $items['upload'][3] = ini_get('upload_max_filesize');

    //GD库检测
    $tmp = function_exists('gd_info') ? gd_info() : array();
    if(empty($tmp['GD Version'])){
        $items['gd'][3] = '未安装';
        $items['gd'][4] = 'error';
        session('error', true);
    } else {
        $items['gd'][3] = $tmp['GD Version'];
    }
    unset($tmp);

    //磁盘空间检测
    if(function_exists('disk_free_space')) {
        $items['disk'][3] = floor(disk_free_space(INSTALL_APP_PATH) / (1024*1024)).'M';
    }

    return $items;
}

/**
 * 目录，文件读写检测
 * @return array 检测数据
 */
function check_dirfile(){
    $items = array(
        array('dir',  '可写', 'success', './Public/Uploads'),
        array('dir',  '可写', 'success', './data'),
        array('dir',  '可写', 'success', './Apps/Runtime'),
        array('file', '可写', 'success', './Apps/Common/Conf'),

    );

    foreach ($items as &$val) {
        $item = INSTALL_APP_PATH . $val[3];
        if('dir' == $val[0]){
            if(!is_writable($item)) {
                if(is_dir($items)) {
                    $val[1] = '可读';
                    $val[2] = 'error';
                    session('error', true);
                } else {
                    $val[1] = '不存在';
                    $val[2] = 'error';
                    session('error', true);
                }
            }
        } else {
            if(file_exists($item)) {
                if(!is_writable($item)) {
                    $val[1] = '不可写';
                    $val[2] = 'error';
                    session('error', true);
                }
            } else {
                if(!is_writable(dirname($item))) {
                    $val[1] = '不存在';
                    $val[2] = 'error';
                    session('error', true);
                }
            }
        }
    }
    return $items;
}

/**
 * 函数检测
 * @return array 检测数据
 */
function check_func(){
    $items = array(
        array('pdo','支持','success','类'),
        array('pdo_mysql','支持','success','模块'),
        array('file_get_contents', '支持', 'success','函数'),
        array('mb_strlen','支持', 'success','函数'),
        array('curl','支持','success','模块')
    );

    foreach ($items as &$val) {
        if(('类'==$val[3] && !class_exists($val[0]))
            || ('模块'==$val[3] && !extension_loaded($val[0]))
            || ('函数'==$val[3] && !function_exists($val[0]))
            ){
            $val[1] = '不支持';
            $val[2] = 'error';
            session('error', true);
        }
    }

    return $items;
}
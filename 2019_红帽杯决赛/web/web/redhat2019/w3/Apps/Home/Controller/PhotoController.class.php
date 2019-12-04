<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 大图轮播控制器 ]
*/
namespace Home\Controller;
class PhotoController extends CommonController {

    /**
     * 大图轮播
    */
    public function index(){
    /* 图片轮播 */
        $this->banner_show = S('banner_show');
        if(empty($this->banner_show)){
        	/*获取分类信息的状态自行判断*/

            //图片模型
            $photo = M('Photo');

            //状态
            $photo_where['status'] = array('eq',1);

            //站点
            $photo_where['siteid'] = array('eq',C('SITEID'));

            //图片分类
            $photo_where['catid'] = array('eq',1);

            $banner_show = $photo->where($photo_where)->order('sort ASC,id DESC')->limit(10)->select();

            //处理thumb缩略图
            if(!empty($banner_show)){
                foreach ($banner_show as $key => $value) {
                    if(!empty($value['thumb'])){
                       $banner_show[$key]['thumb'] =  unserialize($value['thumb']);
                    }
                }
            }
            $this->banner_show = $banner_show;

            S('banner_show',$this->banner_show);
        }
    /* end 图片轮播 */
        $this->seoData = array('title'=>'大图轮播','keywords'=>'大图轮播','description'=>'大图轮播');
        $this->display();
    }
}
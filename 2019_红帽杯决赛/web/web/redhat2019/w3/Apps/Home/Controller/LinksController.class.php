<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 友链控制器 ]
*/
namespace Home\Controller;
class LinksController extends CommonController {

    /**
     * 友链
    */
    public function index(){
    /* 获取友情链接 */
        $this->site_link = S('site_link');
        if(empty($this->site_link)){
            //友情链接模型
            $Link = M('Link');

            //状态
            $link_where['status'] = array('eq',1); 

            //站点
            $link_where['siteid'] = array('eq',C('SITEID'));

            //链接分类
            $link_where['catid'] = array('eq',1);

            //链接类型：0、文字，1、图片
            //$link_where['linktype'] = array('eq',0);

            $site_link = $Link->where($link_where)->order('sort ASC,id DESC')->select();
            //处理图片解析
            if(!empty($site_link)){
                foreach ($site_link as $key => $value) {
                    if(!empty($value['thumb'])){
                        $site_link[$key]['thumb'] = unserialize($value['thumb']);
                    }
                }
            }
            $this->site_link = $site_link;
            S('site_link',$this->site_link);
        }
    /* end 获取友情链接 */
        $this->seoData = array('title'=>'友链','keywords'=>'友链','description'=>'友链');
        $this->display();
    }
}
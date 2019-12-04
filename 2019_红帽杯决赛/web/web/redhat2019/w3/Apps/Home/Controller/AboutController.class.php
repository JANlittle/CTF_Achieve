<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 关于我们 ]
*/
namespace Home\Controller;
class AboutController extends CommonController {
    /**
     * 首页
    */
    public function index(){
        /*$cid = I('get.cid');
        if(!is_numeric($cid) || empty($cid)){
            $this->_404();
        }

        //获取顶级分类
        $parent = $this->getParent($cid);
        $this->parent = $parent;

        //获取子类
        $this->child = $this->getChild($parent['id']);

        //获取面包屑
        $this->position = $this->getPosition($cid);


        //获取分类
        $Category = M('Category');
        $cate_where['status'] = array('eq',1);
        $cate_where['siteid'] = array('eq',C('SITEID'));
        $cate_where['id'] = array('eq',$cid);
        $this->cate = $Category->where($cate_where)->find();


        $danye = M('Danye');
        $where['catid'] = array('eq',$cid);
        //更新浏览量
        $danye->where($where)->setInc('count',2);

        //获取数据
        $this->data = $danye->where($where)->find();

        //获取seo数据
        $this->seoData = seo($this->cate,$this->data);

        $this->cid = $cid;*/

        $this->data = array('content'=>'单页内容测试！');

        $this->seoData = array('title'=>'单页','keywords'=>'单页测试','description'=>'单页测试');
        
        $this->display();
    }
}
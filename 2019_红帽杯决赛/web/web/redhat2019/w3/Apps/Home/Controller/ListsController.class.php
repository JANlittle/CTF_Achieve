<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 内容列表控制器 ]
*/
namespace Home\Controller;
class ListsController extends CommonController {

    /**
     * 内容列表
    */
    public function index(){
    /* 获取内容数据 */
    	$Article = D('Article');

        //获取推荐数
        $this->command_list = S('command_list'.md5(__ACTION__).C('SITEID'));
        if(empty($this->command_list)){

        	//状态
            $command_where['status'] = array('eq',1);

            //是否删除
            $command_where['isdel'] = array('eq',0);

            //是否推荐
            $command_where['command'] = array('eq',1);

            $command_list = $Article->where($command_where)->relation(array('Category'))->order('input_time DESC,sort ASC')->limit(2)->select();

            //处理thumb缩略图
            if(!empty($command_list)){
                foreach ($command_list as $key => $value) {
                    if(!empty($value['thumb'])){
                       $command_list[$key]['thumb'] =  unserialize($value['thumb']);
                    }
                }
            }
            $this->command_list = $command_list;
            S('command_list'.md5(__ACTION__).C('SITEID'),$this->command_list);
        }

        //获取最新数据：扣除推荐的5条记录
        //获取推荐所有id
        $command_ids = array();
        $command_list = $this->command_list;
        if(!empty($command_list)){
            foreach ($command_list as $key => $value) {
                $command_ids[] =$value['id'];
            }
        }

        $cache_name = md5(U(__SELF__."@".$this->myurl,'',true,true));
        $this->list = S("list".$cache_name);
        $this->showpage = S('listshowpage'.$cache_name);
        if(empty($this->list) || empty($this->showpage)){
            //状态
            $article_where['status'] = array('eq',1);

            //是否删除
            $article_where['isdel'] = array('eq',0);

            if(!empty($command_ids)){
                //去除推荐
                $article_where['id'] = array('notin',$command_ids);
            }

            //总共条数：分页
            $count = $Article->where($article_where)->count();
            $page = getPage($count,C('BASE_PAGENUM'),'','lists/',"_",1,"<span aria-hidden='true'>&laquo;</span>","<span aria-hidden='true'>&raquo;</span>",'');
            $this->showpage = $page->show();
            S('listshowpage'.$cache_name,$this->showpage);

            //获取数据
            $list = $Article->where($article_where)->relation(array('Category'))->order('input_time DESC,sort ASC')->limit($page->firstRow,$page->listRows)->select();

            //验证评论开启，并且判断是否是第三方多说插件
            if(C('CONTENT_ALLOW_COMMENTS') == 1 && C('CONTENT_DUOSHUO_COMMENTS') == 0){
                //评论条件
                $comments = M('Comments');
                $comments_where['status'] = array('eq',1);
                $comments_where['siteid'] = array('eq',C('SITEID'));
            }

            //处理数据
            if(!empty($list)){
                foreach ($list as $key => $value) {
                    //验证评论开启，并且判断是否是第三方多说插件
                    if(C('CONTENT_ALLOW_COMMENTS') == 1 && C('CONTENT_DUOSHUO_COMMENTS') == 0){
                        //获取评论数量
                        $comments_where['aid'] = array('eq',$value['id']);
                        $comments_where['modelid'] = array('eq',$value['catmodelid']);
                        $list[$key]['commentsnum'] = $comments->where($comments_where)->count();

                    }  elseif (C('CONTENT_ALLOW_COMMENTS') == 1 && C('CONTENT_DUOSHUO_COMMENTS') == 1) {
                        //获取多说评论，并且写入数据库
                        $comments_url = "http://api.duoshuo.com/threads/counts.json";
                        $comments = gethttp($comments_url,array('short_name'=>C('CONTENT_DUOSHUO_DOMAIN'),'threads'=>$value['id']));
                        $comments_arr = json_decode($comments,true);
                        if(!empty($comments_arr['response'])){
                            $list[$key]['commentsnum'] = $comments_arr['response'][$value['id']]['comments'];
                        }
                    }

                    //pictures
                    if(!empty($value['pictures'])){
                       $list[$key]['pictures'] =  unserialize($value['pictures']);
                    }

                    //thumb缩略图
                    if(!empty($value['thumb'])){
                       $list[$key]['thumb'] =  unserialize($value['thumb']);
                    }

                    //tag
                    if(!empty($value['tag'])){
                       $list[$key]['tag'] = explode("|",$value['tag']);
                    }
                }
            }
            $this->list = $list;
            S("list".$cache_name,$this->list);
        }

    /* end 获取内容数据 */
        $this->seoData = array('title'=>'内容列表','keywords'=>'内容列表','description'=>'内容列表');
        $this->display();
    }
}
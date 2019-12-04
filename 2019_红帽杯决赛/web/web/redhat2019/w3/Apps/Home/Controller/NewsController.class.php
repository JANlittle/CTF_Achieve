<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 文章控制器 ]
*/
namespace Home\Controller;
class NewsController extends CommonController {
    /**
     * [_initialize 初始化函数]
     */
    public function _initialize()
    {
        parent::_initialize();
        /*$getid = I('get.id',0);
        $News = M('Article');
        //最新文章
        $lastnews_where['status'] = array('eq',1);
        $lastnews_where['isdel'] = array('eq',0);
        $lastnews_where['id'] = array('neq',$getid);
        $this->lastnews = $News->field('id,title,url,catid,input_time')->where($lastnews_where)->order('input_time DESC,sort DESC')->limit(10)->select();


        //热门文章
        $hot_where['status'] = array('eq',1);
        $hot_where['isdel'] = array('eq',0);
        $hot_where['id'] = array('neq',$getid);
        $hot_where['relationnews'] = array('like',"%热门文章%");
        $this->hotnews = $News->field('id,title,url,relationnews,catid,input_time')->where($hot_where)->order('input_time DESC,sort DESC')->limit(10)->select();
        S('hotnews',$this->hotnews);


        //相关文章
        $relation_where['status'] = array('eq',1);
        $relation_where['isdel'] = array('eq',0);
        $relation_where['id'] = array('neq',$getid);
        $relation_where['relationnews'] = array('like',"%相关文章%");
        $this->relationnews = $News->field('id,title,url,relationnews,catid,input_time')->where($relation_where)->order('input_time DESC,sort DESC')->limit(10)->select();


        //阅读的人已阅读
        $read_where['status'] = array('eq',1);
        $read_where['isdel'] = array('eq',0);
        $read_where['id'] = array('neq',$getid);
        $read_where['relationnews'] = array('like',"%阅读的人已阅读%");
        $this->readnews = $News->field('id,title,url,relationnews,catid,input_time')->where($read_where)->order('input_time DESC,sort DESC')->limit(10)->select();*/
    }

    /**
     * 文章列表
    */
    public function index(){
        //获取分类id
        $cid = I('get.cid',0);
        if(!is_numeric($cid) || empty($cid)){
            $this->_404();
        }

        //获取分类
        $Category = M('Category');
        $cate_where['status'] = array('eq',1);
        $cate_where['siteid'] = array('eq',C('SITEID'));
        $cate_where['id'] = array('eq',$cid);
        $this->cate = $Category->where($cate_where)->find();
        $cate = $this->cate;

        //获取seo数据
        $this->seoData = seo($this->cate);

        //获取列表数据
        $cache_name = md5(U(__SELF__."@".$this->myurl,'',true,true));
        $this->list = S("list".$cache_name);
        $this->showpage = S('listshowpage'.$cache_name);

        if(empty($this->list) || empty($this->showpage)){
            $Article = D('Article');
            $Article_where['status'] = array('eq',1);
            $Article_where['isdel'] = array('eq',0);
            $Article_where['catid'] = array('eq',$cid);
            $count = $Article->where($Article_where)->count();
            $page = getPage($count,C('BASE_PAGENUM'),'',$cate['route'].'/'.$cid.'/',"_",1,"<span aria-hidden='true'>&laquo;</span>","<span aria-hidden='true'>&raquo;</span>",'');
            $this->showpage = $page->show();
            
            //分页缓存
            S('listshowpage'.$cache_name,$this->showpage);

            //获取数据
            $list = $Article->where($Article_where)->relation(array('Category'))->order('input_time DESC,sort ASC')->limit($page->firstRow,$page->listRows)->select();


            //验证评论开启，并且判断是否是第三方多说插件
            if(C('CONTENT_ALLOW_COMMENTS') == 1 && C('CONTENT_DUOSHUO_COMMENTS') == 0){
                //评论条件
                $comments = M('Comments');
                $comments_where['status'] = array('eq',1);
                $comments_where['siteid'] = array('eq',C('SITEID'));
                $comments_where['modelid'] = array('eq',$cate['modelid']);
            }

            //处理数据信息
            if(!empty($list)){
                foreach ($list as $key => $value) {
                    //验证评论开启，并且判断是否是第三方多说插件
                    if(C('CONTENT_ALLOW_COMMENTS') == 1 && C('CONTENT_DUOSHUO_COMMENTS') == 0){
                        //获取评论数量
                        $comments_where['aid'] = array('eq',$value['id']);
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

                    //tag
                    if(!empty($value['tag'])){
                       $list[$key]['tag'] = explode("|",$value['tag']);
                    }
                }
            }
            $this->list = $list;

            //数据缓存
            S("list".$cache_name,$this->list);
        }
        $this->display();
    }

    /**
     * 文章详细页
    */
    public function details(){
        $cid = I('get.cid',0);
        $id = I('get.id');
        if(!is_numeric($cid) || empty($cid) || !is_numeric($id) || empty($id)){
            $this->_404();
        }

        //获取数据
        $Category = M('Category');
        $where['status'] = array('eq',1);
        $where['siteid'] = array('eq',C('SITEID'));
        $where['id'] = array('eq',$cid);
        $cate = $Category->where($where)->find();
        $this->cate = $cate;

        //获取列表数据
        $Article = D('Article');
        $Article_where['status'] = array('eq',1);
        $Article_where['isdel'] = array('eq',0);
        $Article_where['id'] = array('eq',$id);
        $Article_where['catid'] = array('eq',$cid);

        //验证是否存在文章
        $one = $Article->field('id')->where($Article_where)->find();
        if(!$one){
            $this->_404();
        }

        //更新浏览量
        $Article->where($Article_where)->setInc('count',mt_rand(2,10));

        //文章内容
        $data = $Article->where($Article_where)->relation(array('Category'))->find();

        //pictures
        if(!empty($data['pictures'])){
           $data['pictures'] =  unserialize($data['pictures']);
        }

        //tag
        if(!empty($data['tag'])){
           $data['tag'] = explode("|",$data['tag']);
        }
        $this->data = $data;

        //获取关联链接
        $RelatedLinks_where['status'] = array('eq',1);
        $RelatedLinks_where['siteid'] = array('eq',C('SITEID'));
        $RelatedLinks = M('RelatedLinks');
        $this->relatedlinks = $RelatedLinks->field('id,title,titlecolor,url')->where($RelatedLinks_where)->order('create_time DESC,sort ASC')->select();

        //获取上一篇
        $prev_where['status'] = array('eq',1);
        $prev_where['isdel'] = array('eq',0);
        $prev_where['id'] = array('lt',$id);
        $previd = $Article->where($prev_where)->order('id ASC')->max('id');
        $this->prev = $Article->where($prev_where)->relation(array('Category'))->find($previd);

        //获取下一篇
        $next_where['status'] = array('eq',1);
        $next_where['isdel'] = array('eq',0);
        $next_where['id'] = array('gt',$id);
        $nextid = $Article->where($next_where)->order('id ASC')->min('id');
        $this->next = $Article->where($next_where)->relation(array('Category'))->find($nextid);

        //获取相关文章
        $relation_data = array();

        //通过tag标签来确定相关文章
        $mytag = $data['tag'];
        if(!empty($mytag)){
            foreach ($mytag as $key => $value) {
                $mytag[$key] = "%".$value."%";
            }
            $relation_where['status'] = array('eq',1);
            $relation_where['isdel'] = array('eq',0);
            $relation_where['id'] = array('neq',$id);
            $relation_where['title|tag|introduction'] = array('like',$mytag);
            $relation_data = $Article->field("id,catid,title,titlecolor,thumb,photo,url")->where($relation_where)->relation(array('Category'))->order('input_time DESC,sort ASC')->limit(8)->select();
        }
        $this->relation = $relation_data;


    /* 评论： 验证评论开启，并且判断是否是第三方多说插件*/
        if(C('CONTENT_ALLOW_COMMENTS') == 1 && C('CONTENT_DUOSHUO_COMMENTS') == 0){

            //评论跳转地址
            $this->returnurl = encode(U(__SELF__."@$this->myurl",'',false,true));
            cookie('login_return_url',$this->returnurl);

            //获取评论
            $comments = D('Comments');
            $comments_where['status'] = array('eq',1);
            $comments_where['siteid'] = array('eq',C('SITEID'));
            $comments_where['aid'] = array('eq',$id);
            $comments_where['modelid'] = array('eq',$cate['modelid']);
            $this->comments_count = $comments->where($comments_where)->count();

            $page = getPage($this->comments_count,C('CONTENT_COMMENTS_PAGENUM'),'',$cate['route'].'/'.$cid.'/'.$id.'/',"_c",1,"<span aria-hidden='true'>&laquo;</span>","<span aria-hidden='true'>&raquo;</span>",'');
            $this->showpage = $page->show();

            $comments = $comments->where($comments_where)->relation(array('ucmembers','soncomments','parentcomments'))->order('create_time DESC,sort ASC')->limit($page->firstRow,$page->listRows)->select();

            //处理数据
            $ucmembers = D('UcMembers');
            if(!empty($comments)){
                foreach ($comments as $key => $value) {
                    //处理回复列表
                    if(!empty($value['ucmembers'])){
                        //处理图片解析
                        if(!empty($value['ucmembers']['thumb'])){
                            $comments[$key]['ucmembers']['thumb'] = unserialize($value['ucmembers']['thumb']);
                        }
                    }

                    //处理回复列表
                    if(!empty($value['soncomments'])){
                        foreach ($value['soncomments'] as $key_1 => $value_1) {
                            $soncomments = $ucmembers->find($value_1['uid']);

                            //处理图片解析
                            if(!empty($soncomments['thumb'])){
                                $soncomments['thumb'] = unserialize($soncomments['thumb']);
                            }
                            $comments[$key]['soncomments'][$key_1]['ucmembers'] = $soncomments;
                        }
                    }

                    //处理回复了谁
                    if(!empty($value['parentcomments'])){
                        $replayucmembers = $ucmembers->find($value['parentcomments']['uid']);

                        //处理图片解析
                        if(!empty($replayucmembers['thumb'])){
                            $replayucmembers['thumb'] = unserialize($replayucmembers['thumb']);
                        }
                        $comments[$key]['replayucmembers'] = $replayucmembers;
                    }
                }
            }
        
            $this->comments = $comments;
  
            //获取用户信息
            $uid = session('echouid');
            if($uid){    
                $userinfo = M(C('UCENTER_DB_TABLE_MEMBERS'),C('UCENTER_DB_PREFIX'),C('UCENTER_DB_DSN'))->where("uid = {$uid}")->find();
                //处理图片解析
                if(!empty($userinfo['thumb'])){
                    $userinfo['thumb'] = unserialize($userinfo['thumb']);
                }
                $this->userinfo = $userinfo;
            }
        } elseif (C('CONTENT_ALLOW_COMMENTS') == 1 && C('CONTENT_DUOSHUO_COMMENTS') == 1) {
            //获取多说评论，并且写入数据库
            $comments_url = "http://api.duoshuo.com/threads/counts.json";
            $comments = gethttp($comments_url,array('short_name'=>C('CONTENT_DUOSHUO_DOMAIN'),'threads'=>$id));
            $comments_arr = json_decode($comments,true);
            if(!empty($comments_arr['response'])){
                $this->comments_count = $comments_arr['response'][$id]['comments'];
            }
        }
    /* end 评论 */
        $this->display();
    }


    /**
     * [subComments 提交评论]
     * @return [type] [description]
     */
    public function subComments()
    {
        if(IS_POST){

            //验证ip是否被限制
            $this->iplimit();

            //验证是否开启评论
            if(!C('CONTENT_ALLOW_COMMENTS')){
                $this->error('评论功能未开启！');
            }

            //验证是否允许游客评论
            if(C('CONTENT_ALLOW_GUEST_COMMENTS')){
                //允许游客留言
                $uid = 0;
            } else {
                //不允许游客评论，则提示登陆
                if(session('?echouid')){
                    $uid = session('echouid');
                } else {
                    //请去登陆
                    $return_data['status']  = -1;
                    $return_data['info'] = '用户未登录请去登陆！';
                    $this->ajaxReturn($return_data);
                }
            }

            //创建数据
            $comments = D('Comments');
            if($data = $comments->create()){
                //验证是否开启回复功能
                if(isset($comments->pid) && !empty($comments->pid)){
                    if(!C('CONTENT_COMMENTS_ALLOWREPLAY')){
                        $this->error('不允许回复评论');
                    }
                }
                
                //验证敏感词汇
                $this->sensitivewords($comments->comments,'评论内容存在敏感词，请检查！');

                //验证评论字数
                $wordslimit = abs(C('CONTENT_COMMENTS_WORDSLIMIT'));
                if($wordslimit > 0 && mb_strlen($comments->comments,'utf8') > $wordslimit){
                    $this->error('评论字数超过了限制数：' . abs(C('CONTENT_COMMENTS_WORDSLIMIT')) . ' 个字符！');
                }

                //验证评论内容不能为空
                if(empty($comments->comments)){
                    $this->error('评论内容不能为空！');
                }

                //时间限制
                $where['uid'] = array('eq',$uid);
                $where['aid'] = array('eq',$comments->aid);
                $where['modelid'] = array('eq',$comments->modelid);
                $where['siteid'] = array('eq',C('SITEID'));
                $where['create_time'] = array('egt',time() - C('CONTENT_COMMENTS_TIMELIMIT'));
                if($one = $comments->where($where)->find()){
                    $this->error("您已经评论过了，谢谢您对我们的支持！ ".(C('CONTENT_COMMENTS_TIMELIMIT') + $one['create_time'] - time())."s 之后继续评论！");
                }

                //写入数据
                $comments->siteid = C('SITEID');
                $comments->uid = $uid;
                $comments->create_time = time();

                //是否审核
                if(C('CONTENT_COMMENTS_CHECKED')){
                    $comments->status = 0;
                    $success_info = "评论成功！请等待审核！";
                } else {
                    $comments->status = 1;
                    $success_info = "评论成功！";
                }

                if($inserid = $comments->add()){
                    //设置排序与唯一标识符
                    $update_data = array('sort'=>$inserid,'uniqid'=>aikehou_uniqid($inserid));
                    $comments->where("id = ".$inserid."")->setField($update_data);

                    $this->success($success_info);
                } else {
                    $this->error('评论失败！');
                }

            } else {
                $this->error($comments->getError());
            }

        } else {
            $this->error(L('_ACCESS_ERROR_'));
        }
    }

    /**
     * [delComments 删除评论]
     * @return [type] [description]
     */
    public function delComments()
    {
        if(IS_POST){
            //验证是否允许删除评论
            if(!C('CONTENT_COMMENTS_ALLOWDEL')){
                $this->error('不允许删除评论');
            }

            $uniqid = I('post.commentsid','');
            if(!is_string($uniqid) || empty($uniqid)){
                $this->error(L('_ACCESS_ERROR_'));
            }

            //验证是否存在
            $comments = M('Comments');
            
            //必须是当前用户删除的，游客不允许删除
            $uid = session('?echouid') ? session('echouid') : -1;

            $where['uid'] = array('eq',$uid);
            $where['uniqid'] = array('eq',$uniqid);
            if(!$one = $comments->field('id')->where($where)->find()){
                $this->error(L('_ACCESS_ERROR_'));
            }

            //执行删除
            if($comments->where($where)->delete()){
                $this->success('删除评论成功！');
            } else {
                $this->error('删除评论失败！');
            }

        } else {
            $this->error(L('_ACCESS_ERROR_'));
        }
    }
}
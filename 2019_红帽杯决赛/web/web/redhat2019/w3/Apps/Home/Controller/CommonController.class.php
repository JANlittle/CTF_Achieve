<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 公共控制 ]
*/
namespace Home\Controller;
use Think\Controller;
class CommonController extends Controller {
    /**
     * [_initialize 初始化函数]
     */
    public function _initialize()
    {
        //验证是否安装
        if(!file_exists('./data/system_install.lock')){
            $this->redirect("Install/Index/index");
        }

    /* 处理页面跳转 */
        //获取当前站点信息
        $nowsite = M('Site')->find(C('siteid'));

        //验证WAP域名如果是空的没有配置，则认为是响应式的网站，否则是分布式的
        if(empty($nowsite['wap_url'])){
            $urlinfo = parse_url($nowsite['url']);
        } else {
            if(!empty($nowsite['url'])){
                if(!ismobile()){
                    $urlinfo = parse_url($nowsite['url']);
                } else {
                    $urlinfo = parse_url($nowsite['wap_url']);
                }
            }
        }
        //设置二级域名
        $this->myurl = $urlinfo['host'] ? $urlinfo['host'] : '';

        //当前页面缓存标识符
        $this->cache_name = md5(U(__SELF__."@".$this->myurl,'',false,true));
        
        //响应式的页面不做跳转
        if(!empty($nowsite['wap_url'])){
            //pc端条件下跳转到WAP端
            if(ismobile()){
                $redirect_url = U("@".$this->myurl,"",false,true);
                redirect($redirect_url);
            }

            //WAP端条件跳转到pc端
            /*if(!ismobile()){
                $redirect_url = U("@".$this->myurl,"",false,true);
                redirect($redirect_url);
            }*/
        }
    /* end 处理页面跳转 */

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

        //设置数据缓存初始化
        if(C('CACHE_OPEN') == 1){
            S(array(
                'type' => C('CACHE_TYPE'),
                'host' => C('CACHE_HOST'),
                'port' => C('CACHE_PORT'),
                'prefix' => C('CACHE_PREFIX'),
                'expire' => C('CACHE_EXPIRE'),
                'length' => C('CACHE_LENGTH'),
                'timeout' => C('CACHE_TIMEOUT'),
                'persistent' => C('CACHE_PERSISTENT'),
                )
            );
        } else {
            S(array(
                'type' => C('CACHE_TYPE'),
                'host' => C('CACHE_HOST'),
                'port' => C('CACHE_PORT'),
                'prefix' => C('CACHE_PREFIX'),
                'expire' => 1,
                'length' => C('CACHE_LENGTH'),
                'timeout' => C('CACHE_TIMEOUT'),
                'persistent' => C('CACHE_PERSISTENT'),
                )
            );
        }

        //获取菜单分类
        $this->menu = S('menu');
        if(empty($this->menu)){
            $this->menu = $this->getMenu();
            S('menu',$this->menu);
        }

        //获取顶级父类id，用于菜单选中
        $menu_cid = I('get.cid',0);
        if($menu_cid){
            $menu_parent = $this->getParent($menu_cid);
            $this->parent_id = $menu_parent['id'];
        } else {
            $this->parent_id = 0;
        }
    }

    /**
     *  [getMenu 获取菜单分类]
    */
    public function getMenu()
    {

        $pid = I('get.pid',0);
        $siteid = I('get.siteid',C('SITEID'));
        $category = M('Category');
        $where['position'] = array('neq',0);
        $where['status'] = array('eq',1);
        $where['siteid'] = array('eq',C('SITEID'));
        //获取数据
        $data = $category->where($where)->order('sort ASC,id DESC')->select();
        
        //处理图片解析
        if(!empty($data)){
            foreach ($data as $key => $value) {
                if(!empty($value['thumb'])){
                    $data[$key]['thumb'] = unserialize($value['thumb']);
                }
            }
        }
        $data = unlimitedForLayer($data);
        return $data;
    }

    /**
     * [getParent 获取父类菜单]
     * @param  $cid [int]
     * @return [type]
    */
    public function getParent($cid)
    {
        $category = M('Category');
        $where['status'] = array('eq',1);
        $where['siteid'] = array('eq',C('SITEID'));
        //获取数据
        $data = $category->where($where)->select();
        //处理图片解析
        if(!empty($data)){
            foreach ($data as $key => $value) {
                if(!empty($value['thumb'])){
                    $data[$key]['thumb'] = unserialize($value['thumb']);
                }
            }
        }
        $data = getParents($data,$cid);
        $data = unlimitedForLayer($data);
        return $data[0];
    }


    /**
     * [getChild 获取子类]
     * @param  $cid [int]
     * @return [type]
    */
    public function getChild($cid)
    {
        $category = M('Category');
        $where['status'] = array('eq',1);
        $where['siteid'] = array('eq',C('SITEID'));
        $where['position'] = array('neq',0);
        //获取数据
        $data = $category->where($where)->order('sort ASC,id DESC')->select();
        //处理图片解析
        if(!empty($data)){
            foreach ($data as $key => $value) {
                if(!empty($value['thumb'])){
                    $data[$key]['thumb'] = unserialize($value['thumb']);
                }
            }
        }
        $data = getChilds($data,$cid);
        return $data;
    }


    /**
     * [getPosition 获取面包屑]
     * @param  $cid [int]
     * @return [type]
    */
    public function getPosition($cid)
    {

        //获取当前分类下(包括本身)id
        $cate_id = $this->getCateIds();

        //获取父类的所有id(位置导航)
        $parent_id = getParents($cate_id,$cid);
        $parent_ids = array();
        if(!empty($parent_id)){
            foreach ($parent_id as $key => $value) {
                $parent_ids[] = $value['id'];
            }
        }

        if(!empty($parent_ids)){
            $where['id'] = array('IN',$parent_ids);
        }
        $where['status'] = array('eq',1);
        $where['siteid'] = array('eq',C('SITEID'));
        $data = M('Category')->where($where)->order('sort ASC')->select();
        return $data;
    }

    /**
     * [getCateIds 获取分类的所有id]
     * @param  $cid [int]
     * @return [type]
    */
    public function getCateIds()
    {
        $category = M('Category');
        //获取当前分类下(包括本身)id
        $where['status'] = array('eq',1);
        $where['siteid'] = array('eq',C('SITEID'));
        $cate_id = $category->field('id,pid')->where($where)->select();
        return $cate_id;
    }

    /**
     * [_404 404错误]
     * @return [type]
    */
    public function _404()
    {
        header("HTTP/1.0 404 Not Found");
        header('Status:404 Not Found"');
        $this->display(".".C('URL_404_REDIRECT'));
        exit();
    }

    /**
     * [_empty 空方法]
     * @return [type]
    */
    public function _empty(){
        $this->_404();
    }


    /**
     * [verify 验证码]
     * @param  integer $codeW    [验证码宽度 设置为0为自动计算]
     * @param  integer $codeH    [验证码高度 设置为0为自动计算]
     * @param  string  $font     [指定验证码字体 默认为随机获取]
     * @param  integer $fontSize [验证码字体大小（像素） 默认为25]
     * @param  integer $length   [验证码长度]
     * @param  boolean $useNoise [是否添加杂点 默认为true]
     * @param  boolean $useCurve [是否使用混淆曲线 默认为true]
     * @param  boolean $useImgBg [是否使用背景图片 默认为false]
     * @return [type]            [description]
     */
    public function verify($codeW = 240, $codeH = 60, $font = '4.ttf', $fontSize = 25, $length = '', $useNoise = true, $useCurve = true, $useImgBg = false)
    {
        $verify = new \Think\Verify();
        $verify->imageW = $codeW;
        $verify->imageH = $codeH;
        $verify->fontttf = $font;
        $verify->fontSize = $fontSize;

        //不传入数量时默认
        if(!is_numeric($length)){
            $length = C('VERIFY_NUMBER');
        }
        $verify->length = $length;

        $verify->useNoise = $useNoise;
        $verify->useCurve = $useCurve;
        $verify->useImgBg = $useImgBg;
        $verify->entry();
    }

    /**
     * [baoming 在线报名]
     * @return [type] [description]
     */
    public function baoming()
    {
        if(IS_POST){
            //验证ip是否被限制
            $this->iplimit();

            //验证标题敏感词汇
            $this->sensitivewords(I('post.name'),L('_BAOMING_NAME_SENSITIVE_'));

            //验证码
            $code = I('post.code');
            if(isset($code) && !empty($code)){
                if(!check_verify($code)){
                    $this->error(L('_VERIFY_ERROR_'));
                }
            }

            //创建数据
            $baoming = D('Baoming');
            if($baoming->create()){
                //验证是否存在数据
                $where['name'] = array('eq',$baoming->name);
                $where['tel'] = array('eq',$baoming->tel);
                if($one = $baoming->where($where)->find()){
                    $this->error(L('_BM_EXISTS_'));
                }

                //ip
                $baoming->ip = get_client_ip();

                //地区
                $ip = new \Org\Net\IpLocation('UTFWry.dat');
                $area = $ip->getlocation($baoming->ip);
                $baoming->area = serialize($area);

                //事件
                $baoming->create_time = time();
                $baoming->siteid = C('SITEID');

                if($inserid = $baoming->add()){
                    $baoming->where("id = ".$inserid."")->setField("sort",$inserid);
                    $this->success(L('_BM_SUCCESS_'));
                } else {
                    $this->error(L('_BM_ERROR_'));
                }
            } else {
                $this->error($baoming->getError());
            }
        } else {
            $this->error(L('_ACCESS_ERROR_'));
        }
    }

    /**
     * [iplimit 验证ip是否被禁用]
     * @return [type] [description]
     */
    protected function iplimit()
    {
        //验证ip是否被限制
        $ip_arr = explode('|',C('BASE_IPLIMIT'));
        $ip_now = get_client_ip();
        if(in_array($ip_now,$ip_arr)){
            $this->error(L('_IP_LIMIT_'));
        }
    }

    /**
     * [iplimit 验证是否敏感词汇]
     * @return [type] [description]
     */
    protected function sensitivewords($str = '',$errmsg = '')
    {
        if(empty($errmsg)){
            $errmsg = L('_SENSITIVE_WORDS_LIMIT_');
        }

        //验证敏感词汇
        $sensitive_arr = explode('|',C('BASE_SENSITIVE_WORD'));
        if(!empty($sensitive_arr)){
            foreach ($sensitive_arr as $key => $value) {
                if(preg_match("/".$value."/", $str)){
                    $this->error($errmsg);
                    break;
                }
            }
        }
    }

    /**
     * [getDataList 获取jsonp数据：作为获取数据参考使用，不同场合可以改装使用]
     * @return [type] [description]
    */
    //传递数组条件
    /*
    $this->condition = json_encode(
        array(
            'catid'=>array('neq',0),
            'status'=>array('eq',1),
            'isdel'=>array('eq',0),
            '_where' => 'geyoutu1.id >= id2',
            '_alias'=>'geyoutu1',
            '_limit' => $limit,
            '_join'=>'(SELECT ROUND(RAND() * ((SELECT MAX(id) FROM `'.C('DB_PREFIX').'geyoutu`)-(SELECT MIN(id) FROM `'.C('DB_PREFIX').'geyoutu`))+(SELECT MIN(id) FROM `'.C('DB_PREFIX').'geyoutu`)) AS id2) as geyoutu2'
        )
    );
    */
    public function getDataList(){
        if(IS_AJAX){
            $Geyoutu = D('Geyoutu');

            //返回数据
            $returnData = array();

            //设置条件
            $_orderby = "";
            $_join = "";
            $_alias = "";
            $_limit = "";
            $_where = "";

            //获取条件
            $condition  = I('get.condition');
            if(!empty($condition)){
                foreach ($condition as $key => $value) {
                    if($key === '_orderby'){
                        $_orderby = htmlspecialchars_decode($value);
                    } else if($key === '_join'){
                        $_join = htmlspecialchars_decode($value);
                    } else if($key === '_alias'){
                        $_alias = htmlspecialchars_decode($value);
                    } else if($key === '_limit'){
                        $_limit = htmlspecialchars_decode($value);
                    } else if($key === '_where'){
                        $_where = htmlspecialchars_decode($value);
                    } else {
                        $Geyoutu_where[$key] = array(trim($value[0]),$value[1]);
                    }
                }
            }

            //设置where
            if(!empty($_where)){
                $Geyoutu_where['_string'] = $_where;
            }

            //设置limit
            if(empty($_limit)){
                //查询条数
                $count = $Geyoutu->alias($_alias)->join($_join)->order($_orderby)->where($Geyoutu_where)->count();

                //限制条数
                $returnData['limitNumber'] = I('get.limitNumber', C('BASE_PAGENUM'));

                //总共的分页
                $totaPage = ceil($count/$returnData['limitNumber']);

                //当前分页
                $returnData['page'] = I('get.page',1);
                $pageStart = ($returnData['page'] - 1) * $returnData['limitNumber'];

                $_limit = $pageStart.",".$returnData['limitNumber'];
            }

            $list = $Geyoutu->alias($_alias)->join($_join)->order($_orderby)->where($Geyoutu_where)->relation(array('Category'))->limit($_limit)->select();
            if(!empty($list)){
                foreach ($list as $key => $value) {
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
            $returnData['data'] = $list;
            $this->ajaxReturn($returnData,'jsonp');
        }
    }
}
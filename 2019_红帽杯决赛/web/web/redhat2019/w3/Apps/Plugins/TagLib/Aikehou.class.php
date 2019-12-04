<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 扩展标签 ]
*/
namespace Plugins\TagLib;
use Think\Template\TagLib;

/**
 * Aikehou标签库解析类
 */
class Aikehou extends TagLib {

    // 标签定义
    protected $tags   =  array(
        'runsql'    =>  array('attr'=>'name,id,table,relation,truetable,join,field,where,order,limit,cache,cachename,page,pageparameter,pageurl,pagedepr,pageisbootstrap,pageprev,pagenext,pageheader','close'=>1,'level'=>3,'alias'=>'getsql'),
    );


    /**
     * [_runsql 执行sql标签]
     * @param  [type] $tag     [description]
     * @param  [type] $content [description]
     * @return [type]          [description]
     */
    public function _runsql($tag,$content)
    {   

        $name  =    $tag['name'];
        $id    =    $tag['id'];
        $empty =    isset($tag['empty'])?$tag['empty']:'';
        $key   =    !empty($tag['key'])?$tag['key']:'i';
        $mod   =    isset($tag['mod'])?$tag['mod']:'2';

        //判断是否开启缓存
        if($tag['cache']){
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
            $myurl = $urlinfo['host'] ? $urlinfo['host'] : '';

            $tag['cachename'] = !empty($tag['cachename']) ? $tag['cachename'] : '*';

            $cache_name = $tag['cachename'].md5(U(__SELF__."@".$myurl,'',true,true));
        }

        //设置sql参数
        $tag['table'] = !empty($tag['table']) ? $tag['table'] : '';
        $tag['field'] = !empty($tag['field']) ? $tag['field'] : '*';
        $tag['where'] = !empty($tag['where']) ? $tag['where'] : '';
        $tag['order'] = !empty($tag['order']) ? $tag['order'] : '';
        $tag['limit'] = !empty($tag['limit']) ? $tag['limit'] : '';

        
        //relation
        $tag['relation'] = !empty($tag['relation']) ? $tag['relation'] : '';
        $tag['relation'] = preg_match ("/$array\((.*)\)$/Umi", $tag['relation']) ? $tag['relation'] : '"'.$tag['relation'].'"';
        $relation = ($tag['relation'] != '""') ? '->relation('.$tag['relation'].')' : '';
        $model = ($tag['relation'] != '""') ? 'D("'.$tag['table'].'")' : 'M("'.$tag['table'].'")';
        if($tag['relation'] != '""'){
            $relation = '->relation('.$tag['relation'].')';
            $model = !empty($tag['table']) ? 'D("'.$tag['table'].'")' : 'D()';
        } else {
            $relation = '';
            $model = !empty($tag['table']) ? 'M("'.$tag['table'].'")' : 'M()';
        }

        //truetable
        $tag['truetable'] = !empty($tag['truetable']) ? $tag['truetable'] : '';
        if(!empty($tag['truetable'])){
            $truetable = '->table("'.$tag['truetable'].'")';
        } else {
            $truetable = '';
        }

        //如果真实表与基本表都不存在则直接退出
        if(empty($tag['truetable']) && empty($tag['table'])){
            return '请设置数据表名称或者真实表名！';
        }

        //join
        $tag['join'] = !empty($tag['join']) ? $tag['join'] : '';
        $join = !empty($tag['join']) ? '->join("'.$tag['join'].'")' : '';

        // 允许使用函数设定数据集 <volist name=":fun('arg')" id="vo">{$vo.name}</volist>
        $parseStr   =  '<?php ';

        //判断是否开启分页
        if($tag['page'] && $tag['page'] > 0 && is_numeric($tag['page'])){
           //设置分页数量
           
           $tag['pageparameter'] = !empty($tag['pageparameter']) ? $tag['pageparameter'] : '';
           $tag['pageurl'] = !empty($tag['pageurl']) ? $tag['pageurl'] : '';
           $tag['pagedepr'] = !empty($tag['pagedepr']) ? $tag['pagedepr'] : '';
           $tag['pageisbootstrap'] = !empty($tag['pageisbootstrap']) ? $tag['pageisbootstrap'] : 1;
           $tag['pageprev'] = !empty($tag['pageprev']) ? $tag['pageprev'] : '上一页';
           $tag['pagenext'] = !empty($tag['pagenext']) ? $tag['pagenext'] : '下一页';
           $tag['pageheader'] = !empty($tag['pageheader']) ? $tag['pageheader'] : "";

           //$count = M($tag['table'])->table($tag['truetable'])->join($tag['join'])->where()->count();
           //$page = getPage($count,$tag['page'],$tag['pageparameter'],$tag['pageurl'],$tag['pagedepr'],$tag['pageisbootstrap'],$tag['pageprev'],$tag['pagenext'],$tag['pageheader']);
           //$showpage = $page->show();
           //如果有分页则设置limit为分页的limit，单独传递的limit失效
           //$tag['limit'] = $page->firstRow.",".$page->listRows;

           $parseStr  .=  '$count = '.$model.$truetable.$join.'->where("'.$tag['where'].'")->count();';
           $parseStr  .=  '$page = getPage($count,'.$tag['page'].',"'.$tag['pageparameter'].'","'.$tag['pageurl'].'","'.$tag['pagedepr'].'",'.$tag['pageisbootstrap'].',"'.$tag['pageprev'].'","'.$tag['pagenext'].'","'.$tag['pageheader'].'");';
           $parseStr  .=  '$showpage = $page->show();';

           //如果有分页则设置limit为分页的limit，单独传递的limit失效
           $parseStr  .=  '$limit = $page->firstRow.",".$page->listRows;';
        } else {
           $parseStr  .=  '$limit = '.$tag['limit'].';';
        }

        //判断是否开启缓存
        if($tag['cache']){
            $parseStr   .=  '$sqldatalist = S($cache_name);';
            $parseStr   .=  'if(empty($sqldatalist)): ';

            //sql
            $parseStr  .=  '$'.$tag['name'].' = '.$model.$truetable.'->field("'.$tag['field'].'")'.$join.'->where("'.$tag['where'].'")'.$relation.'->order("'.$tag['order'].'")->limit($limit)->select();';
            $parseStr  .=  'S($cache_name,$'.$tag['name'].');';
            $parseStr  .=  'else:';
            $parseStr  .=  '$'.$tag['name'].' = $sqldatalist;';
            $parseStr .= 'endif;';
        } else {
            //sql
            $parseStr  .=  '$'.$tag['name'].' = '.$model.$truetable.'->field("'.$tag['field'].'")'.$join.'->where("'.$tag['where'].'")'.$relation.'->order("'.$tag['order'].'")->limit($limit)->select();';
        }

        //判断name是否使用函数
        if(0===strpos($name,':')) {
            $parseStr   .= '$_result='.substr($name,1).';';
            $name   = '$_result';
        }else{
            $name   = $this->autoBuildVar($name);
        }

        $parseStr  .=  'if(is_array('.$name.')): $'.$key.' = 0;';
        if(isset($tag['length']) && '' !=$tag['length'] ) {
            $parseStr  .= ' $__LIST__ = array_slice('.$name.','.$tag['offset'].','.$tag['length'].',true);';
        }elseif(isset($tag['offset'])  && '' !=$tag['offset']){
            $parseStr  .= ' $__LIST__ = array_slice('.$name.','.$tag['offset'].',null,true);';
        }else{
            $parseStr .= ' $__LIST__ = '.$name.';';
        }
        $parseStr .= 'if( count($__LIST__)==0 ) : echo "'.$empty.'" ;';
        $parseStr .= 'else: ';
        $parseStr .= 'foreach($__LIST__ as $key=>$'.$id.'): ';
        $parseStr .= '$mod = ($'.$key.' % '.$mod.' );';
        $parseStr .= '++$'.$key.';?>';
        $parseStr .= $this->tpl->parse($content);
        $parseStr .= '<?php endforeach; endif; else: echo "'.$empty.'" ;endif; ?>';

        //判断是否开启分页
        if($tag['page'] && $tag['page'] > 0 && is_numeric($tag['page'])){
            $parseStr .= '<?php echo $showpage;?>';
        }

        if(!empty($parseStr)) {
            return $parseStr;
        }
        return ;
    }
}

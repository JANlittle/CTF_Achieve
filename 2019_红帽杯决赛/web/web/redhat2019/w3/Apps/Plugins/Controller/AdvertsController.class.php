<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 广告插件控制器 ]
*/
namespace Plugins\Controller;
class AdvertsController extends CommonController {

    /**
     * 广告插件处理函数
    */
    public function index(){
        if(IS_GET){
        	$data = I('get.','');
        	$this->adid = $data['adid'];
        	$this->siteid = $data['siteid'];
        	$this->adspaceid = $data['adspaceid'];
        	
        	//验证数据
        	if(empty($this->siteid) || !is_numeric($this->siteid)){
        		//$ads_info = L('_ACCESS_ERROR_');
                $ads_info = '';
                echo 'document.write("'.$ads_info.'");';
                exit();
        	}

        	if(empty($this->adspaceid) || !is_numeric($this->adspaceid)){
        		//$ads_info = L('_ACCESS_ERROR_');
                $ads_info = '';
                echo 'document.write("'.$ads_info.'");';
                exit();
        	}

        	//广告位
        	if(empty($this->adid) || !is_numeric($this->adid)){
                $Adspace = D('Adspace');
                $adspace_where['id'] = array('eq',$this->adspaceid);
                $adspace_where['siteid'] = array('eq',$this->siteid);
                $adspace_where['status'] = array('eq',1);

                //获取广告位
                $adspace_data = $Adspace->where($adspace_where)->relation('ads')->find();

                //广告位是不开启时或不存在时
                if(empty($adspace_data)){
                    //$ads_info = '不存在广告位或者广告位已经关闭！';
                    $ads_info = '';
                    echo 'document.write("'.$ads_info.'");';
                    exit();
                }

                //组装广告信息
                $ads_info_new = '';
                if(!empty($adspace_data['ads'])){
                    foreach ($adspace_data['ads'] as $key => $value) {
                        //图片类型
                        if(!empty($value) && $value['type'] == 1){
                            //解析图片
                            $value['pictures'] = unserialize($value['pictures']);

                            //设置链接
                            $value['links'] = empty($value['links']) ? "javascript:;" : $value['links'];
                            $ads_info = "<li><a href='".$value['links']."' target='_blank'><img src='".$value['pictures'][0]['thumb']."' width='".$adspace_data['width']."' height='".$adspace_data['height']."' alt='".$value['name']."'/></a><div class='am-slider-desc'><a href='".$value['links']."' target='_blank'>".$value['name']."</a></div></li>";
                        } elseif(!empty($value) && $value['type'] == 2){
                        //文字类型
                            
                            //设置链接
                            $value['links'] = empty($value['links']) ? "javascript:;" : $value['links'];

                            //设置名称
                            $value['words'] = empty($value['words']) ? $value['name'] : $value['words'];

                            $ads_info = "<li style='padding:5px 0'><a href='".$value['links']."' target='_blank' title='".$value['words']."'>".$value['words']."</a></li>";
                        } elseif(!empty($value)  && $value['type'] == 3){
                        //代码类型
                            $ads_info_code = htmlspecialchars_decode("<p>".$value['code']."</p>");
                            $ads_info = preg_replace('/\"/','\'',$ads_info_code);
                        }

                        //开启时间设定
                        if($value['datetype'] == 2){

                            //1、当前时间戳小于开始时间，则广告没开始投放
                            if(time() < $value['starttime']){
                                //$ads_info = '广告未开始投放！';
                                $ads_info = '';
                            }

                            //2、当前时间戳大于结束时间，则广告过期
                            if(time() > $value['endtime']){
                                //$ads_info = '广告已经过期！';
                                $ads_info = '';
                            }
                        }

                        //组装字符串
                        $ads_info_new .= $ads_info;

                        //更新显示次数，过期或则没开始则不更新次数
                        if($value['datetype'] == 1 || (time() <= $value['endtime'] && time() >= $value['starttime'])){
                            $ads_where['id'] = array('eq',$value['id']);
                            $ads_where['aid'] = array('eq',$this->adspaceid);
                            $ads_where['siteid'] = array('eq',$this->siteid);
                            $ads_where['status'] = array('eq',1);
                            M('Ads')->where($ads_where)->setInc('shownum',1);
                        }
                    }
                }

                //如果是图片类型
                if($adspace_data['type'] == 1){

                    $img_info = "<div data-am-widget='slider' style='width:".$adspace_data['width']."px;height:".$adspace_data['height']."px;' class='am-slider am-slider-c2' data-am-slider='directionNav:false'><ul class='am-slides'>";
                    $img_info .=  $ads_info_new;          
                    $img_info .= "</ul></div>";

                    $ads_info_new = $img_info;
                }

                //输出内容
                echo 'document.write("'.$ads_info_new.'");';

        	} else {
        	//单条广告
        		$ads = D('Ads');
        		$ads_where['id'] = array('eq',$this->adid);
        		$ads_where['aid'] = array('eq',$this->adspaceid);
        		$ads_where['siteid'] = array('eq',$this->siteid);
                $ads_where['status'] = array('eq',1);

        		//获取单个广告数据
        		$ads_data = $ads->where($ads_where)->relation('adspace')->find();

                //广告数据为空或则，广告位是不开启时
                if(empty($ads_data) || empty($ads_data['adspace'])){
                    //$ads_info = '不存在当前广告或者广告已经关闭；不存在广告位或者广告位已经关闭！';
                    $ads_info = '';
                    echo 'document.write("'.$ads_info.'");';
                    exit();
                }

        		//图片类型
        		if(!empty($ads_data) && $ads_data['type'] == 1){
        			//解析图片
        			$ads_data['pictures'] = unserialize($ads_data['pictures']);

        			//设置链接
        			$ads_data['links'] = empty($ads_data['links']) ? "javascript:;" : $ads_data['links'];
        			$ads_info = "<a href='".$ads_data['links']."' target='_blank'><img src='".$ads_data['pictures'][0]['thumb']."' width='".$ads_data['adspace']['width']."' height='".$ads_data['adspace']['height']."' alt='".$ads_data['name']."'/></a>";
        		} elseif(!empty($ads_data) && $ads_data['type'] == 2){
        		//文字类型
        			
        			//设置链接
        			$ads_data['links'] = empty($ads_data['links']) ? "javascript:;" : $ads_data['links'];

        			//设置名称
        			$ads_data['words'] = empty($ads_data['words']) ? $ads_data['name'] : $ads_data['words'];

        			$ads_info = "<a href='".$ads_data['links']."' target='_blank'>".$ads_data['words']."</a>";
        		} elseif(!empty($ads_data)  && $ads_data['type'] == 3){
        		//代码类型
        			$ads_info = htmlspecialchars_decode($ads_data['code']);
        			$ads_info = preg_replace('/\"/','\'',$ads_info);
        		}

        		//开启时间设定
        		if($ads_data['datetype'] == 2){

        			//1、当前时间戳小于开始时间，则广告没开始投放
        			if(time() < $ads_data['starttime']){
        				//$ads_info = '广告未开始投放！';
        				$ads_info = '';
        			}

        			//2、当前时间戳大于结束时间，则广告过期
        			if(time() > $ads_data['endtime']){
        				//$ads_info = '广告已经过期！';
        				$ads_info = '';
        			}
        		}

        		//更新显示次数，过期或则没开始则不更新次数
        		if($ads_data['datetype'] == 1 || (time() <= $ads_data['endtime'] && time() >= $ads_data['starttime'])){
        			$ads->where($ads_where)->setInc('shownum',1);
        		}

        		//输出内容
        		echo 'document.write("'.$ads_info.'");';
        	}
        } else {
        	//$ads_info = L('_ACCESS_ERROR_');
            $ads_info = '';
            echo 'document.write("'.$ads_info.'");';
            exit();
        }
    }
}
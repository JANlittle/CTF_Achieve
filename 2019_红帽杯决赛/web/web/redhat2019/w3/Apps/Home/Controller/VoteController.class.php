<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.13 ]
* Description [ 投票控制器 ]
*/
namespace Home\Controller;
class VoteController extends CommonController {

    /**
     * 投票
    */
    public function index(){
    	//获取投票信息
    	$Vote = D('Vote');
    	$where['siteid'] = array('eq',C('SITEID'));
    	$where['status'] = array('eq',1);
    	$vote = $Vote->where($where)->relation(array('Voteoptions','Voteinfo'))->select();

    	//处理图片
    	if(!empty($vote)){
    		foreach ($vote as $key => $value) {
    			$vote[$key]['thumb'] = unserialize($value['thumb']);
    		}
    	}
    	$this->vote = $vote;
        $this->seoData = array('title'=>'投票','keywords'=>'投票','description'=>'投票');
        $this->display();
    }

    /**
     * [vote 投票函数]
     * @return [type] [description]
     */
    public function vote()
    {
    	if(IS_POST){
    		//验证ip是否被限制
            $this->iplimit();

            //获取数据
            $data = I('post.');

            //验证数据
            $Vote = M('Vote');
            if(!$votedata = $Vote->find($data['vid'])){
            	$this->error(L('_ACCESS_ERROR_'));
            }

            //验证时间是否过去或者未开始，或者关闭
            if(!$votedata['status']){
            	$this->error('当前投票已经关闭！');
            }
            if(time() < $votedata['start'] || time() > $votedata['end']){
            	$this->error('当前投票未开始或已经过期！');
            }

            //验证是否允许游客登陆
            if($votedata['guest']){
                //允许游客
                $uid = 0;
            } else {
                //不允许游客，则提示登陆
                if(session('?echouid')){
                    $uid = session('echouid');
                } else {
                    //请去登陆
                    $return_data['status']  = -1;
                    $return_data['info'] = '用户未登录请去登陆！';

                    //跳转地址
		            $returnurl = encode(U("vote@$this->myurl",array('id'=>$data['vid']),false,true));
		            cookie('login_return_url',$returnurl);

                    $this->ajaxReturn($return_data);
                }
            }

            //判断选项
            if(!isset($data['voteArr']) || empty($data['voteArr'])){
                $this->error('请选择投票选项！');
            }

            //投票
            $Voteinfo = M('Voteinfo');

            //验证时间周期或者时间间隔以及投票次数
            if(!$votedata['guest']){
	            //用户
                $where['uid'] = array('eq',$uid);
            } else {
                //游客，使用ip作为限制会导致同一个ip的其他用户无法投票
                $where['ip'] = array('eq',get_client_ip());
            }
			$where['vid'] = array('eq',$votedata['id']);
			$where['siteid'] = array('eq',C('siteid'));

			//是否开启周期或间隔
			if($votedata['cycle'] > 0){
	            if($votedata['datetype'] == 1){
					//是否验证周期
					$where['create_time'] = array('gt',time()-$votedata['cycle']);
	            } elseif($votedata['datetype'] == 2){
					//是否验证间隔天数投票次数
					$starttime = strtotime(date('Y-m-d')) - ($votedata['cycle']-1) * 86400;
			    	$endtime = strtotime(date('Y-m-d')) + 86400;
			    	$where['create_time'] = array('between',array($starttime,$endtime));
				}
            }

            //验证投票次数
            $voteinfo_data = $Voteinfo->where($where)->group('pici')->select();
            $count = count($voteinfo_data);
			if($count >= $votedata['number']){
				$this->error('您的投票次数已经用完！');
			}

            //地区
            $vote_ip = get_client_ip();
            $ip = new \Org\Net\IpLocation('UTFWry.dat');
            $area = $ip->getlocation($vote_ip);
            $area = serialize($area);

            //设置投票批次唯一标识符
            $pici = aikehou_uniqid();
            $dataList = array();
            if(!empty($data['voteArr'])){
                foreach ($data['voteArr'] as $key => $value) {
                    $dataList[] = array('siteid'=>C('SITEID'),'uid'=>$uid,'vid'=>$data['vid'],'optionsid'=>$value,'create_time'=>time(),'ip'=>$vote_ip,'area'=>$area,'pici'=>$pici);
                }
            }

            //添加
            if($Voteinfo->addAll($dataList)){
                $this->success('投票成功！');
            } else {
                $this->error('投票失败！');
            }
    	} else {

	    	$id = I('get.id');
	    	if(!is_numeric($id)){
	    		$this->error(L('_ACCESS_ERROR_'));
	    	}

	    	//投票模型
	    	$Vote = D('Vote');

	    	$where['id'] = array('eq',$id);
	    	$where['siteid'] = array('eq',C('SITEID'));
	    	if(!$data = $Vote->where($where)->relation(array('Voteoptions','Voteinfo'))->find()){
	    		$this->error(L('_ACCESS_ERROR_'));
	    	}

			//投票模型	    	
	    	$Voteinfo = M('Voteinfo');

	    	//如果开启游客投票是无法判断当前有多少人投过票(只能计算累计投票次数)
	    	if(!$data['guest']){
		    	$where_uid_count['vid'] = array('eq',$data['id']);
		    	$where_uid_count['siteid'] = array('eq',C('SITEID'));
		    	$uid_count = $Voteinfo->where($where_uid_count)->group('uid')->select();
		    	$data['count_uid'] = count($uid_count);
	    	} else {
	    		$data['count_uid'] = count($data['Voteinfo']);
	    	}

	    	//处理图片
	    	if(!empty($data)){
	    		$data['thumb'] = unserialize($data['thumb']);

                $all_voteinfo_count = count($data['Voteinfo']);
	    		//处理选项
	    		if(!empty($data['Voteoptions'])){
	    			foreach ($data['Voteoptions'] as $key => $value) {
	    				$data['Voteoptions'][$key]['thumb'] = unserialize($value['thumb']);

	    				//获取
	    				$voteinfo_where['optionsid'] = array('eq',$value['id']);
	    				$voteinfo_where['vid'] = array('eq',$data['id']);
	    				$voteinfo_where['siteid'] = array('eq',C('SITEID'));
	    				$voteinfo_data_1 = $Voteinfo->where($voteinfo_where)->select();
                        $data['Voteoptions'][$key]['Voteinfo'] = $voteinfo_data_1;

                        //设置百分比
                        $data['Voteoptions'][$key]['percent'] = number_format(count($voteinfo_data_1)/$all_voteinfo_count,4)*100;
	    			}
	    		}
	    	}

	    	$this->data = $data;
	        $this->seoData = array('title'=>$data['title'],'keywords'=>$data['title'],'description'=>$data['title']);

	    	$this->display();
    	}
    }
}
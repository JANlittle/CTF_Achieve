<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 上传文件控制器 ]
*/
namespace Home\Controller;

class UploadfileController extends CommonController
{

	/**
	 * [index 文件上传代码]
	 * @return [type] [description]
	 */
	public function index(){
		if(IS_POST){

			/**
			 * 1、如果是图片直接调用uploadPhoto()
			 * 2、如果是替他附件则按照当前配置设置
			*/
			$type = I('post.type','files');
			$saveDir = I('post.savedir');
			$thumbw = I('post.thumbw');
			$thumbh = I('post.thumbh');
			$filesize = I('post.filesize');
			if($type == 'images' || preg_match('/^image\//', $_FILES['Filedata']['type'][0])){
				$type = "images";
				//是否添加水印
				$water = I('post.water');

				if($water){
					$water_site = C('SITE_SYSTEM_IMG_WATER');
					$water_new = isset($water_site) && !empty($water_site) ? $water_site : C('SYSTEM_IMG_WATER');
				    //$info = uploadPhoto('photo/'.$type.'',$thumbw,$thumbh,array('open'=>1));
					$info = uploadPhoto($saveDir.'/'.$type,$thumbw,$thumbh,$water_new,$filesize);
				} else {
					$info = uploadPhoto($saveDir.'/'.$type,$thumbw,$thumbh,'',$filesize);
				}

				if(!is_array($info)){
					$error_data['info'] = $info;
					$error_data['status'] = 0;
					$this->ajaxReturn($error_data);
				}

				$file['oringinal_type'] = 'images';
				$file['status'] = 1;
				$file['name'] = $_FILES['Filedata']['name'][0];
				$file['savename'] = $info[0]['savename'];
				$file['photo'] = substr($info[0]['savepath'],1).$info[0]['savename'];
				$file['thumb'] = $info[0]['thumbpath'];
				$file['location'] = 'upload';
				$this->ajaxReturn($file);
			} else {
				$type = 'files';
				$upload = new \Think\Upload();
				$upload->maxSize = $filesize*1024; //文件上传的最大文件大小，附件最大100M
				$upload->rootPath = "./"; //文件上传保存的根路径
				$upload->savePath = $upload->rootPath."Public/Uploads/".$saveDir."/" . $type ."/";//文件上传的保存路径
				$upload->saveName = date("YmdHis")."_".uniqid(); //上传文件的保存规则
				$upload->replace = true; //存在同名文件是否是覆盖
				$upload->autoSub = true;//自动使用子目录保存上传文件
				$upload->subName = date("Ymd");//子目录创建方式，采用数组或者字符串方式定义
				$upload->hash = true;//是否生成文件的hash编码 默认为true

				if(!$info = $upload->upload()){
					$error_data['status'] = 0;
					$error_data['info'] = $upload->getError();
					$this->ajaxReturn($error_data);
				} else {
					//处理文件图标
					//excel
					if(in_array($info[0]['ext'], array('xls','xlsx'))){
						$info[0]['ext'] = 'xls';
					}
					//ppt
					if(in_array($info[0]['ext'], array('ppt','pptx'))){
						$info[0]['ext'] = 'ppt';
					}
					//word
					if(in_array($info[0]['ext'], array('doc','docx'))){
						$info[0]['ext'] = 'doc';
					}
					//音频
					if(in_array($info[0]['ext'], array('wma','mp3','mid','wav'))){
						$info[0]['ext'] = 'mp3';
					}
					//视频
					if(in_array($info[0]['ext'], array('avi','mov','mpeg','mpg','swf','mp4'))){
						$info[0]['ext'] = 'vedio';
					}
					//zip
					if(in_array($info[0]['ext'], array('zip','rar','7z'))){
						$info[0]['ext'] = 'zip';
					}
					//判断是否存在文件不存在则取默认图片
					if(!file_exists("./Public/images/uploadfile/".$info[0]['ext'].".png")){
						$info[0]['ext'] = "readme";
					}
					$info[0]['status'] = 1;
					$info[0]['oringinal_type'] = 'files';
					$info[0]['savepathall'] = substr($info[0]['savepath'],1).$info[0]['savename'];
					$info[0]['location'] = 'upload';
					$this->ajaxReturn($info[0]);
				}
			}
		} else {
			 //缩略图配置
			$site_thumbw = C('SITE_SYSTEM_THUMB_WIDTH');
			$site_thumbh = C('SITE_SYSTEM_THUMB_HEIGHT');
			$thumbw = isset($site_thumbw) && !empty($site_thumbw) ? C('SITE_SYSTEM_THUMB_WIDTH') : C('SYSTEM_THUMB_WIDTH');
			$thumbh = isset($site_thumbh) && !empty($site_thumbh) ? C('SITE_SYSTEM_THUMB_HEIGHT') : C('SYSTEM_THUMB_HEIGHT');
			$this->thumbw = I('get.thumbw',$thumbw);
			$this->thumbh = I('get.thumbh',$thumbh);
			$this->thumbw = $this->thumbw === 0 ? $thumbw : $this->thumbw;
			$this->thumbh = $this->thumbh === 0 ? $thumbh : $this->thumbh;

			$this->type = I('get.type',''); //类型
			$this->myid = I('get.myid',''); //返回容器内容的值
			$this->iframe = I('get.iframe',''); //返回的iframe容器
			$this->field = I('get.field','');
			$this->returntype = I('get.returntype','multiple'); //返回文件的个数类型，单个-single，多个-multiple，默认是多个

			//控制上传文件大小
			$this->filesize = I('get.filesize');
			if(empty($this->filesize)){
				if($this->type == 'images'){
					$this->filesize = 3072; //图片默认3M
				} else {
					$this->filesize = 102400; //附件默认100M
				}
			}

			//上传目录
			$this->savedir = I('get.savedir','uploadfile');
			$this->display();
		}
	}

	/**
	 * [del 上传文件]
	 * @return [type] [description]
	 */
	public function del()
	{
		if(IS_POST){
			$type = I('post.oringinal_type');
			$location = I('post.location');
			if($location == 'upload'){
				if($type == 'images'){
					$photo = I('post.photo');
					$thumb = I('post.thumb');
					if(file_exists('.'.$photo)){
						unlink('.'.$photo);
					}
					if(file_exists('.'.$thumb)){
						unlink('.'.$thumb);
					}
				} else {
					$savepathall = I('post.savepathall');
					if(file_exists('.'.$savepathall)){
						unlink('.'.$savepathall);
					}
				}
			}
			$this->success(L('_DEL_SUCCESS_'));
		} else {
			$this->error(L('_ACCESS_ERROR_'));
		}
	}

	/**
	 * [files 获取本地文件]
	 * @return [type]        [description]
	 */
	public function files()
	{
		//上传类型
		$this->returntype = I('get.returntype');
		//获取类型
		$this->savedir = I('get.savedir');
		//获取类型
		$this->type = I('get.type','files');
		//返回容器
		$this->origin_domid = I('get.myid');
		//返回容器的iframe
		$this->iframe = I('get.iframe');
		//最原始目录
		$this->oringi_path = "./Public/Uploads/".$this->savedir."/".$this->type;

		//获取目录
		$uploadfilePath = I('get.dir',"");
		if(empty($uploadfilePath)){
			$uploadfilePath = $this->oringi_path."/*";
		} else {
			$uploadfilePath = decode($uploadfilePath)."/*";
		}

		//取出来的中文会乱码，暂时不考虑
		$dirs = glob($uploadfilePath,GLOB_NOESCAPE);
		$dirall = array();
		foreach ($dirs as $key => $value) {
			if(is_file($value)){
				//判断文件类型
				$pathinfo = pathinfo($value);
				$imagesarr = array('jpg','png','gif','bmp');
				if(in_array($pathinfo['extension'],$imagesarr)){
					$pathinfo['type'] = 'images';
				} else {
					$pathinfo['type'] = 'files';
					if(!file_exists("./Public/images/uploadfile/".$pathinfo['extension'].".png")){
						$pathinfo['extension'] = 'readme';
					}
				}

				$pathinfo['filepath'] = substr($value,1);
				$dirall['files'][] = $pathinfo;
			} else if(is_dir($value)) {
				$arr = explode("/", $value);
				$dirarr = array('path'=>$value,'pathname'=>$arr[count($arr)-1]);
				$dirall['dirs'][] = $dirarr;
			}
		}
		//当前目录
		$this->path = substr($uploadfilePath,0,strlen($uploadfilePath)-2);
		//上一目录
		$prevarr = explode("/", $this->path);
		unset($prevarr[count($prevarr)-1]);
		$prevpath = implode("/",$prevarr);
		$this->prevpath = $prevpath;
		$this->data = $dirall;
		$this->display();
	}
}
?>
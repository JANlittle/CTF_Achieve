<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 公共函数库 ]
*/

/**
 * [p 变量打印函数]
 * @param  string|array $arr [变量]
 * @return [void]
 */
function p($arr = '')
{
	echo "<pre>";
	print_r($arr);
	echo "</pre>";
}

/**
 * [changeTimeType 将秒转成时分秒]
 * @param  [type] $seconds [description]
 * @return [type]          [description]
 */
function changeTimeType($seconds){
	if($seconds > 3600){
		$hours = intval($seconds/3600);
		$left_minutes = ($seconds - $hours*3600);
		$time = $hours.":".gmstrftime('%M:%S', $left_minutes);
	} else {
		$time = gmstrftime('%H:%M:%S', $seconds);
	}
	return $time;
}

/**
 * 格式化字节大小
 * @param  number $size      字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 */
function format_bytes($size, $delimiter = '') {
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
    return round($size, 2) . $delimiter . $units[$i];
}

/**
 * [authcode 数据加密函数]
 * @param  [string]  $string    [加密数据]
 * @param  string  $operation [加密类型：DECODE|ENCODE]
 * @param  string  $key       [加密密钥]
 * @param  integer $expiry    [明文key有效期]
 * @return [string]             [加密字符串]
*/
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length = 4;

	$key = md5($key ? $key : UC_KEY);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
				return '';
			}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}
}

/**
 * [check_verify description]
 * @param  [string] $code [验证码]
 * @param  string $id     [验证码id]
 * @return [bool]         [true|false]
 */
function check_verify($code, $id = ''){
   $verify = new \Think\Verify();
   return $verify->check($code, $id);
}

/**
 * [groupArray 数组按照某个键值的值进行分组]
 * @param  array  $arr  [原始数组]
 * @param  string $name [键值]
 * @return [array]      [返回数组]
 */
function groupArray($arr = array(),$name = "")
{
	$array = array();
	if(is_array($arr) && !empty($arr) && !empty($name)){
		foreach ($arr as $key => $value) {
			foreach ($arr as $key_1 => $value_1) {
				if($value[$name] == $value_1[$name]){
					$array[$value[$name]][$key] = $value;
				}
			}
		}
	}
	return $array;
}

/**
 * [unlimitedForLayer 无限极函数：所有数据放到父级下]
 * @param  [array]  $array [数组]
 * @param  string  $name   [子类键值]
 * @param  integer $pid    [父元素id]
 * @return [array]         [返回处理后的数组]
 */
function unlimitedForLayer($array = array(),$name='child',$pid=0){
	$arr = array();
	foreach ($array as $key => $value) {
		if($value['pid'] == $pid){
			$value[$name] = unlimitedForLayer($array,$name,$value['id']);
			$arr[] = $value;
		}
	}
   return $arr;
}


/**
 * [getChilds 传递一个父级的id查找子级分类]
 * @param  [array]  $array [数组]
 * @param  integer $pid    [父级id]
 * @return [array]         [子类数组]
 */
function getChilds($array, $pid=0){
	$arr = array();
	foreach ($array as $key => $value) {
		if($value['pid'] == $pid){
			$arr[] = $value;
			$arr = array_merge($arr,getChilds($array,$value['id']));
		}
	}
	return $arr;
}


/**
 * [getParents 传递一个子级id获取父级分类]
 * @param  [type]  $array [description]
 * @param  integer $id    [description]
 * @return [type]         [description]
 */

function getParents($array,$id = 1)
{
    $arr = array();
    foreach ($array as $key => $value) {
        if($value['id'] == $id){
            $arr[] = $value;
            $arr = array_merge($arr,getParents($array,$value['pid']));
        }
    }
    return $arr;
}

/**
 * [getSelectedOption 获取被选中的无限极下拉菜单，选出被选中那一项：方法待修改]
 * @param  array   $array     [数组]
 * @param  integer $pid       [父级id]
 * @param  integer $selecteId [选中项id]
 * @param  string  $link      [子类菜单连接符]
 * @param  string $defaultValue [option值字段名]
 * @param  string  $defaultLabelName [option里内容字段名]
 * @return [type]             [返回option]
 */
function getSelectedOption($array = array(), $pid = 0, $selecteId = 0, $link = '└',$defaultValue="id",$defaultLabelName="name"){
	$str = '';
	foreach ($array as $key => $value) {
		if($value['pid'] == $pid){

			$str .= "<option value='".$value[$defaultValue]."' ";
			if($value['id'] == $selecteId){
				$str .= " selected='selected'";
			}
			$str .=   " >" . $link . $value[$defaultLabelName] . "</option>";
			$linka = str_repeat("&nbsp;",5) . $link;
			$str .= getSelectedOption($array, $value[$defaultValue], $selecteId, $linka,$defaultValue,$defaultLabelName);
		}
	}
    return $str;
}


/**
 * [action_log 记录日志，并执行该行为的规则]
 * @param  [int] $record_id    [触发行为的记录id]
 * @param  [int] $user_type    [用户类型：1-系统,2-前台会员]
 * @param  [string] $action    [行为标识]
 * @param  [string] $model     [触发行为的表名]
 * @param  [type] $user_id     [执行的用户id]
 * @param  [int] $open         [是否开启日志：默认否]
 * @return [bool]
*/
function action_log($user_type = 1,$user_id = null,$action = null, $model =  null, $record_id = null,$open = 0,$siteid = 0, $ip = ''){
	//判断是否开启日志
	if(!$open){
		return;
	}
	//日志参数为空或者错误不执行之后的操作
	if(empty($action) || empty($model) || !is_numeric($record_id) || !is_numeric($user_id)){
		return '日志行为参数不能为空或者设置不正确！';
	}

	//获取行为信息
	$action_info = M('Action')->field('id,name,rule,log,type')->where("status = 1")->getByName($action);
	if(!$action_info){
		return "该行为规则被删除或者禁用！";
	}

	//日志信息
	$data['user_type'] = $user_type; //行为类型1-系统,2-会员与行为规则表的对应
	$data['user_id'] = $user_id; //用户id
	$data['action_id'] = $action_info['id']; //行为id
	if(!empty($ip)){
		$data['action_ip'] = $ip; //行为ip
	} else {
		$data['action_ip'] = get_client_ip(); //行为ip
	}
	$data['model'] = $model; //行为操作表
	$data['record_id'] = $record_id; //行为记录id
	$data['siteid'] = $siteid; //站点id
	$data['create_time'] = time(); //行为时间

	//获取用户信息
	if($user_type == 1){
		//获取后台管理员信息
		$userinfo = M('Adminuser')->find($user_id);
	} elseif($user_type == 2) {
		//获取ucente中心的用户信息
		$userinfo = M(C('UCENTER_DB_TABLE_MEMBERS'),C('UCENTER_DB_PREFIX'),C('UCENTER_DB_DSN'))->where("uid = {$user_id}")->find();
		if($userinfo['type'] != 'system'){
			$userinfo['username'] = $userinfo['openid'];
		}
	}

	//执行日志规则
	if(!empty($action_info['log'])){
		//解析日志规则
		preg_match_all("/\[(\S+?)\]/", $action_info['log'], $matches);

		//设置可以解析的数组
		$array = array('user'=>$userinfo['username'],'date'=>date("Y-m-d H:i:s"));
		if(!empty($matches[1])){
			foreach ($matches[1] as $key => $value) {
				if(array_key_exists($value,$array)){
					//替换数组
					$arr[] = $array[$value];
				} else {
					//删除不支持的项
					unset($matches[0][$key]);
				}
			}
		}
		//重新索引
		$matches[0] = array_values($matches[0]);
		$data['remark'] = str_replace($matches[0], $arr, $action_info['log']);
	} else {
		$data['remark'] = $userinfo['username'] . ' 在 ' . date("Y-m-d H:i:s") . " 操作url：" . __SELF__ ;
	}

	//插入日志数据
	M('Actionlog')->add($data);

	//处理行为规则
	if(!empty($action_info['rule'])){
		//解析规则
		$rules = parse_action($action_info['rule'],$user_id,$siteid);

		//执行规则
		execute_action($rules,$action_info['id'],$user_id,$user_type,$siteid);
	}
}

/**
 * parse_action('table:adminuser|field:score|condition:id={$uid}|rule:score+10|cycle:24|max:1;table:adminuser|field:score|condition:id={$uid}|rule:score+10',1);
 * 规则字段解释：table->要操作的数据表，不需要加表前缀；
 *              field->要操作的字段；
 *              condition->操作的条件，目前支持字符串，默认变量{$self}为执行行为的用户
 *              rule->对字段进行的具体操作，目前支持四则混合运算，如：1+score*2/2-3
 *              cycle->执行周期，单位（小时），表示$cycle小时内最多执行$max次
 *              max->单个周期内的最大执行次数（$cycle和$max必须同时定义，否则无效）
 * [parse_action 解析行为规则]
 * @param  string $rules [行为规则]
 * @param  [int] $uid    [用户id]
 * @return [array]       [返回解析后的数组]
 */
function parse_action($rules = '',$uid,$siteid){
	if(empty($rules)){
		return;
	}

	//替换uid、siteid
	$rules = str_replace('{$uid}', $uid, $rules);
	$rules = str_replace('{$siteid}', $siteid, $rules);

	//分割
	$rules = explode(";", $rules);
	$array = array();
	if(!empty($rules)){
		foreach ($rules as $key => $value) {
			$rule = explode("|", $value);
			foreach ($rule as $key_1=> $value_1) {
				$fielda = empty($value_1) ? array() : explode(':', $value_1);
				if(!empty($fielda)){
					$array[$key][$fielda[0]] = $fielda[1];
				}
			}

			//如果周期最大执行次数有一个不存在则删除这天规则
			if(!array_key_exists('max', $array[$key]) || !array_key_exists('cycle', $array[$key])){
				unset($array[$key]['max'],$array[$key]['cycle']);
			}
		}
	}
	return $array;
}

/**
 * [execute_action 执行行为规则]
 * @param  [array] $rules     [行为规则]
 * @param  [int] $action_id   [行为id]
 * @param  [int] $user_id     [用户id]
 * @param  [int] $user_type   [用户类型：1-系统，2-会员]
 */
function execute_action($rules,$action_id,$user_id,$user_type,$siteid)
{
	foreach ($rules as $key => $value) {
		//表不存在不可行
		if(empty($value['table']) || !isset($value['table'])){
			continue;
		}

		//判断执行周期和最大执行次数,cycle按小时来算的
		if(isset($value['max']) && isset($value['cycle'])){
			$where['user_id'] = array('eq',$user_id);
			$where['user_type'] = array('eq',$user_type);
			$where['action_id'] = array('eq',$action_id);
			$where['create_time'] = array('gt',time()-3600*$value['cycle']);

			//站点
			$where['siteid'] = array('eq',$siteid);
			$count = M('Actionlog')->where($where)->count();
			if($count > $value['max']){
				continue;
			}
		}

		//实例化表
		$model = M(ucfirst($value['table']));
		$model->where($value['condition'])->setField($value['field'],array('exp',$value['rule']));
	}
}

/**
 * 删除整个目录
 * @param $dir
 * @return bool
 */
function delDir( $dir )
{
    //先删除目录下的所有文件：
    $dh = opendir( $dir );
    while ( $file = readdir( $dh ) ) {
        if ( $file != "." && $file != ".." ) {
            $fullpath = $dir . "/" . $file;
            if ( !is_dir( $fullpath ) ) {
                unlink( $fullpath );
            } else {
                delDir( $fullpath );
            }
        }
    }
    closedir( $dh );
    //删除当前文件夹：
    return rmdir( $dir );
}

/**
 * [SendMail 发送邮件函数基于PHPMailer]
 *格式：SendMail("1010117575@qq.com","标题","内容");
 * @param [string] $address [接收邮件地址]
 * @param [string] $title   [发送标题]
 * @param [string] $message [发送的内容]
 */
function SendMail($address,$title,$message)
{
    import('Api.PHPMailer.class#phpmailer',APP_PATH,'.php');
    import('Api.PHPMailer.class#smtp',APP_PATH,'.php');

    $mail = new PHPMailer();
    // 设置PHPMailer使用SMTP服务器发送Email
    $mail->IsSMTP();

    // 设置邮件的字符编码，若不指定，则为'UTF-8'
    $mail->CharSet='UTF-8';

    //设置服务器端口号
    $mail->Port = C('MAIL_PORT');

    // 添加收件人地址，可以多次使用来添加多个收件人
    $mail->AddAddress($address);

    // 设置邮件正文
    $mail->Body=$message;

    // 设置邮件头的From字段。
    $mail->From=C('MAIL_ADDRESS');

    // 设置发件人名字
    $mail->FromName = C('MAIL_FROMUSERNAME');

    // 设置邮件标题
    $mail->Subject = $title;

    // 设置SMTP服务器
    $mail->Host = C('MAIL_SMTP');

    // 设置为"需要验证"
    $mail->SMTPAuth=true;

    // 设置用户名和密码
    $mail->Username = C('MAIL_LOGINNAME');
    $mail->Password = C('MAIL_PASSWORD');

    //$mail->WordWrap = 50; //设置每行字符长度
    //$mail->IsHTML(C('MAIL_ISHTML')); // 是否HTML格式邮件
    //$mail->AltBody = "This is the body in plain text for non-HTML mail clients"; //邮件正文不支持HTML的备用显示
    // 发送邮件
    return($mail->Send());
}

/**
 * [loadPHPExcel 导出数据到Excel表，基于PHPExcel类库]
 * $_fields与$data要一一对应
 * @param  string $fileName [导出文件名称]
 * @param  array  $_fields  [导出中文字段数组：array('编号','内容','时间')]
 * @param  array  $data     [导出对应字段数组内容：array(1,'内容',2015-05-16)]
 * @return [type]           [description]
 */
function exportData($fileName = '',$_fields = array(),$data = array())
{
	if(empty($fileName)){
		$fileName = date('YmdHis');
	}
	//加载分组下的公共缓存处理类
    import('Api.PHPExcel.PHPExcel',APP_PATH,'.php');
    import('Api.PHPExcel.PHPExcel.Writer.Excel2007',APP_PATH,'.php');
    import('Api.PHPExcel.PHPExcel.Writer.Excel5',APP_PATH,'.php');
    import('Api.PHPExcel.PHPExcel.IOFactory',APP_PATH,'.php');
    //导出数据
    getExcel($fileName,$_fields,$data);
}

/*
*导出数据表
*param $fileName 名称
*param $headArr 头部数组
*param $data 数据
*/
/**
 * [getExcel 导出数据表]
 * $_fields与$data要一一对应
 * @param  string $fileName [导出文件名称]
 * @param  array  $_fields  [导出中文字段数组：array('编号','内容','时间')]
 * @param  array  $data     [导出对应字段数组内容：array(1,'内容',2015-05-16)]
 * @return [type]           [description]
 */
function getExcel($fileName,$headArr,$data){
    if(empty($data) || !is_array($data)){
        die("data must be a array");
    }
    if(empty($fileName)){
        exit;
    }
    $date = date("YmdHis",time());
    $fileName .= "_{$date}.xlsx";
    //$fileName .= "_{$date}.xls";

    //创建新的PHPExcel对象
    $objPHPExcel = new \PHPExcel();
    $objProps = $objPHPExcel->getProperties();

    //设置表头
    $key = ord("A");
    $key2 = ord("@");//@--64
    foreach($headArr as $v){
        //$colum = chr($key);
        if($key>ord("Z")){
            $key2 += 1;
            $key = ord("A");
            $colum = chr($key2).chr($key);//超过26个字母时才会启用  
        }else{
            if($key2>=ord("A")){
                $colum = chr($key2).chr($key);//超过26个字母时才会启用  
            }else{
                $colum = chr($key);
            }
        }
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum.'1', $v);
        $key += 1;
    }

    $column = 2;
    $objActSheet = $objPHPExcel->getActiveSheet();
    foreach($data as $key => $rows){ //行写入  
        $span = ord("A");  
        $span2 = ord("@");
        foreach($headArr as $k=>$v){  
            if($span>ord("Z")){  
                $span2 += 1;  
                $span = ord("A");  
                $j = chr($span2).chr($span);//超过26个字母时才会启用  dingling 20150626  
            }else{  
                if($span2>=ord("A")){  
                    $j = chr($span2).chr($span);  
                }else{  
                    $j = chr($span);  
                }  
            }
            //$j = chr($span);
            $inputdata = array_values($rows);//设置索引
            $objActSheet->setCellValue($j.$column, strip_tags($inputdata[$k]));  
            $span++;  
        }  
        $column++;  
    }  

    $fileName = iconv("gb2312", "utf-8", $fileName);
    //重命名表
    $objPHPExcel->getActiveSheet()->setTitle('Simple');
    //设置活动单指数到第一个表,所以Excel打开这是第一个表
    $objPHPExcel->setActiveSheetIndex(0);
    //将输出重定向到一个客户端web浏览器(Excel2007)
          header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
          header("Content-Disposition: attachment; filename=\"$fileName\"");
          header('Cache-Control: max-age=0');
          $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        // if(!empty($_GET['excel'])){
        //     $objWriter->save('php://output'); //文件通过浏览器下载
        // }else{
        //   $objWriter->save($fileName); //脚本方式运行，保存在当前目录
        // }
          $objWriter->save('php://output');
  exit;
}

/**
 * [msubstr 字符串截取]
 * @param  [string]  $str     [字符串]
 * @param  integer $start     [开始位置]
 * @param  integer $length    [截取长度]
 * @param  string  $charset   [字符编码,默认：utf-8]
 * @param  boolean $suffix    [是否开启后缀]
 * @return [string]           [截取后的字符串]
 */
function msubstr($str, $start=0, $length = 0, $charset="utf-8", $suffix=true)
{
    if(!isset($length)){
    	 if(function_exists("mb_substr")){
    	 	$length = mb_strlen($str)-1;
    	 } else {
    	 	$length = iconv_strlen($str)-1;
    	 }
    }

    if(function_exists("mb_substr")){
        if(mb_strlen($str,$charset) >= $length && $suffix)
             return mb_substr($str, $start, $length, $charset)."...";
        else
             return mb_substr($str, $start, $length, $charset);
    } else if(function_exists('iconv_substr')) {
         if(iconv_substr($str,$charset) >= $length  && $suffix)
             return iconv_substr($str,$start,$length,$charset)."...";
        else
             return iconv_substr($str,$start,$length,$charset);
    }
    $re['utf-8']   = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";  
    $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";
    $re['gbk']    = "/[x01-x7f]|[x81-xfe][x40-xfe]/";
    $re['big5']   = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("",array_slice($match[0], $start, $length));
    if($suffix) return $slice."…";
    return $slice;
}

 /**
 * 简单对称加密算法之加密
 * @param String $string 需要加密的字串
 * @param String $skey 加密EKY
 * @author Anyon Zou <zoujingli@qq.com>
 * @date 2013-08-13 19:30
 * @update 2014-10-10 10:10
 * @return String
 */
function encode($string = '', $skey = 'cxphp') {
	if(!is_numeric($string) && empty($string)){
		return '';
	}
    $strArr = str_split(base64_encode($string));
    $strCount = count($strArr);
    foreach (str_split($skey) as $key => $value)
        $key < $strCount && $strArr[$key].=$value;
    return str_replace(array('=', '+', '/'), array('O0O0O', 'o000o', 'oo00o'), join('', $strArr));
}

/**
 * 简单对称加密算法之解密
 * @param String $string 需要解密的字串
 * @param String $skey 解密KEY
 * @author Anyon Zou <zoujingli@qq.com>
 * @date 2013-08-13 19:30
 * @update 2014-10-10 10:10
 * @return String
 */
function decode($string = '', $skey = 'cxphp') {
    if(!is_numeric($string) && empty($string)){
		return '';
	}
    $strArr = str_split(str_replace(array('O0O0O', 'o000o', 'oo00o'), array('=', '+', '/'), $string), 2);
    $strCount = count($strArr);
    foreach (str_split($skey) as $key => $value)
        $key <= $strCount && $strArr[$key][1] === $value && $strArr[$key] = $strArr[$key][0];
    return base64_decode(join('', $strArr));
}


/**
 * [ismobile 检测是否是手机端访问网站]
 * @return [bool] [是手机设备：true,不是：false]
 */
function ismobile() {
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
        return true;
    //此条摘自TPM智能切换模板引擎，适合TPM开发
    if(isset ($_SERVER['HTTP_CLIENT']) &&'PhoneClient'==$_SERVER['HTTP_CLIENT'])
        return true;
    //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA']))
        //找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
    //判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array(
            'nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile'
        );
        //从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    //协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
 }

/**
 * [uploadPhoto 图片上传处理函数]
 * @param  string  $saveDir     [保存目录]
 * @param  integer $thumbWidth  [缩略图宽度]
 * @param  integer $thumbHeight [缩略图高度]
 * @param  string $water        [水印参数，默认字符串形式:格式1：open::1;type::img;content::./Public/images/water.png;position::9;opacity::80，格式2：array('open'=>1,'type'=>'img','content'=>'./Public/images/water.png','position'=>9,'opacity'=>80)]
 * @return [string|array]       [错误|图片信息]
 */
function uploadPhoto($saveDir = '',$thumbWidth = 100,$thumbHeight = 100,$water = '',$maxsize = 3072){
	if(empty($saveDir)){
		$saveDir = date("YmdHis");
	}

	//设置默认
	if(!is_numeric($maxsize) || empty($maxsize)){
		$maxsize = 3072;
	}

	$upload = new \Think\Upload();
	$upload->maxSize = $maxsize*1024; //文件上传的最大文件大小
	$upload->rootPath = "./"; //文件上传保存的根路径
	$upload->savePath = $upload->rootPath."Public/Uploads/".$saveDir."/";//文件上传的保存路径
	$upload->saveName = date("YmdHis")."_".uniqid(); //上传文件的保存规则
	$upload->replace = true; //存在同名文件是否是覆盖
	$upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 允许上传的文件后缀
	$upload->mimes = array('image/gif', 'image/jpeg', 'image/png'); //允许上传的文件类型
	$upload->autoSub = true;//自动使用子目录保存上传文件
	$upload->subName = date("Ymd");//子目录创建方式，采用数组或者字符串方式定义
	$upload->hash = true;//是否生成文件的hash编码 默认为true

	if(!$info = $upload->upload()){
		return $upload->getError();
	} else {
		$image = new \Think\Image();
		//开启缩略图
		$thumb_path = $info[0]['savepath']."thumb";
		if(!is_dir($thumb_path)){
			mkdir($thumb_path,0777);
		}
		if(!empty($info)){
			foreach ($info as $key => $value) {
				$image->open($value['savepath'].$value['savename']);

				//处理水印
				water($value['savepath'].$value['savename'],$water);

				//处理缩略
				$thumb_file_path = $thumb_path."/thumb_".$value['savename'];
		        $image->thumb($thumbWidth,$thumbHeight,\Think\Image::IMAGE_THUMB_CENTER)->save($thumb_file_path);
		        //把缩略图的路径添加到数组中
		        $info[$key]['thumbpath'] = substr($thumb_file_path,1);
			}
		}
		return $info;
	}
}

/**
 * [water 系统水印]
 * @param  [type] $file  [水印文件：array('./1.jpg','./2.jpg')|'./1.jpg']
 * @param  string $water [水印参数]
 * @return [bool]
 */
function water($file,$water = '')
{
	if(empty($file)){
		return;
	}
/*配置水印*/
	//字符串形式
	if(is_string($water)){
		//解析水印字符串
		$water_arr = array();
		if(!empty($water)){
			$water_arr = explode(';',$water);
		}

		//水印配置数组参数
		$water = array();
		if(!empty($water_arr)){
			foreach ($water_arr as $key => $value) {
				list($water_key,$water_value) = explode("::",$value);
				$water[$water_key] = $water_value;
			}
		}
	}

	//如果没有存在则关闭
	//是否开启
	if(!isset($water['open']) || $water['open'] == 0){
		$water['open'] = 0;
	} else {

		//类型
		if(!isset($water['type'])){
			$water['type'] = 'img';
		}

		//内容
		if(!isset($water['content'])){
			$water['content'] = './Public/images/water.png';
		}

		//位置
		if(!isset($water['position'])){
			$water['position'] = 9;
		}

		//透明度
		if(!isset($water['opacity'])){
			$water['opacity'] = 80;
		}

		//如果是字体水印
		if($water['type'] == 'text'){
			//字体
			if(!isset($water['font'])){
				$water['font'] = './Public/images/1.ttf';
			}

			//字体大小
			if(!isset($water['fontsize'])){
				$water['fontsize'] = 20;
			}

			//字体颜色
			if(!isset($water['color'])){
				$water['color'] = '#000000';
			}

			//偏移量
			if(!isset($water['offset'])){
				$water['offset'] = 0;
			}

			//角度
			if(!isset($water['angle'])){
				$water['angle'] = 0;
			}
		}
	}

/*end 配置水印*/
	//处理水印
	$image_water = new \Think\Image();
	if($water['open'] == 1){

		//如果文件列表是字符串，那么转成数组形式
		$water_file_arr = array();
		if(is_string($file)){
			$water_file_arr[] = $file;
		} else if(is_array($file)){
			$water_file_arr = $file;
		}


		//批量水印
		foreach ($water_file_arr as $key => $value) {
			//文件不存在之前跳过
			if(!is_file($value)){
				continue;
			}

			//处理水印
			$image_water->open($value);
			if($water['type'] == 'img'){
				$image_water->water($water['content'],$water['position'],$water['opacity'])->save($value);
			} else {
				$image_water->text($water['content'],$water['font'],$water['fontsize'],$water['color'],$water['position'],$water['offset'],$water['angle'])->save($value);
			}
		}
	}
}

/**
 * [setConfig 生成配置缓存]
 * @param string $name [缓存名称]
 * @param string $siteid [站点id]
 */
function setConfig($name = 'ALL_CONFIG',$siteid = array())
{
	$config  = M('Config');
	$where['status'] = 1;
	if(!empty($siteid)){
		$where['siteid'] = array('in',$siteid);
	}
	$data = $config->field('id,name,title,value')->where($where)->order('sort ASC')->select();
	//设置缓存数组
	$cache_data = array();
	if(!empty($data)){
		foreach ($data as $key => $value) {
			$cache_data[$value['name']] = $value['value'];
		}
	}

	//先删除缓存
	$previous_cache = S($name);
	if(!empty($previous_cache)){
		S($name ,null);
	}
	//设置缓存
	S($name, $cache_data);
}


/**
 * [get_url 获取当前url地址]
 * @return [type] [description]
 */
 function get_url() {
    $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
    $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
    $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
    $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
    return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
 }

 /**
 * [get_host 获取当前域名]
 * @return [type] [description]
 */
 function get_host() {
    $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
    return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
 }

 /* 
 * 经典的概率算法， 
 * $proArr是一个预先设置的数组， 
 * 假设数组为：array(100,200,300，400)， 
 * 开始是从1,1000 这个概率范围内筛选第一个数是否在他的出现概率范围之内，  
 * 如果不在，则将概率空间，也就是k的值减去刚刚的那个数字的概率空间， 
 * 在本例当中就是减去100，也就是说第二个数是在1，900这个范围内筛选的。 
 * 这样 筛选到最终，总会有一个数满足要求。 
 * 就相当于去一个箱子里摸东西， 
 * 第一个不是，第二个不是，第三个还不是，那最后一个一定是。 
 * 这个算法简单，而且效率非常 高， 
 * 关键是这个算法已在我们以前的项目中有应用，尤其是大数据量的项目中效率非常棒。 
 */  
function prizeRandom($proArr) {   
    $result = '';    
    //概率数组的总概率精度   
    $proSum = array_sum($proArr);    
    //概率数组循环   
    foreach ($proArr as $key => $proCur) {   
        $randNum = mt_rand(1, $proSum);   
        if ($randNum <= $proCur) {   
            $result = $key;   
            break;   
        } else {   
            $proSum -= $proCur;   
        }         
    }   
    unset ($proArr);    
    return $result;   
}

/**
	* 发送HTTP请求方法，目前只支持CURL发送请求
	* @param  string $url    请求URL
	* @param  array  $param  GET参数数组
	* @param  array  $data   POST的数据，GET请求时该参数无效
	* @param  string $method 请求方法GET/POST
	* @return array          响应数据
*/
function gethttp($url, $param, $data = '', $method = 'GET'){
    $opts = array(
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    );

    /* 根据请求类型设置特定参数 */
    $opts[CURLOPT_URL] = $url . '?' . http_build_query($param);

    if(strtoupper($method) == 'POST'){
        $opts[CURLOPT_POST] = 1;
        $opts[CURLOPT_POSTFIELDS] = $data;
        
        if(is_string($data)){ //发送JSON数据
            $opts[CURLOPT_HTTPHEADER] = array(
                'Content-Type: application/json; charset=utf-8',  
                'Content-Length: ' . strlen($data),
            );
        }
    }

    /* 初始化并执行curl请求 */
    $ch = curl_init();
    curl_setopt_array($ch, $opts);
    $data  = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if($error){
    	return $error;
    }
    return  $data;
}

/**
 * [clearArrEmpty 清空数组中的空值]
 * @param  array  $arr [description]
 * @return [type]      [description]
 */
function clearArrEmpty($arr = array())
{
	if(!empty($arr)){
		foreach ($arr as $key => $value) {
			if(empty($value)){
				unset($arr[$key]);
			}
		}
	}
	return $arr;
}

/*关联链接*/
/**
 * [ReplaceKeyworeds 替换关联链接]
 * @param array   $arr [替换进去的数组]
 * @param string  $str [被替换的内容]
 * @param integer $num [替换的个数]
 */
function ReplaceKeyworeds($arr = array(), $str = '' , $num = 1){
	if(empty($arr) || empty($str)){
		return $str;
	}

	//script标签
	$script = "/\<script(.*)\>(.*)\<\/script\>/Umi";
	preg_match_all($script, $str, $matchs_script);
	$script_replace_key = 'GeyoutuReplayKeyWords_Script';
	$str = preg_replace($script,$script_replace_key,$str);

	//css标签
	$css = "/\<link(.*)\>/Umi";
	preg_match_all($css, $str, $matchs_css);
	$css_replace_key = 'GeyoutuReplayKeyWords_Css';
	$str = preg_replace($css,$css_replace_key,$str);

	//a标签
	$alink = "/\<a(.*)\>(.*)\<\/a\>/Umi";
	preg_match_all($alink, $str, $matchs_alink);
	$alink_replace_key = 'GeyoutuReplayKeyWords_Alink';
	$str = preg_replace($alink,$alink_replace_key,$str);


	//全部属性
	$all_atrr = "/\<(.*)\>/Umi";
	preg_match_all($all_atrr, $str, $matchs_all_atrr);
	$all_atrr_replace_key = 'GeyoutuReplayKeyWords_All_Atrr';
	$str = preg_replace($all_atrr,$all_atrr_replace_key,$str);

	//title属性
	/*$title = "/(title\s*=\s*)[\"|\'](.*?)[\"|\']/ise";
	preg_match_all($title, $str, $matchs_title);
	$title_replace_key = 'GeyoutuReplayKeyWords_Title';
	$str = preg_replace($title,$title_replace_key,$str);*/

	//alt属性
	/*$alt = "/(alt\s*=\s*)[\"|\'](.*?)[\"|\']/ise";
	preg_match_all($alt, $str, $matchs_alt);
	$alt_replace_key = 'GeyoutuReplayKeyWords_Alt';
	$str = preg_replace($alt,$alt_replace_key,$str);*/

	//处理替换关键字
	$str = replacekeywords($arr, $str, $num);

	//替换回来
	$str = KeyWordsBack($matchs_script[0], $str, $script_replace_key);
	$str = KeyWordsBack($matchs_css[0], $str, $css_replace_key);
	$str = KeyWordsBack($matchs_alink[0], $str, $alink_replace_key);
	$str = KeyWordsBack($matchs_all_atrr[0], $str, $all_atrr_replace_key);
	//$str = KeyWordsBack($matchs_title[0], $str, $title_replace_key);
	//$str = KeyWordsBack($matchs_alt[0], $str, $alt_replace_key);
	return $str;
}

/**
 * [KeyWordsBack 替换回来]
 * @param [type]  $arr               [原始匹配到的数组]
 * @param [type]  $str               [替换之后的数组]
 * @param [type]  $title_replace_key [替换关键字]
 * @param integer $num               [返回添加关联链接的原始内容]
 */
function KeyWordsBack($arr, $str, $replace_key, $num = 1){
	foreach($arr as $key => $value){
		$str = preg_replace('/'.$replace_key.'/', $value, $str, $num);
	}
	return $str;
}

/**
 * [replacekeywords 处理替换]
 * @param  [type]  $arr [替换的数组]
 * @param  [type]  $str [被替换的内容]
 * @param  integer $num [替换次数]
 * @return [type]       [返回替换之后的内容]
 */
function replacekeywords($arr, $str, $num = 1){
	foreach($arr as $key => $value){
		if(empty($value['url'])){
			$value['url'] = U("/tag/{$value['title']}","");
		}
		if(!empty($value['titlecolor'])){
			$titlecolor = "style='color:".$value['titlecolor'] ."'";
		} else {
			$titlecolor = '';
		}

		//替换之前先替换掉a标签
		/*$alink = "/\<a(.*)\>(.*)\<\/a\>/Umi";
		preg_match_all($alink, $str, $matchs_alink);
		$alink_replace_key = 'GeyoutuReplayKeyWords_Alink';
		$str = preg_replace($alink,$alink_replace_key,$str);*/

		//进行替换
		$replace = "<a href='".$value['url']."' target='_blank' ".$titlecolor.">".$value['title']."</a>";
		$str = preg_replace('/'.$value['title'].'/',$replace , $str, $num);

		//替换回来
		//$str = KeyWordsBack($matchs_alink[0], $str, $alink_replace_key);
	}
	return $str;
}
/*end 关联链接*/

/**
 * [hiddenemail 隐藏邮件部分]
 * @param  string  $email  [description]
 * @param  integer $start  [description]
 * @param  integer $length [description]
 * @return [type]          [description]
 */
function hiddenemail($email = 'copylian@aikehou.com',$start = 3, $length = 4){
	$arr = explode('@',$email);

	//长度为1时不截取，小于开始截取位置时
	if(strlen($arr[0]) > 1 && strlen($arr[0]) <= $start){
		$arr[0] = substr_replace($arr[0],'****',1,1);
	} elseif(strlen($arr[0]) > $start && strlen($arr[0]) - $start <= $length) {
		$arr[0] = substr_replace($arr[0],'****',$start,2);
	} else {
		$arr[0] = substr_replace($arr[0],'****',$start,$length);
	}
	return implode("@",$arr);
}

/**
 * [randcode 获取0-9,a-z,A-Z的随机数]
 * @param  integer $length [description]
 * @return [type]          [description]
 */
function randcode($length = 6){
	$range_arr = array_merge(range('a','z'),range('A','Z'),range(0,9));
	shuffle($range_arr);
	$range_arr = array_splice($range_arr,0,$length);
	return implode('',$range_arr);
}

/**
 * [echouniqid 获取唯一标识符]
 * @param  integer $id     [description]
 * @param  string  $prefix [description]
 * @return [type]          [description]
 */
function aikehou_uniqid($id = '',$prefix = 'aikehou_'){
	if(empty($id)){
		$id = mt_rand();
	}
	return $id.md5($id.uniqid($prefix));
}

/**
 * [resolve_comments 解析评论表情]
 * @param  string $str  [被解析字符串]
 * @param  string $path [图像路径]
 * @return [type]       [description]
 */
function resolve_comments($str = '',$path ='/Public/images/comments/')
{
	//空的话直接返回
	if(empty($str)){
		return $str;
	}

	//定要返回字符串
	$face_str = '';

	//表情数组
	$face_arr = array(
		array("faceName"=>"微笑","facePath"=>"0.gif"),
		array("faceName"=>"撇嘴","facePath"=>"1.gif"),
		array("faceName"=>"色","facePath"=>"2.gif"),
		array("faceName"=>"发呆","facePath"=>"3.gif"),
		array("faceName"=>"得意","facePath"=>"4.gif"),
		array("faceName"=>"流泪","facePath"=>"5.gif"),
		array("faceName"=>"害羞","facePath"=>"6.gif"),
		array("faceName"=>"闭嘴","facePath"=>"7.gif"),
		array("faceName"=>"大哭","facePath"=>"9.gif"),
		array("faceName"=>"尴尬","facePath"=>"10.gif"),
		array("faceName"=>"发怒","facePath"=>"11.gif"),
		array("faceName"=>"调皮","facePath"=>"12.gif"),
		array("faceName"=>"龇牙","facePath"=>"13.gif"),
		array("faceName"=>"惊讶","facePath"=>"14.gif"),
		array("faceName"=>"难过","facePath"=>"15.gif"),
		array("faceName"=>"酷","facePath"=>"16.gif"),
		array("faceName"=>"冷汗","facePath"=>"17.gif"),
		array("faceName"=>"抓狂","facePath"=>"18.gif"),
		array("faceName"=>"吐","facePath"=>"19.gif"),
		array("faceName"=>"偷笑","facePath"=>"20.gif"),
		array("faceName"=>"可爱","facePath"=>"21.gif"),
		array("faceName"=>"白眼","facePath"=>"22.gif"),
		array("faceName"=>"傲慢","facePath"=>"23.gif"),
		array("faceName"=>"饥饿","facePath"=>"24.gif"),
		array("faceName"=>"困","facePath"=>"25.gif"),
		array("faceName"=>"惊恐","facePath"=>"26.gif"),
		array("faceName"=>"流汗","facePath"=>"27.gif"),
		array("faceName"=>"憨笑","facePath"=>"28.gif"),
		array("faceName"=>"大兵","facePath"=>"29.gif"),
		array("faceName"=>"奋斗","facePath"=>"30.gif"),
		array("faceName"=>"咒骂","facePath"=>"31.gif"),
		array("faceName"=>"疑问","facePath"=>"32.gif"),
		array("faceName"=>"嘘","facePath"=>"33.gif"),
		array("faceName"=>"晕","facePath"=>"34.gif"),
		array("faceName"=>"折磨","facePath"=>"35.gif"),
		array("faceName"=>"衰","facePath"=>"36.gif"),
		array("faceName"=>"骷髅","facePath"=>"37.gif"),
		array("faceName"=>"敲打","facePath"=>"38.gif"),
		array("faceName"=>"再见","facePath"=>"39.gif"),
		array("faceName"=>"擦汗","facePath"=>"40.gif"),
		array("faceName"=>"抠鼻","facePath"=>"41.gif"),
		array("faceName"=>"鼓掌","facePath"=>"42.gif"),
		array("faceName"=>"糗大了","facePath"=>"43.gif"),
		array("faceName"=>"坏笑","facePath"=>"44.gif"),
		array("faceName"=>"左哼哼","facePath"=>"45.gif"),
		array("faceName"=>"右哼哼","facePath"=>"46.gif"),
		array("faceName"=>"哈欠","facePath"=>"47.gif"),
		array("faceName"=>"鄙视","facePath"=>"48.gif"),
		array("faceName"=>"委屈","facePath"=>"49.gif"),
		array("faceName"=>"快哭了","facePath"=>"50.gif"),
		array("faceName"=>"阴险","facePath"=>"51.gif"),
		array("faceName"=>"亲亲","facePath"=>"52.gif"),
		array("faceName"=>"吓","facePath"=>"53.gif"),
		array("faceName"=>"可怜","facePath"=>"54.gif"),
		array("faceName"=>"菜刀","facePath"=>"55.gif"),
		array("faceName"=>"西瓜","facePath"=>"56.gif"),
		array("faceName"=>"啤酒","facePath"=>"57.gif"),
		array("faceName"=>"篮球","facePath"=>"58.gif"),
		array("faceName"=>"乒乓","facePath"=>"59.gif"),
		array("faceName"=>"拥抱","facePath"=>"60.gif"),
		array("faceName"=>"握手","facePath"=>"61.gif"),
		array("faceName"=>"得意地笑","facePath"=>"62.gif"),
		array("faceName"=>"听音乐","facePath"=>"63.gif")
    );

	//替换
	foreach ($face_arr as $key => $value) {
		$str = preg_replace("/\[".$value['faceName']."\]/Umi","<img src='" . $path . $value['facePath']."'/>",$str);
	}

	return $str;
}

/**
 * [isurl 验证是否是url]
 * @param  string $url [description]
 * @return [type]      [description]
 */
function isurl($url = ''){
	return preg_match('/^http[s]?:\/\/'.
		'(([0-9]{1,3}\.){3}[0-9]{1,3}'. // IP形式的URL- 199.194.52.184
		'|'. // 允许IP和DOMAIN（域名）
		'([0-9a-z_!~*\'()-]+\.)*'. // 域名- www.
		'([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.'. // 二级域名
		'[a-z]{2,6})'.  // first level domain- .com or .museum
		'(:[0-9]{1,4})?'.  // 端口- :80
		'((\/\?)|'.  // a slash isn't required if there is no file name
		'(\/[0-9a-zA-Z_!~\'\(\)\[\]\.;\?:@&=\+\$,%#-\/^\*\|]*)?)$/',
		$url) == 1;
}

/**
 * [aikehou_ad 广告调用函数]
 * @param  string $adid      [广告id]
 * @param  string $siteid    [站点id]
 * @param  string $adspaceid [广告位id]
 * @return [type]            [description]
 */
function aikehou_ad($adid = '', $siteid = '', $adspaceid = ''){

	//验证数据
	if(empty($siteid) || !is_numeric($siteid)){
        $ads_info = '非法操作';
	}

	if(empty($adspaceid) || !is_numeric($adspaceid)){
        $ads_info = '非法操作';
	}

	//广告位
	if(empty($adid) || !is_numeric($adid)){
        $Adspace = D('Adspace');
        $adspace_where['id'] = array('eq',$adspaceid);
        $adspace_where['siteid'] = array('eq',$siteid);
        $adspace_where['status'] = array('eq',1);

        //获取广告位
        $adspace_data = $Adspace->where($adspace_where)->relation('ads')->find();

        //广告位是不开启时或不存在时
        if(empty($adspace_data)){
            //$ads_info = '不存在广告位或者广告位已经关闭！';
            $ads_info = '';
        }

        //组装广告信息
        $ads_info_new = '';
        if(!empty($adspace_data) && !empty($adspace_data['ads'])){
            foreach ($adspace_data['ads'] as $key => $value) {
                //图片类型
                if(!empty($value) && $value['type'] == 1){
                    //解析图片
                    $value['thumb'] = unserialize($value['thumb']);

                    //设置链接
                    $value['links'] = empty($value['links']) ? "javascript:;" : $value['links'];
                    $ads_info = "<li><a href='".$value['links']."' target='_blank'><img src='".$value['thumb'][0]['thumb']."' width='".$adspace_data['width']."' height='".$adspace_data['height']."' alt='".$value['name']."'/></a><div class='am-slider-desc'><a href='".$value['links']."' target='_blank'>".$value['name']."</a></div></li>";
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
                    $ads_where['aid'] = array('eq',$adspaceid);
                    $ads_where['siteid'] = array('eq',$siteid);
                    $ads_where['status'] = array('eq',1);
                    M('Ads')->where($ads_where)->setInc('shownum',1);
                }
            }
        }

        //设置最后的字符串
        if(!empty($adspace_data)){
            //如果是图片类型
            if($adspace_data['type'] == 1){

                $img_info = "<div data-am-widget='slider' style='width:".$adspace_data['width']."px;height:".$adspace_data['height']."px;' class='am-slider am-slider-c2' data-am-slider='directionNav:false'><ul class='am-slides'>";
                $img_info .=  $ads_info_new;          
                $img_info .= "</ul></div>";

                $ads_info_new = $img_info;
            }
        } else {
            $ads_info_new = $ads_info;
        }

        //输出内容
        echo $ads_info_new;

	} else {
	//单条广告
		$ads = D('Ads');
		$ads_where['id'] = array('eq',$adid);
		$ads_where['aid'] = array('eq',$adspaceid);
		$ads_where['siteid'] = array('eq',$siteid);
        $ads_where['status'] = array('eq',1);

		//获取单个广告数据
		$ads_data = $ads->where($ads_where)->relation('adspace')->find();

        //广告数据为空或则，广告位是不开启时
        if(empty($ads_data) || empty($ads_data['adspace'])){
            //$ads_info = '不存在当前广告或者广告已经关闭；不存在广告位或者广告位已经关闭！';
            $ads_info = '';
        }

		//图片类型
		if(!empty($ads_data) && !empty($ads_data['adspace']) && $ads_data['type'] == 1){
			//解析图片
			$ads_data['thumb'] = unserialize($ads_data['thumb']);

			//设置链接
			$ads_data['links'] = empty($ads_data['links']) ? "javascript:;" : $ads_data['links'];
			$ads_info = "<a href='".$ads_data['links']."' target='_blank'><img src='".$ads_data['thumb'][0]['thumb']."' width='".$ads_data['adspace']['width']."' height='".$ads_data['adspace']['height']."' alt='".$ads_data['name']."'/></a>";
		} elseif(!empty($ads_data) && !empty($ads_data['adspace']) && $ads_data['type'] == 2){
		//文字类型
			
			//设置链接
			$ads_data['links'] = empty($ads_data['links']) ? "javascript:;" : $ads_data['links'];

			//设置名称
			$ads_data['words'] = empty($ads_data['words']) ? $ads_data['name'] : $ads_data['words'];

			$ads_info = "<a href='".$ads_data['links']."' target='_blank'>".$ads_data['words']."</a>";
		} elseif(!empty($ads_data) && !empty($ads_data['adspace']) && $ads_data['type'] == 3){
		//代码类型
			$ads_info = htmlspecialchars_decode($ads_data['code']);
			$ads_info = preg_replace('/\"/','\'',$ads_info);
		}

        //存在数据数据是进行判断
        if(!empty($ads_data) && !empty($ads_data['adspace'])){
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
        }

		//输出内容
		echo $ads_info;
	}
}

/**
 * [getParameter 获取url参数]
 * @param  array  $arr  [description]
 * @param  string $page [description]
 * @return [type]       [description]
 */
function getParameter($arr = array(),$page = '')
{
	
	$parameter = "";
	//将数组转成url字符串
	if(!empty($arr)){
		$parameter = http_build_query($arr);
	}
	
	//设置分页
	if(!empty($page)){
		//设置分页
		$page_parameter = "&" . $page->p . "=" . $page->nowPage;
		$parameter = $parameter . $page_parameter;
	}

	//返回数据
	return $parameter;
}

/**
 * [messocket 消息推送]
 * @param  array  $data [description]
 * @return [type]       [description]
 */
function messocket($data = array())
{
	$url = U(__SELF__,'',false,true);
    $urlinfo = parse_url($url);
    $push_api_url = $urlinfo['scheme'] . "://" . $urlinfo['host'] . ":2121/";
    gethttp($push_api_url,$data);
}

/**
 * [sortArrByField 根据某字段对多维数组进行排序的方法]
 * @param  [type]  &$array [数组]
 * @param  [type]  $field  [字段]
 * @param  boolean $desc   [排序]
 * @return [type]          [description]
 */
function sortArrByField(&$array, $field, $desc = false){
	$fieldArr = array();
	foreach ($array as $k => $v) {
		$fieldArr[$k] = $v[$field];
	}
	$sort = $desc == false ? SORT_ASC : SORT_DESC;
	array_multisort($fieldArr, $sort, $array);
}

/**
 * [uploadOne 上传单个文件]
 * @param  string  $type     [上传类型]
 * @param  string  $saveDir  [文件目录]
 * @param  integer $filesize [文件大小]
 * @param  integer $thumbw   [缩略图宽度]
 * @param  integer $thumbh   [缩略图高度]
 * @return [type]            [description]
 */
function uploadOne($type = 'images',$saveDir = 'images',$filesize = 3072,$thumbw = 200,$thumbh = 200){
	//文件上传处理
	if($type == 'images' || preg_match('/^image\//', $_FILES['Filedata']['type'][0])){
	    $info = uploadPhoto($saveDir.'/'.$type,$thumbw,$thumbh,'',$filesize);

	    if(!is_array($info)){
	        $error_data['info'] = $info;
	        $error_data['status'] = 0;
	        return $error_data;
	    }

	    $file['oringinal_type'] = 'images';
	    $file['status'] = 1;
	    $file['name'] = $_FILES['Filedata']['name'][0];
	    $file['savename'] = $info[0]['savename'];
	    $file['photo'] = substr($info[0]['savepath'],1).$info[0]['savename'];
	    $file['thumb'] = $info[0]['thumbpath'];
	    $file['location'] = 'upload';
	    $file['uniqid'] = uniqid();
	    return $file;
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
	        return $error_data;
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
	        $info[0]['ext'] = "jpg";
	        $info[0]['status'] = 1;
	        $info[0]['oringinal_type'] = 'files';
	        $info[0]['savepathall'] = substr($info[0]['savepath'],1).$info[0]['savename'];
	        $info[0]['location'] = 'upload';
	        return $info[0];
	    }
	}
}

/**
 * [delfilefun 删除上传文件(图片或附件)函数]
 * @param  array  $arr [description]
 * @return [type]      [description]
 */
function delfilefun($arr = array())
{
	$pictures_files_arr = $arr;
	if(!empty($pictures_files_arr)){
		foreach ($pictures_files_arr as $key => $value) {
			if(!empty($value)){
				foreach ($value as $key_1 => $value_1) {
					if($value_1['location'] == 'upload'){
						if($value_1['type'] == 'images'){
							if(file_exists(".".$value_1['thumb'])){
								unlink(".".$value_1['thumb']);
							}
							if(file_exists(".".$value_1['photo'])){
								unlink(".".$value_1['photo']);
							}
						} elseif($value_1['type'] == 'files') {
							if(file_exists(".".$value_1['filepath'])){
								unlink(".".$value_1['filepath']);
							}
						}
					}
				}
			}
		}
	}
}
?>
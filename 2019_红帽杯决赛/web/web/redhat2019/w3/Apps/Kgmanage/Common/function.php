<?php
/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.08 ]
* Description [ 公共函数库 ]
*/

/**
  * 权限验证
  * @param rule string|array  需要验证的规则列表,支持逗号分隔的权限规则或索引数组
  * @param uid  int           认证用户的id
  * @param string mode        执行check的模式
  * @param relation string    如果为 'or' 表示满足任一条规则即通过验证;如果为 'and'则表示需满足所有规则才能通过验证
  * @return boolean           通过验证返回true;失败返回false
  * @return author            黄药师		46914685@qq.com
*/
function authCheck($rule, $uid, $type=1, $mode='url', $relation='or'){
	//超级管理员跳过验证
	$auth = new \Think\Auth();

	//设置小写
	$no_auth_rules = C('NO_AUTH_RULES');
	$no_auth_rules = array_map('strtolower',$no_auth_rules);
	$no_rule = strtolower($rule);


	/*//获取当前uid所在的角色组id
	$groups = $auth->getGroups($uid);

	//获取group_id
	$groupids = array();
	if(!empty($groups)){
		foreach ($groups as $key => $value) {
			$groupids[] = $value['group_id'];
		}
	}
	//获取角色组的交集
	$result  = array_intersect($groupids, C('ADMINISTRATOR'));
	//设置的是一个用户对应一个角色组,所以直接取值.如果是对应多个角色组的话,需另外处理
	//if(in_array($groups[0]['group_id'], C('ADMINISTRATOR'))){ //适合单个角色组
	if(!empty($result)){ //多个角色组*/
	if(in_array(session('uid'),C('SUPERADMIN'))){
		return true;
	}else if(in_array($no_rule, $no_auth_rules)){
		return true;
	}else{
		return $auth->check($rule,$uid,$type,$mode,$relation) ? true:false;
	}
}

/**
 * [getPage 分页方法]
 * @param  [int] $count   [总记录条数]
 * @param  [int] $pagenum [显示条数]
 * @return [object]       [分页对象]
 */
function getPage($count,$pagenum = 15){

	//判断分页数是否存在，如果不存在且不是数字的话就去配置的值，如果配置没有值就默认15
	if(!isset($pagenum) || !is_numeric($pagenum)){
		
		$page_site = C('SITE_SYSTEM_PAGENUM');
		$page_new = isset($page_site) && !empty($page_site) ? $page_site : C('SYSTEM_PAGENUM');
		if(isset($page_new)){
			$pagenum = $page_new;
		} else {
			$pagenum = 15;
		}
	}

	//开始分页设置
	$page = new \Think\Page($count,$pagenum);
	$page->setConfig('header','<span class="rows">共 %TOTAL_ROW% 条记录</span>');
	$page->setConfig('prev','<<');
	$page->setConfig('next','>>');
	$page->setConfig('first','1...');
	$page->setConfig('last','...%TOTAL_PAGE%');
	$page->setConfig('theme','%UP_PAGE% %FIRST% %LINK_PAGE% %END% %DOWN_PAGE% %HEADER%');
	$page->lastSuffix = false;
	return $page;
}


/**
 * [getAuthRuleList 角色授权组装字符串函数]
 * @param  array   $array   [需要处理的数组]
 * @param  integer $pid     [父级pid]
 * @param  array   $ruleids [选中项id数组]
 * @return [string]         [返回组装字符串]
 */
function getAuthRuleList($array = array(),$pid=0,$ruleids = array()){

	$str = '';
	foreach ($array as $key => $value) {
		if($value['pid'] == $pid){
			$str .= "<ul style='border: 1px solid #e5e5e5;padding: 5px;margin-bottom: 10px;border-radius:5px'>";
			$str .= "<h2 style='padding: 10px 0 10px 0px;border-radius: 5px 5px 0 0;font-weight: bold;color: #000;border-bottom: 1px dashed #e5e5e5;'><label class='label'><input type='checkbox' name='mid[]' value='".$value['id']."'><span>".$value['name']."</span></label></h2>";

			if(!empty($value['authrule'])){
				$str .= "<li style='padding: 10px 0px 10px 20px;'>";
				foreach ($value['authrule'] as $key_1 => $value_1) {
					if(in_array($value_1['id'],$ruleids)){
						$check = "checked='checked'";
					} else {
						$check = '';
					}
					$str .= "<label class='label'><input type='checkbox' name='rules[]' ".$check." value='".$value_1['id']."'><span title='".$value_1['name']."'>".$value_1['title']."</span></label>";
				}
				$str .= "</li>";
			}

			$str .= "<li style='padding: 10px 0px 10px 20px;'>";
			$str .= getAuthRuleList($array ,$value['id'],$ruleids);
			$str .= "</li>";
			$str .= "</ul>";
		}
	}
   return $str;
}

/**
 * [getCateAuthList 栏目授权组装字符串函数]
 * @param  array  $array [需要处理的数组]
 * @param  string $child [子类键名]
 * @return [string]        [返回字符串]
 */
function getCateAuthList($array = array(),$child='child',$catids = array())
{
    $str = "";
    if(!empty($array)){
        foreach ($array as $key => $value) {

        	//选中
        	if(in_array($value['id'],$catids)){
				$check = "checked:true";
			} else {
				$check = 'checked:false';
			}

			//读取数据
        	$str .= "<li data-options = '".$check."'><span><a href='javascript:;' id='catids_". $value['id'] ."'>". $value['name'] . "</a></span>";
            if(!empty($value[$child])){
                $str .= "<ul>";
                $str .= getCateAuthList($value[$child],$child,$catids);
                $str .= "</ul>";
            }
            $str .= "</li>";
        }
    }
    return $str;
}


/**
 * [getCateList 整理分类菜单成树]
 * @param  array  $array [需要处理的数组]
 * @param  string $child [子类键名]
 * @return [string]        [返回字符串]
 */
function getCateList($array = array(),$child='child')
{
    $str = "";
    if(!empty($array)){
        foreach ($array as $key => $value) {

        	//判断单页
    		$value['danye'] ? $danye = '(<span class="green">单页</span>)' : $danye = '';
    		//数据量
    		$value['danye'] ? $countnum = '' : $countnum = '(<strong class="red">'.$value['countnum'].'</strong>)';
        	if(count($value[$child]) > 0){
        		$str .= "<li><span>". $value['name'] . $danye .   "</span>";
        	} else {
        		$str .= "<li><span><a href='".U("contentlist",array('id'=>$value['id'],'modelid'=>$value['modelid']))."' target='contentlist' id='selectedid_".$value['id']."'>" . $value['name'] . $danye . $countnum . "</a></span>";
        	}
            if(!empty($value[$child])){
                $str .= "<ul>";
                $str .= getCateList($value[$child],$child);
                $str .= "</ul>";
            }
            $str .= "</li>";
        }
    }
    return $str;
}


/**
 * [getMoveCate 整理内容移动分类菜单成树]
 * @param  array  $array [需要处理的数组]
 * @param  string $child [子类键名]
 * @return [string]        [返回字符串]
 */
function getMoveCate($array = array(),$child='child',$arr = array())
{
    $str = "";
    if(!empty($array)){
        foreach ($array as $key => $value) {
        	if($arr['modelid'] == $value['catmodelid'] && count($value[$child]) == 0 && $arr['catid'] != $value['id']){
        		$move = '<strong style="color:green;position:absolute;right:20px">(可移动)</strong>';
        		$movefun = 'onclick="getCateMoveInfo('.$value['id'].',\''.$value['name'].'\')"';
        		$style = "style='display:inline-block;width:100%;color:green'";
        	} else {
        		$move = '';
        		$movefun = '';
        		$style = "style='font-weight: normal;display:inline-block;width:100%;color:#ccc;'";
        	}
        	$str .= "<li><span>". "<strong ".$movefun." ".$style.">" . $value['name'] . "</strong>" . $move .  "</span>";
            if(!empty($value[$child])){
                $str .= "<ul>";
                $str .= getMoveCate($value[$child],$child,$arr);
                $str .= "</ul>";
            }
            $str .= "</li>";
        }
    }
    return $str;
}
?>
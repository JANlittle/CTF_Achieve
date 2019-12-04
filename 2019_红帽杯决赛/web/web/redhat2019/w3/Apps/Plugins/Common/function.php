<?php
/**
 * [getPage 分页方法]
 * @param  [int] $count   [总记录条数]
 * @param  [int] $pagenum [显示条数]
 * @return [object]       [分页对象]
 */
function getPage($count,$pagenum,$parameter = array(), $url = '',$routepage_depr = '',$is_bootstrap = 0,$prev = '上一页',$next = '下一页',$header = '<span class="rows">共 %TOTAL_ROW% 条记录</span>'){

	//判断分页数是否存在，如果不存在且不是数字的话就去配置的值，如果配置没有值就默认15
	if(!isset($pagenum) || !is_numeric($pagenum)){
		$page_c = C('BASE_PAGENUM');
		if(isset($page_c)){
			$pagenum = C('BASE_PAGENUM');
		} else {
			$pagenum = 15;
		}
	}

	//开始分页设置
	$page = new \Think\Page($count,$pagenum,$parameter,$url,$routepage_depr,$is_bootstrap);
	$page->setConfig('header',$header);
	$page->setConfig('prev',$prev);
	$page->setConfig('next',$next);
	$page->setConfig('first','1...');
	$page->setConfig('last','...%TOTAL_PAGE%');
	$page->setConfig('theme','%UP_PAGE% %FIRST% %LINK_PAGE% %END% %DOWN_PAGE% %HEADER%');
	$page->lastSuffix = false;
	return $page;
}

/**
 * [seo 获取seo数据]
 * @param  [type] $cate  [分类]
 * @param  [type] $danye [单页]
 * @return [type]        [返回seo数组]
 */
function seo($cate = array(),$danye = array())
{
    /*单页有则取单页的值否则取栏目的值*/
    $seoData = array();
    $seoData['title'] = !empty($danye['title']) ?  $danye['title'] :  $cate['title'];
    $seoData['keywords'] = !empty($danye['keywords']) ?  $danye['keywords'] :  $cate['keywords'];
    $seoData['description'] = !empty($danye['description']) ?  $danye['description'] :  $cate['description'];

    /*如果都没有的话取栏目名称*/
    $seoData['title'] = !empty($seoData['title']) ? $seoData['title'] : $cate['name'];
    $seoData['keywords'] = !empty($seoData['keywords']) ? $seoData['keywords'] : $cate['name'];
    $seoData['description'] = !empty($seoData['description']) ?  $seoData['description'] : $cate['name'];
    return $seoData;
}
?>
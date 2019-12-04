-- 主表
CREATE TABLE IF NOT EXISTS `$table_name` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `catid` int(11) unsigned NOT NULL COMMENT '栏目id',
  `title` varchar(150) NOT NULL COMMENT '标题',
  `titlecolor` varchar(20) NOT NULL COMMENT '标题颜色',
  `savename` varchar(255) NOT NULL COMMENT '缩略图',
  `photo` varchar(255) NOT NULL COMMENT '原图路径',
  `thumb` varchar(255) NOT NULL COMMENT '缩略图路径',
  `keywords` varchar(100) NOT NULL COMMENT '关键字',
  `description` varchar(255) NOT NULL COMMENT '描述',
  `content` mediumtext NOT NULL COMMENT '内容',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  `url` text NOT NULL COMMENT '跳转地址',
  `sort` int(11) unsigned NOT NULL COMMENT '排序',
  `count` int(11) unsigned NOT NULL COMMENT '点击量',
  `username` varchar(50) NOT NULL COMMENT '发布人',
  `input_time` varchar(20) NOT NULL COMMENT '发布时间',
  `create_time` varchar(20) NOT NULL COMMENT '添加时间',
  `update_time` varchar(20) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT = '$comment';

-- 在sitemodel_field表中插入数据
INSERT INTO `$tableprefix_sitemodel_field` (`modelid`, `type`, `field`, `name`, `tips`, `field_rule`, `extra`, `default`, `remark`, `is_must`, `validate`, `auto`, `issystem`, `isshow`, `isextend`, `sort`, `status`) VALUES
($modelid, 'num', 'id', '数据id', '', 'int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT', '', '', 'id', 0, '', '', 1, 0, 0, 1, 0),
($modelid, 'num', 'catid', '栏目id', '', 'int(11) unsigned NOT NULL', '', '', '栏目id', 0, '', '', 1, 0, 0, 2, 0),
($modelid, 'string', 'title', '标题', '请输入标题', 'varchar(150) NOT NULL', '', '', '标题', 1, 'array(array(''field'',''require'',''标题必须填写！''),array(''field'','''',''标题必须已经存在！'',0,''unique'',3))', '', 1, 1, 0, 1, 1),
($modelid, 'color', 'titlecolor', '标题颜色', '例如：red 或者 #000', 'varchar(20) NOT NULL', '', '', '标题颜色', 0, '', '', 1, 1, 0, 2, 1),
($modelid, 'thumb', 'savename', '缩略图', '图片规格:以宽度thumbW*thumbH以上这个值或者是它的倍数为佳！', 'varchar(255) NOT NULL', 'array(200,150)', '', '缩略图', 1, '', '', 1, 1, 0, 3, 1),
($modelid, 'string', 'photo', '原图路径', '', 'varchar(255) NOT NULL', '', '', '原图路径', 0, '', '', 1, 0, 0, 6, 0),
($modelid, 'string', 'thumb', '缩略图路径', '', 'varchar(255) NOT NULL', '', '', '缩略图路径', 0, '', '', 1, 0, 0, 7, 0),
($modelid, 'string', 'keywords', '关键字', 'seo关键字', 'varchar(100) NOT NULL', '', '', '关键字', 0, '', '', 1, 1, 0, 4, 1),
($modelid, 'textarea', 'description', '描述', 'seo描述', 'varchar(255) NOT NULL', '', '', '描述', 0, '', '', 1, 1, 0, 5, 1),
($modelid, 'editor', 'content', '内容', '', 'mediumtext NOT NULL', '', '', '内容', 0, '', '', 1, 1, 0, 6, 1),
($modelid, 'bool', 'status', '状态', '', 'tinyint(1) NOT NULL', 'array(1,0)', '1', '状态', 0, '', '', 1, 1, 0, 7, 1),
($modelid, 'string', 'url', '跳转地址', '格式：http://www.yeone.cn/，存在则直接跳转', 'text NOT NULL', '', '', '跳转地址', 0, 'array(array(''field'',''url'',''url地址不正确！'',2))', '', 1, 1, 1, 8, 1),
($modelid, 'num', 'sort', '排序', '', 'int(11) UNSIGNED NOT NULL', '', '', '排序', 0, '', '', 1, 0, 0, 13, 0),
($modelid, 'datetime', 'input_time', '发布时间', '', 'varchar(20) NOT NULL', '', '', '发布时间', 0, '', '', 1, 1, 1, 9, 1),
($modelid, 'datetime', 'create_time', '创建时间', '', 'varchar(20) NOT NULL', '', '', '创建时间', 0, '', '', 1, 0, 0, 15, 1),
($modelid, 'datetime', 'update_time', '更新时间', '', 'varchar(20) NOT NULL', '', '', '更新时间', 0, '', '', 1, 0, 0, 16, 0),
($modelid, 'num', 'count', '点击量', '', 'int(11) UNSIGNED NOT NULL', '', '0', '点击量', 0, '', '', 0, 0, 0, 17, 1),
($modelid, 'string', 'username', '发布人', '', 'varchar(50) NOT NULL', '', '', '发布人', 0, '', '', 0, 0, 0, 18, 1);
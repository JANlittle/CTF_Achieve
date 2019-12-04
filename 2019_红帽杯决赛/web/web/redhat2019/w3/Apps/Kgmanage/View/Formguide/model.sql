-- 主表
CREATE TABLE IF NOT EXISTS `$table_name` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `uniqid` varchar(60) DEFAULT NULL COMMENT '唯一标识符：用于数据的删除与编辑',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户',
  `guide_ip` varchar(20) DEFAULT NULL COMMENT 'ip',
  `ip_area` varchar(255) DEFAULT NULL COMMENT 'ip地区信息',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  `sort` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` varchar(20) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT = '$comment';

-- 在formguide_field表中插入数据
INSERT INTO `$tableprefix_formguide_field` (`modelid`, `type`, `field`, `name`, `tips`, `field_rule`, `extra`, `default`, `remark`, `additional`, `css`, `min`, `max`, `regex`, `is_must`, `validate`, `auto`, `issystem`, `isshow`, `listshow`, `sort`, `status`, `isdetails`) VALUES
($modelid, 'num', 'id', '数据id', NULL, 'int(10) unsigned NOT NULL AUTO_INCREMENT', NULL, NULL, 'id', NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 1, 0, 0, 0, 0, 0),
($modelid, 'string', 'uniqid', '标识符', NULL, 'varchar(60) NULL', NULL, NULL, '唯一标识符：用于数据的删除与编辑', NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 1, 0, 0, 1, 0, 0),
($modelid, 'uid', 'uid', '用户', NULL, 'int(10) unsigned NOT NULL', NULL, 0, '用户', NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 1, 0, 1, 9993, 0, 1),
($modelid, 'string', 'guide_ip', 'ip', NULL, 'varchar(20) NULL', NULL, NULL, 'ip', NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 1, 0, 1, 9994, 0, 1),
($modelid, 'iparea', 'ip_area', 'ip地区信息', NULL, 'varchar(255) NULL', NULL, NULL, 'ip地区信息', NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 1, 0, 0, 9995, 0, 0),
($modelid, 'bool', 'status', '状态', NULL, 'tinyint(1) NOT NULL', '开启:1|关闭:0', '0', '状态', NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 1, 0, 1, 9996, 0, 1),
($modelid, 'num', 'sort', '排序', '请输入排序', 'int(11) UNSIGNED NOT NULL', NULL, NULL, '排序', NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 1, 0, 0, 9997, 0, 0),
($modelid, 'datetime', 'create_time', '创建时间', '请输入创建时间', 'varchar(20) NULL', NULL, NULL, '创建时间', NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 1, 0, 1, 9998, 0, 1);
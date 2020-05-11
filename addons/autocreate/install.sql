SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `lt_autocreate_curd`;
CREATE TABLE `lt_autocreate_curd` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `table_name` varchar(255) DEFAULT NULL COMMENT '表名',
  `table_comment` varchar(255) DEFAULT NULL COMMENT '表注释',
  `field_list` text COMMENT '字段列表，详细设置',
  `global` text COMMENT '全局设置',
  `relation_model` text COMMENT '关联模型相关数据',
  `create_time` datetime DEFAULT NULL COMMENT '数据创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '数据更新时间',
  `exec_create_time` datetime DEFAULT NULL COMMENT '首次执行时间',
  `exec_update_time` datetime DEFAULT NULL COMMENT '最近一次执行时间',
  `exec_count` int(11) DEFAULT '1' COMMENT '总共生成的次数',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='curd自动生成';

DROP TABLE IF EXISTS `lt_autocreate_menu`;
CREATE TABLE `lt_autocreate_menu` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `controller` text COMMENT '控制器完整路径名称',
  `first_menu_id` int(11) DEFAULT '0' COMMENT '所属一级菜单ID',
  `second_menu_id` int(11) DEFAULT '0' COMMENT '所属二级菜单ID',
  `create_time` datetime DEFAULT NULL COMMENT '生成时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='自动生成菜单';
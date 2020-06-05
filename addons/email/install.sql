SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `lt_email`
-- ----------------------------
DROP TABLE IF EXISTS `lt_email`;
CREATE TABLE `lt_email` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `template_id` int(11) DEFAULT NULL COMMENT '模板ID',
  `event` varchar(255) DEFAULT NULL COMMENT '事件名称',
  `params` text COMMENT '邮件内容参数',
  `content` text COMMENT '邮件内容',
  `from` varchar(255) DEFAULT NULL COMMENT '发件人邮箱',
  `to` varchar(255) DEFAULT NULL COMMENT '收件人邮箱',
  `status` int(11) DEFAULT '1' COMMENT '状态。1=未使用,2=已使用,3=已过期',
  `expire_time` int(11) DEFAULT '0' COMMENT '过期时间，0表示永不过期',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='邮件管理';

-- ----------------------------
--  Table structure for `lt_email_template`
-- ----------------------------
DROP TABLE IF EXISTS `lt_email_template`;
CREATE TABLE `lt_email_template` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `event` varchar(255) DEFAULT NULL COMMENT '事件名称',
  `title` varchar(255) DEFAULT NULL COMMENT '模板标题',
  `template` text COMMENT '模板内容，支持html标签',
  `ishtml` tinyint(4) DEFAULT '2' COMMENT '模板是否为html，1=是，2=不是。默认2',
  `expire` int(11) DEFAULT '0' COMMENT '过期时长，单位秒，0表示永不过期',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='邮件模板管理';

SET FOREIGN_KEY_CHECKS = 1;

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `{$prefix}aliyun_mobilemsg`
-- ----------------------------
DROP TABLE IF EXISTS `{$prefix}aliyun_mobilemsg`;
CREATE TABLE `{$prefix}aliyun_mobilemsg` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `template_code` varchar(255) DEFAULT NULL COMMENT '阿里云短信模板ID',
  `event` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '事件名称',
  `params` text CHARACTER SET utf8 COMMENT '邮件内容参数',
  `mobile` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '收信人手机号码',
  `status` int(11) DEFAULT '1' COMMENT '状态。1=未使用,2=已使用,3=已过期',
  `expire_time` int(11) DEFAULT '0' COMMENT '过期时间，0表示永不过期',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='手机短信管理';

SET FOREIGN_KEY_CHECKS = 1;

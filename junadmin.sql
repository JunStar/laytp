/*
 Navicat Premium Data Transfer

 Source Server         : 192.168.3.15
 Source Server Type    : MySQL
 Source Server Version : 50722
 Source Host           : 192.168.3.15:3306
 Source Schema         : junadmin

 Target Server Type    : MySQL
 Target Server Version : 50722
 File Encoding         : 65001

 Date: 19/10/2018 18:07:24
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for ja_autocreate_curd
-- ----------------------------
DROP TABLE IF EXISTS `ja_autocreate_curd`;
CREATE TABLE `ja_autocreate_curd`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `table_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '表名',
  `table_comment` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '表注释',
  `field_list` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '字段设置',
  `global` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '全局设置',
  `create_time` int(11) NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) NULL DEFAULT NULL COMMENT '更新时间',
  `exec_count` int(11) NULL DEFAULT NULL COMMENT '生成次数',
  `exec_update_time` int(11) NULL DEFAULT NULL COMMENT '最近一次生成时间',
  `exec_create_time` int(11) NULL DEFAULT NULL COMMENT '首次执行时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = 'curd自动生成' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ja_autocreate_curd
-- ----------------------------
INSERT INTO `ja_autocreate_curd` VALUES (1, 'ja_test', '测试模型', '[{\"field_name\":\"content\",\"field_comment\":\"\\u6587\\u672c\\u57df\",\"form_type\":\"input\",\"form_additional\":\"\",\"form_empty\":\"0\",\"table_width\":\"\\u81ea\\u9002\\u5e94\",\"table_min_width\":\"\\u4f7f\\u7528\\u5168\\u5c40\\u914d\\u7f6e\",\"table_templet\":\"\",\"table_align\":\"center\",\"table_additional_unresize\":\"0\",\"table_additional_sort\":\"0\",\"table_additional_edit\":\"0\"},{\"field_name\":\"name\",\"field_comment\":\"\\u540d\\u79f0\",\"form_type\":\"input\",\"form_additional\":\"\",\"form_empty\":\"0\",\"table_width\":\"\\u81ea\\u9002\\u5e94\",\"table_min_width\":\"\\u4f7f\\u7528\\u5168\\u5c40\\u914d\\u7f6e\",\"table_templet\":\"\",\"table_align\":\"center\",\"table_additional_unresize\":\"0\",\"table_additional_sort\":\"0\",\"table_additional_edit\":\"0\"},{\"field_name\":\"radio\",\"field_comment\":\"\\u5e38\\u89c4\\u5355\\u9009\",\"form_type\":\"input\",\"form_additional\":\"\",\"form_empty\":\"0\",\"table_width\":\"\\u81ea\\u9002\\u5e94\",\"table_min_width\":\"\\u4f7f\\u7528\\u5168\\u5c40\\u914d\\u7f6e\",\"table_templet\":\"\",\"table_align\":\"center\",\"table_additional_unresize\":\"0\",\"table_additional_sort\":\"0\",\"table_additional_edit\":\"0\"},{\"field_name\":\"radio_switch\",\"field_comment\":\"\\u5f00\\u5173\\u5355\\u9009\",\"form_type\":\"input\",\"form_additional\":\"\",\"form_empty\":\"0\",\"table_width\":\"\\u81ea\\u9002\\u5e94\",\"table_min_width\":\"\\u4f7f\\u7528\\u5168\\u5c40\\u914d\\u7f6e\",\"table_templet\":\"\",\"table_align\":\"center\",\"table_additional_unresize\":\"0\",\"table_additional_sort\":\"0\",\"table_additional_edit\":\"0\"}]', '{\"common_model\":\"0\",\"hide_pk\":\"0\",\"hide_del\":\"0\",\"create_number\":\"0\",\"table_name\":\"ja_test\",\"fields_name\":\"content,name,radio,radio_switch\",\"close_page\":\"0\",\"search_mode\":\"down\",\"search_fields\":\"content,name,radio,radio_switch\",\"cell_min_width\":\"80\",\"show_fields\":\"content,name,radio,radio_switch\",\"all_fields\":[{\"field_name\":\"name\",\"field_comment\":\"\\u540d\\u79f0\"},{\"field_name\":\"content\",\"field_comment\":\"\\u6587\\u672c\\u57df\"},{\"field_name\":\"radio\",\"field_comment\":\"\\u5e38\\u89c4\\u5355\\u9009\"},{\"field_name\":\"radio_switch\",\"field_comment\":\"\\u5f00\\u5173\\u5355\\u9009\"}]}', 1539942683, 1539942683, NULL, 1539942683, 1539942683);

-- ----------------------------
-- Table structure for ja_menu
-- ----------------------------
DROP TABLE IF EXISTS `ja_menu`;
CREATE TABLE `ja_menu`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '标题',
  `rule` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '规则',
  `is_menu` tinyint(4) NULL DEFAULT NULL COMMENT '菜单',
  `sort` int(11) NULL DEFAULT NULL COMMENT '排序',
  `pid` int(11) NULL DEFAULT NULL COMMENT '父级',
  `icon` varchar(45) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '图标',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 22 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ja_menu
-- ----------------------------
INSERT INTO `ja_menu` VALUES (1, '首页', 'admin/index/index', 1, 0, 0, 'layui-icon layui-icon-home');
INSERT INTO `ja_menu` VALUES (17, '系统核心', 'admin/core.menu/index', 1, 0, 0, 'layui-icon layui-icon-template-1');
INSERT INTO `ja_menu` VALUES (18, '权限管理', 'admin/core.menu/index', 1, 0, 17, 'layui-icon layui-icon-align-left');
INSERT INTO `ja_menu` VALUES (19, '菜单管理', 'admin/core.menu/index', 1, 0, 18, 'layui-icon layui-icon-templeate-1');
INSERT INTO `ja_menu` VALUES (20, '一键生成', 'admin/autocreate.curd/index', 1, 0, 17, 'layui-icon layui-icon-share');
INSERT INTO `ja_menu` VALUES (21, 'Curd', 'admin/autocreate.curd/index', 1, 0, 20, 'layui-icon layui-icon-star-fill');

-- ----------------------------
-- Table structure for ja_test
-- ----------------------------
DROP TABLE IF EXISTS `ja_test`;
CREATE TABLE `ja_test`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '名称',
  `content` blob NULL COMMENT '文本域',
  `radio` tinyint(4) NULL DEFAULT NULL COMMENT '常规单选',
  `radio_switch` tinyint(4) NULL DEFAULT NULL COMMENT '开关单选',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '测试模型' ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;

/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 50723
 Source Host           : localhost:3306
 Source Schema         : junadmin

 Target Server Type    : MySQL
 Target Server Version : 50723
 File Encoding         : 65001

 Date: 06/09/2018 22:40:33
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for sys_curd
-- ----------------------------
DROP TABLE IF EXISTS `sys_curd`;
CREATE TABLE `sys_curd`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '表名',
  `comment` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '表注释',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sys_curd
-- ----------------------------
INSERT INTO `sys_curd` VALUES (1, 'ja_test', '测试模型');

-- ----------------------------
-- Table structure for sys_curd_model
-- ----------------------------
DROP TABLE IF EXISTS `sys_curd_model`;
CREATE TABLE `sys_curd_model`  (
  `id` int(11) NOT NULL COMMENT 'ID',
  `curd_id` int(11) NULL DEFAULT NULL COMMENT 'sys_curd表主键ID',
  `create_table_sql` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '创建表的sql',
  `field_set` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '字段配置',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sys_menu
-- ----------------------------
DROP TABLE IF EXISTS `sys_menu`;
CREATE TABLE `sys_menu`  (
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
-- Records of sys_menu
-- ----------------------------
INSERT INTO `sys_menu` VALUES (1, '首页', 'admin/index/index', 1, 0, 0, 'layui-icon layui-icon-home');
INSERT INTO `sys_menu` VALUES (17, '系统核心', 'admin/core.menu/index', 1, 0, 0, 'layui-icon layui-icon-template-1');
INSERT INTO `sys_menu` VALUES (18, '权限管理', 'admin/core.menu/index', 1, 0, 17, 'layui-icon layui-icon-align-left');
INSERT INTO `sys_menu` VALUES (19, '菜单管理', 'admin/core.menu/index', 1, 0, 18, 'layui-icon layui-icon-templeate-1');
INSERT INTO `sys_menu` VALUES (20, '一键生成', 'admin/autocreate.curd/index', 1, 0, 17, 'layui-icon layui-icon-share');
INSERT INTO `sys_menu` VALUES (21, 'Curd', 'admin/autocreate.curd/index', 1, 0, 20, 'layui-icon layui-icon-star-fill');

SET FOREIGN_KEY_CHECKS = 1;

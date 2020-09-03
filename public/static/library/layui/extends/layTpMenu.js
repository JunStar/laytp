/**
 * 菜单操作，功能包括
 *  - 得到菜单的树形json字符串，在index.html页面上渲染菜单
 *  - 点击菜单渲染右侧页面
 *  layTpElement组件在点击菜单时的功能
 * @version: 2.0
 * @Author:  JunStar
 * @Date:    2020-7-22 20:16:03
 * @Last Modified by:   JunStar
 * @Last Modified time: 2020-7-22 20:16:10
 */
layui.define([
    "jquery", "layer", "facade"
], function (exports) {
    const MOD_NAME = "layTpMenu";
    let $ = layui.$;
    let facade = layui.facade;

    let layTpMenu = {
        //格式化rule，将全局变量appName、controller和action进行赋值
        formatRule: function (rule) {
            rule = facade.trim(rule, "/");
            let ruleArr = rule.split("/");
            appName = ruleArr[0];
            controller = ruleArr[1];
            action = ruleArr[2];
            return true;
        },

        //ajax请求，渲染右侧界面，修改url，带上layTpMenuId
        ajaxRenderPage: function (rule, layTpMenuId) {
            if (rule === "") {
                facade.error("菜单没有设置路由规则");
                return false;
            }
            layTpMenu.formatRule(rule);
            // var url = layTpMenu.getHtmlUrl(rule) + "?v="+Math.random();
            var url = facade.getHtmlUrl(rule);
            facade.editHistory("/a/index.html?ref=" + layTpMenuId);
            $.ajax({
                url: url,
                success: function (res) {
                    $("#box").html(res);
                    layui.form.render();
                },
                error: function (xhr) {
                    switch (xhr.status) {
                        case 404:
                            facade.error('文件' + url + '不存在');
                            break;
                        case 500:
                        case 502:
                            facade.error(xhr["responseText"], "异常提示");
                            break;
                    }
                }
            });
        },

        /**
         * 内部使用方法，获取到能展示的所有菜单列表
         */
        getMenuJson: function (treeData) {
            let key;
            for (key in treeData) {
                if (treeData[key]) {

                }
            }
        },

        /**
         * 渲染菜单，如果需要实时渲染菜单，可以使用此方法
         */
        render: function () {
            //ajax请求接口，获取到能显示的菜单数据
            facade.ajax({path: "/admin/auth.menu/getData", async: false, successAlert: false}).done(function (res) {
                if (res["code"] === 0) {
                    menuJson = res.data.treeData;
                    authJson = res.data.listData;
                    selectMenuIds = layTpMenu.getSelectMenuIds(authJson);
                }
            });

            //渲染菜单
            layTpMenu.renderMenu();

            //渲染右侧页面
            let ref = parseInt(facade.getUrlParam('ref'));
            for (k in authJson) {
                if (authJson[k].id === ref) {
                    layTpMenu.ajaxRenderPage(authJson[k].rule, ref);
                    break;
                }
            }
        },

        /**
         * 内部使用方法，根据Url中的ref参数，获取需要选中的菜单id数组
         * @returns {*|Array}
         */
        getSelectMenuIds: function (authJson) {
            let ref = parseInt(facade.getUrlParam('ref'));
            if (ref > 0) {
                return layTpMenu.getSelectMenuPid(authJson, ref);
            } else {

            }
        },

        /**
         * 内部使用方法，当ref存在时，根据菜单原始数据和url的ref参数，设置应该选中的菜单id数组
         * @param authJson
         * @param ref
         * @returns {Array}
         */
        getSelectMenuPid: function (authJson, ref) {
            let key;
            for (key in authJson) {
                if (authJson[key].id === ref) {
                    selectMenuIds.push(ref);
                    if (authJson[key].pid > 0) {
                        layTpMenu.getSelectMenuPid(authJson, authJson[key].pid);
                    }
                    break;
                }
            }
            return selectMenuIds;
        },

        /**
         * 渲染菜单，内部使用方法，如果修改了菜单，需要实时渲染菜单，使用render方法
         */
        renderMenu: function () {
            let topMenuHtml = "";
            let menuClass = "";
            let defaultLeftMenuJson;
            let key;
            for (key in menuJson) {
                if (menuJson[key]["pid"] === 0) {
                    if (facade.inArray(menuJson[key]["id"], selectMenuIds)) {
                        defaultLeftMenuJson = menuJson[key]["children"];
                    }
                    menuClass = (facade.inArray(menuJson[key]["id"], selectMenuIds)) ? "layui-nav-item layui-this" : "layui-nav-item";
                    topMenuHtml += "<li class=\"" + menuClass + "\">" +
                        "                <a href=\"javascript:;\" rule=\"" + menuJson[key]["rule"] + "\" menu_id=\"" + menuJson[key]["id"] + "\">" +
                        "                    <i class=\"" + menuJson[key]["icon"] + "\"></i> " + menuJson[key]["name"] + "" +
                        "                </a>" +
                        "            </li>";
                }
            }
            $('#layTpTopMenu').html(topMenuHtml);
            layTpMenu.renderLeft(defaultLeftMenuJson);
        },

        /**
         * 渲染展示左侧菜单，由于第二级菜单的html循环体与其他级别的菜单循环体的html内容不相同，所以在渲染菜单时，外层循环渲染二级菜单，三级以及三级以下的菜单使用递归获取到Html
         */
        renderLeft: function (subMenu) {
            let subStr = "<ul class=\"layui-nav layui-nav-tree\">";
            let key;
            let menuClass = "";
            for (key in subMenu) {
                menuClass = (facade.inArray(subMenu[key]["id"], selectMenuIds)) ? "layui-nav-item layui-nav-itemed" : "layui-nav-item";
                subStr += "<li class=\"" + menuClass + "\">";
                subStr += "<a href=\"javascript:;\" rule=\"" + subMenu[key].rule + "\" menu_id=\"" + subMenu[key].id + "\">\n" +
                    "       <i class=\"" + subMenu[key].icon + "\"></i> " + subMenu[key].name +
                    "       </a>";
                if (subMenu[key].children != null && subMenu[key].children.length > 0) {
                    subStr += layTpMenu.getChildHtml(subMenu[key].children, 0);
                }
                subStr += "</li>";
            }
            subStr += "<span class=\"layui-nav-bar\" style=\"top: 22.5px; height: 0px; opacity: 0;\"></span></ul>";
            $('#layTpLeftMenu').html(subStr);
            layui.layTpElement.init();
        },

        /**
         * 得到三级以及三级以下的菜单Html
         * @param subMenu   子级菜单数据
         * @param num       子级菜单的级别
         */
        getChildHtml: function (subMenu, num) {
            num++;
            let childMenuMarginLeft = 20;//左侧菜单，每个级别缩进的像素
            let subStr = "";
            let menuClass = "";
            let key;
            subStr += "<dl class=\"layui-nav-child\">\n";
            for (key in subMenu) {
                if (subMenu[key].children != null && subMenu[key].children.length > 0) {
                    menuClass = (facade.inArray(subMenu[key]["id"], selectMenuIds)) ? "layui-nav-item layui-nav-itemed" : "layui-nav-item";
                    subStr += "<dd class=\"" + menuClass + "\">\n" +
                        "       <a style=\"margin-Left:" + (num * childMenuMarginLeft) + "px\" href=\"javascript:;\" rule=\"" + subMenu[key].rule + "\" menu_id=\"" + subMenu[key].id + "\">\n" +
                        "           <i class=\"" + subMenu[key].icon + "\"></i> " + subMenu[key].name + "\n" +
                        "       </a>";
                    subStr += layTpMenu.getChildHtml(subMenu[key].children, num);
                    subStr += "</dd>";
                } else {
                    menuClass = (facade.inArray(subMenu[key]["id"], selectMenuIds)) ? "layui-nav-itemed" : "";
                    subStr += "<dd class=\"" + menuClass + "\">\n" +
                        "       <a style=\"margin-Left:" + (num * childMenuMarginLeft) + "px\" href=\"javascript:;\" rule=\"" + subMenu[key].rule + "\" menu_id=\"" + subMenu[key].id + "\">\n" +
                        "       <i class=\"" + subMenu[key].icon + "\"></i> " + subMenu[key].name + "\n" +
                        "       </a>\n" +
                        "   </dd>"
                }
            }
            subStr += "</dl>";
            return subStr;
        }
    };

    //输出模块
    exports(MOD_NAME, layTpMenu);

    layui.layTpMenu = layTpMenu;
    window.layTpMenu = layTpMenu;
});
/**
 * @version: 2.0
 * @Author:  JunStar
 * @Date:    2018年09月20日11:47:00
 * @Last Modified by:   JunStar
 * @Last Modified time: 2019年06月09日23:23:09
 */
layui.define([
    "jquery", "layer", "form", "table", "laytpl", "laydate", "upload", "colorpicker"
    , "layTpElement"
    , "layTpTree"
    , "facade"
    , "layTpForm"
    , "dropdown"
    , "treeTable"
    , "notice"
    , "xmSelect"
    , "layTpUpload"
], function (exports) {
    const MOD_NAME = "layTp";

    let $ = layui.jquery
        , facade = layui.facade
        , layTp = {}
    ;

    //数据表格，格式化展示数据
    layTp.tableFormatter = {
        custom: ["#FF5722", "#009688", "#FFB800", "#2F4056", "#1E9FFF", "#393D49", "#999999", "#0b1bf8", "#7a0bf8", "#f00bf8", "#5FB878", "#1E9FFF", "#2F4056"],
        images: function (value) {
            let html = "";
            if (value) {
                let value_arr = value.split(",");
                for (key in value_arr) {
                    html += "<a target='_blank' href='" + value_arr[key] + "'><img src='" + value_arr[key] + "' style='width:30px;height:30px;' /></a> ";
                }
            }
            return html;
        },
    };

    /**
     * 监听数据表格顶部左侧默认按钮点击事件
     */
    layTp.tableToolbar = function (obj) {
        if (obj.event === "add") {
            let options = {};
            options.title = "添 加";
            options.url = facade.url("/admin/" + controller + "/add");
            options.data = obj.data;
            facade.popupDiv(options);
        } else if (obj.event === "show-hidden-search-form") {
            if ($("#search-form").css("display") === "none") {
                $("#search-form").show();
            } else {
                $("#search-form").hide();
            }
        } else if (obj.event === "show-hidden-recycle-search-form") {
            if ($("#recycle-search-form").css("display") === "none") {
                $("#recycle-search-form").show();
            } else {
                $("#recycle-search-form").hide();
            }
        } else if (obj.event === "recycle") {
            let options = {};
            options.title = "回收站";
            options.url = facade.url("/admin/" + controller + "/recycle");
            options.data = obj.data;
            options.btn = "";
            options.height = "80%";
            facade.popupDiv(options);
        }
    };

    /**
     * 监听数据表格操作列，默认按钮点击事件
     */
    layTp.tableTool = function (obj) {
        if (obj.event === "del") {
            facade.popupConfirm({text:"真的删除么?", url:"/admin/" + controller + "/del", params:{ids: obj.data.id}});
        } else if (obj.event === "edit") {
            let options = {};
            options.title = "编 辑";
            options.url = facade.url("/admin/" + controller + "/edit");
            options.data = obj.data;
            facade.popupDiv(options);
        } else if (obj.event === "restore") {
            facade.ajax({path: "admin/" + controller + "/restore", params: {ids: obj.data.id}}).done(function (res) {
                if (res.code === 0) {
                    funcController.tableRender();
                    funcRecycleController.tableRender();
                }
            });
        } else if (obj.event === "trueDel") {
            facade.popupConfirm({text:"真的删除么?", url:"/admin/" + controller + "/trueDel", params:{ids: obj.data.id}});
        }
    };

    //初始化
    layTp.init = {
        /**
         * 全局ajax设置
         * - 设置默认的headers
         * - ajax过度效果设置
         */
        ajaxSet: function () {
            var layTpAdminToken = facade.getCookie("laytp_admin_token");
            var loading;
            $.ajaxSetup({
                headers: {
                    "LayTp-Admin-Token": layTpAdminToken
                },
                beforeSend: function () {
                    loading = facade.loading();
                },
                complete: function (xhr) {
                    layui.layer.close(loading);
                }
            });
        },

        /**
         * 点击按钮弹出表单层 - 使用例子：数据表格顶部的添加按钮
         * 所有拥有popup-div类名的节点，点击后都会弹出层
         *  data-name弹窗标题，默认为当前节点的html()
         *  data-open弹窗展示的url地址，非必填，data-open或者data-action二选一必填,优先使用data-open
         *  data-action弹窗展示的action地址，非必填，data-open或者data-type二选一必填,优先使用data-open，例：add
         *  data-btn弹出层底部按钮，只有两种设置方式，1.不设置，表示弹出一个表单，底部展示['确定','取消']两个按钮，点击确定按钮可以提交表单。2.设置为空字符串，表示弹出一个非表单层，仅展示页面，底部没有按钮，其他复杂的弹出层，比如有三个或者三个以上按钮，请自行编写JS实现
         *  data-width属性为弹窗的宽度百分比，非必填，默认值在/a/index.html中设置
         *  data-height属性为弹窗的高度百分比，非必填，默认值在/a/index.html中设置
         *  data-shade属性为弹窗背景阴影，非必填，默认值在/a/index.html中设置
         *  data-params属性为节点id属性值，非必填，多个以逗号隔开，有这个值的话，会获取这些节点的值，拼接到url中，用途是当弹出层需要先使用表单填写一些数据然后再将填写的数据传入弹出层时使用
         */
        popupDiv: function () {
            $(document).off("click", ".popup-div").on("click", ".popup-div", function () {
                var options = {};
                options.title = $(this).data("title") ? $(this).data("title") : $(this).html();
                let url = $(this).data("open") ? $(this).data("open") : "/a/" + controller + "/" + $(this).data("action") + ".html";
                options.width = $(this).data("width");
                if (options.width) options.width = defaultWidthPopupDiv;
                options.height = $(this).data("height");
                if (!options.height) options.height = defaultHeightPopupDiv;
                options.shade = $(this).data("shade");
                if (!options.shade) options.shade = defaultShadePopupDiv;
                options.btn = $(this).data("btn");
                if (options.btn === "") {
                    options.btn = false;
                }
                let paramsIds = $(this).data("params");
                let params = {};
                if (paramsIds) {
                    let labelTitle = "";
                    let arrParamsIds = paramsIds.split(",");
                    for (key in arrParamsIds) {
                        if (!$("#" + arrParamsIds[key]).val()) {
                            facade.error("请输入" + labelTitle);
                            return false;
                        } else {
                            params[arrParamsIds[key]] = $("#" + arrParamsIds[key]).val();
                        }
                    }
                }
                options.url = facade.url(url, params);
                facade.popupDiv(options);
            });
        },

        /**
         * 监听下拉菜单点击事件
         */
        dropDownAction: function () {
            $(document).off("click", ".dropdown-action").on("click", ".dropdown-action", function () {
                //执行callback
                let callback = $(this).attr("callback");
                if (callback !== undefined && callback !== "undefined" && callback !== "") {
                    let param = $(this).attr("param");
                    eval(callback + "(" + param + ")");
                    return true;
                }
                let uri = $(this).attr("uri");
                if (uri === undefined) {
                    return false;
                }
                let field = $(this).attr("field");
                let field_val = $(this).attr("field_val");
                let need_data = $(this).attr("need_data");
                let width = $(this).attr("width") ? $(this).attr("width") : defaultWidthPopupDiv;
                let height = $(this).attr("height") ? $(this).attr("height") : defaultHeightPopupDiv;
                let need_refresh = $(this).attr("need_refresh");
                let table_id = $(this).attr("table_id");
                let text = $(this).text();
                let switch_type = $(this).attr("switch_type");
                let checkData;
                if (need_data === "true") {
                    if (table_id.indexOf("tree-table") !== -1) {
                        checkData = layTpTreeTable.checkStatus(false);
                    } else {
                        var checkStatus = layui.table.checkStatus(table_id);
                        checkData = checkStatus.data;
                    }
                    if (checkData.length === 0) {
                        facade.error("请选择数据");
                        return false;
                    }
                }
                let key;
                switch (switch_type) {
                    case "popupDiv":
                        if (need_data === "true") {
                            for (key in checkData) {
                                facade.popupDiv({
                                    title: text,
                                    url: uri,
                                    data: checkData[key],
                                    width: width,
                                    height: height
                                });
                            }
                        } else {
                            facade.popupDiv({
                                title: text,
                                url: uri,
                                width: width,
                                height: height
                            });
                        }
                        break;
                    case "popupConfirm":
                        let ids_arr = [];
                        let data = {};
                        if (need_data === "true") {
                            for (key in checkData) {
                                ids_arr.push(checkData[key].id);
                            }
                            let ids = ids_arr.join(",");
                            data = {"ids": ids, "field": field, "field_val": field_val};
                        }
                        facade.popupConfirm({text:"确定" + text + "么?", url:facade.url(uri), params:data});
                        break;
                    default:
                        if (need_data === "true") {
                            var ids = [];
                            for (key in checkData) {
                                ids.push(checkData[key].id);
                            }
                            facade.ajax({path: uri, params: {ids: ids.join(",")}}).done(function (res) {
                                if (res.code === 0) {
                                    funcController.tableRender();
                                    if (typeof funcRecycleController !== "undefined") {
                                        funcRecycleController.tableRender();
                                    }
                                }
                            });
                        } else {
                            facade.ajax({path: uri});
                        }
                        break;
                }
            });
        }
    };

    layui.each(layTp.init, function (key, item) {
        if (typeof item == "function") {
            item();
        }
    });

    //输出模块
    exports(MOD_NAME, layTp);

    layui.layTp = layTp;

    window.layTp = layTp;
    window.$ = $;
});
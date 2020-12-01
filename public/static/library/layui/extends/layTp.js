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
    , "layTpIcon"
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
        status: function (field, value, json) {
            let customIndex = 0, key;
            for (key in json['value']) {
                if (value == json['value'][key]) {
                    return '<a class="layTpClickSearch layerTips layui-icon layui-icon-circle-dot" data-field="' + field + '" data-val="' + value + '" data-text="点击搜索 ' + json['text'][key] + '" data-tipsColor="#393D49" style="color:' + layTp.tableFormatter.custom[customIndex] + ';font-size:14px;">' + json['text'][key] + '</a>';
                }
                customIndex++;
            }
            return '';
        },
        //form_type=checkbox
        flag: function (value, dataList) {
            let html = '';
            let customIndex = 0, key;
            let valueArr = value.split(',');
            for (key in dataList) {
                for (v in valueArr) {
                    if (valueArr[v] == key) {
                        customIndex = key % layTp.tableFormatter.custom.length;
                        html += '<span class="layui-btn layui-btn-sm-1" style="background-color: ' + layTp.tableFormatter.custom[customIndex] + '">' + dataList[key] + '</span>';
                    }
                }
            }
            return html;
        },
    };

    /**
     * 监听数据表格顶部左侧默认按钮点击事件
     */
    layTp.tableToolbar = function (obj, isTreeTable) {
        if (obj.event === "add") {
            let options = {};
            options.title = "添 加";
            options.path = apiPrefix + "add";
            options.data = obj.data;
            facade.popupDiv(options);
        } else if (obj.event === "edit") {
            let checkData;
            if (isTreeTable) {
                checkData = layTpTreeTable.checkStatus(false);
            } else {
                let checkStatus = layui.table.checkStatus(obj.config.id);
                checkData = checkStatus.data;
            }
            if (checkData.length === 0) {
                facade.error("请选择数据");
                return false;
            }
            if (checkData.length >= 30) {
                facade.error("选择数据量过多，最多选择30条数据，总共选择了" + checkData.length + "条数据");
                return false;
            }
            let key;
            for (key in checkData) {
                facade.popupDiv({
                    title: "编辑",
                    path: apiPrefix + "edit",
                    data: checkData[key],
                });
            }
        } else if (obj.event === "del") {
            let checkData;
            if (isTreeTable) {
                checkData = layTpTreeTable.checkStatus(false);
            } else {
                let checkStatus = layui.table.checkStatus(obj.config.id);
                checkData = checkStatus.data;
            }
            if (checkData.length === 0) {
                facade.error("请选择数据");
                return false;
            }
            let ids = [];
            let key;
            for (key in checkData) {
                ids.push(checkData[key].id);
            }
            facade.popupConfirm({text: "确定删除么?", path: apiPrefix + "del", params: {ids: ids.join(",")}});
        } else if (obj.event === "show-hidden-search-form") {
            if ($("#search-form").css("display") === "none") {
                $("[lay-event='show-hidden-search-form']").html(" 隐藏搜索");
                $("#search-form").show();
            } else {
                $("[lay-event='show-hidden-search-form']").html(" 展开搜索");
                $("#search-form").hide();
            }
        } else if (obj.event === "show-hidden-recycle-search-form") {
            if ($("#recycle-search-form").css("display") === "none") {
                $("[lay-event='show-hidden-recycle-search-form']").html(" 隐藏搜索");
                $("#recycle-search-form").show();
            } else {
                $("[lay-event='show-hidden-recycle-search-form']").html(" 展开搜索");
                $("#recycle-search-form").hide();
            }
        } else if (obj.event === "recycle") {
            let options = {};
            options.title = "回收站";
            options.path = apiPrefix + "recycle";
            options.btn = "";
            options.height = "80%";
            facade.popupDiv(options);
        } else if (obj.event === "restore") {
            let checkData;
            if (isTreeTable) {
                checkData = layTpTreeTable.checkStatus(false);
            } else {
                let checkStatus = layui.table.checkStatus(obj.config.id);
                checkData = checkStatus.data;
            }
            if (checkData.length === 0) {
                facade.error("请选择数据");
                return false;
            }
            let key;
            let ids = [];
            for (key in checkData) {
                ids.push(checkData[key].id);
            }
            facade.ajax({path: apiPrefix + "restore", params: {ids: ids.join(",")}}).done(function (res) {
                if (res.code === 0) {
                    funController.tableRender();
                    funRecycleController.tableRender();
                }
            });
        } else if (obj.event === "trueDel") {
            let checkData;
            if (isTreeTable) {
                checkData = layTpTreeTable.checkStatus(false);
            } else {
                let checkStatus = layui.table.checkStatus(obj.config.id);
                checkData = checkStatus.data;
            }
            if (checkData.length === 0) {
                facade.error("请选择数据");
                return false;
            }

            let key;
            let ids = [];
            for (key in checkData) {
                ids.push(checkData[key].id);
            }
            facade.popupConfirm({
                text: "真的在回收站删除么？此次删除将不能还原",
                path: apiPrefix + "trueDel",
                params: {ids: ids.join(",")}
            });
        }
    };

    /**
     * 监听数据表格操作列，默认按钮点击事件
     */
    layTp.tableTool = function (obj) {
        if (obj.event === "del") {
            facade.popupConfirm({text: "真的删除么?", path: apiPrefix + "del", params: {ids: obj.data.id}});
        } else if (obj.event === "edit") {
            let options = {};
            options.title = "编 辑";
            options.path = apiPrefix + "edit";
            options.data = obj.data;
            facade.popupDiv(options);
        } else if (obj.event === "restore") {
            facade.ajax({path: apiPrefix + "restore", params: {ids: obj.data.id}}).done(function (res) {
                if (res.code === 0) {
                    funController.tableRender();
                    funRecycleController.tableRender();
                }
            });
        } else if (obj.event === "trueDel") {
            facade.popupConfirm({
                text: "真的在回收站删除么？此次删除将不能还原",
                path: apiPrefix + "trueDel",
                params: {ids: obj.data.id}
            });
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
            let loading;
            $.ajaxSetup({
                timeout: 30000,
                headers: {
                    "LayTp-Admin-Token": facade.getCookie("laytp_admin_token"),
                    "Cache-Control": "no-cache"
                },
                beforeSend: function () {
                    loading = facade.loading();
                },
                complete: function () {
                    layui.layer.close(loading);
                }
            });
        },

        /**
         * 获取所有缓存信息
         */
        config: function () {
            if (facade.getCookie("laytp_admin_token")) {
                facade.ajax({
                    path: "plugin/core/common/getCache",
                    async: false,
                    successAlert: false
                }).done(function (res) {
                    sysConf = res.data["sysConf"] ? res.data["sysConf"] : "";
                    user = res.data["user"] ? res.data["user"] : "";
                    menu = res.data["menu"] ? res.data["menu"] : "";
                    authList = menu.authList;
                    //判断上传方式，设置访问上传资源的域名
                    // if(sysConf.upload.type === "qiniu"){
                    //     sysConf.upload.domain = "http://qiniuyun.com";
                    // }
                });
            }
        },

        /**
         * 点击按钮弹出表单层 - 使用例子：数据表格顶部的添加按钮
         * 所有拥有popup-div类名的节点，点击后都会弹出层
         *  data-name 弹窗标题，默认为当前节点的html()
         *  data-path 必填,弹窗展示的路由地址
         *  data-params 属性为节点id属性值，非必填，多个以逗号隔开，有这个值的话，会获取这些节点的值，拼接到url中，用途是当弹出层需要先使用表单填写一些数据然后再将填写的数据传入弹出层时使用
         *  data-btn 弹出层底部按钮，只有两种设置方式，1.不设置，表示弹出一个表单，底部展示['确定','取消']两个按钮，点击确定按钮可以提交表单。2.设置为空字符串，表示弹出一个非表单层，仅展示页面，底部没有按钮，其他复杂的弹出层，比如有三个或者三个以上按钮，请自行编写JS实现
         *  data-width 属性为弹窗的宽度百分比，非必填，默认值在/a/index.html中设置
         *  data-height 属性为弹窗的高度百分比，非必填，默认值在/a/index.html中设置
         *  data-shade 属性为弹窗背景阴影，非必填，默认值在/a/index.html中设置
         */
        popupDiv: function () {
            $(document).off("click", ".popup-div").on("click", ".popup-div", function () {
                let options = {};
                options.title = $(this).data("title") ? $(this).data("title") : $(this).html();
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
                options.path = $(this).data("path");
                let paramsIds = $(this).data("params");
                let params = {};
                if (paramsIds) {
                    let labelTitle = "";
                    let arrParamsIds = paramsIds.split(",");
                    let key;
                    for (key in arrParamsIds) {
                        if (!$("#" + arrParamsIds[key]).val()) {
                            facade.error("请输入" + labelTitle);
                            return false;
                        } else {
                            params[arrParamsIds[key]] = $("#" + arrParamsIds[key]).val();
                        }
                    }
                }
                options.params = params;
                facade.popupDiv(options);
            });
        },

        /**
         * 监听下拉菜单点击事件
         * data-path 请求路由
         * data-params 路由参数
         * data-needData 是否需要选择数据表格的复选框
         * data-tableId 数据表格id
         * data-switchType 操作类型，popupDiv=弹出层，popupConfirm=弹出确认框，为空表示直接ajax请求路由地址
         * data-width 弹出层宽度
         * data-height 弹出层高度
         * data-callback 回调函数名称
         * data-callbackParam 回调函数参数
         */
        dropDownAction: function () {
            $(document).off("click", ".dropdown-action").on("click", ".dropdown-action", function () {
                //执行callback
                let callback = $(this).data("callback");
                if (callback !== undefined && callback !== "undefined" && callback !== "") {
                    let callbackParam = $(this).data("callbackparam");
                    eval(callback + "(" + callbackParam + ")");
                    return true;
                }
                let path = $(this).data("path");
                if (path === undefined) {
                    facade.error("dropDown插件的options.path参数未定义");
                }
                let params = $(this).data("params") ? eval("(" + $(this).data("params") + ")") : {};
                let needData = $(this).data("needdata");
                let width = $(this).data("width") ? $(this).data("width") : defaultWidthPopupDiv;
                let height = $(this).data("height") ? $(this).data("height") : defaultHeightPopupDiv;
                let tableId = $(this).data("tableid");
                let text = $(this).text();
                let switchType = $(this).data("switchtype");
                let checkData;
                if (needData) {
                    if (tableId.indexOf("tree-table") !== -1) {
                        checkData = layTpTreeTable.checkStatus(false);
                    } else {
                        let checkStatus = layui.table.checkStatus(tableId);
                        checkData = checkStatus.data;
                    }
                    if (checkData.length === 0) {
                        facade.error("请选择数据");
                        return false;
                    }
                    if (checkData.length >= 30) {
                        facade.error("批量操作，选择了" + checkData.length + "条数据，最多同时选择30条数据");
                        return false;
                    }
                }
                let key;
                switch (switchType) {
                    case "popupDiv":
                        if (needData) {
                            for (key in checkData) {
                                facade.popupDiv({
                                    title: text,
                                    path: path,
                                    params: params,
                                    data: checkData[key],
                                    width: width,
                                    height: height
                                });
                            }
                        } else {
                            facade.popupDiv({
                                title: text,
                                path: path,
                                params: params,
                                width: width,
                                height: height
                            });
                        }
                        break;
                    case "popupConfirm":
                        let idsArr = [];
                        let idParams = {};
                        if (needData) {
                            for (key in checkData) {
                                idsArr.push(checkData[key].id);
                            }
                            idParams.ids = idsArr.join(",");
                        }
                        let popupConfirmText = "";
                        if (text === "删除") {
                            popupConfirmText = "真的在回收站删除么？此次删除将不能还原";
                        } else {
                            popupConfirmText = "确定" + text + "么?"
                        }
                        facade.popupConfirm({text: popupConfirmText, path: path, params: idParams});
                        break;
                    default:
                        if (needData) {
                            let ids = [];
                            for (key in checkData) {
                                ids.push(checkData[key].id);
                            }
                            facade.ajax({path: path, params: {ids: ids.join(",")}}).done(function (res) {
                                if (res.code === 0) {
                                    funController.tableRender();
                                    if (typeof funRecycleController !== "undefined") {
                                        funRecycleController.tableRender();
                                    }
                                }
                            });
                        } else {
                            facade.ajax({path: path, params: params});
                        }
                        break;
                }
            });
        },

        /**
         * 监听拥有layTpClickSearch样式的节点，点击进行表单搜索
         */
        layTpClickSearch: function () {
            $(document).off("click", ".layTpClickSearch").on("click", ".layTpClickSearch", function () {
                let field = $(this).data('field');
                if (!field) facade.error("tab的字段名未定义");
                let value = $(this).data('val');
                $("#" + field).val(value);
                layui.form.render('select');
                $("button[lay-filter=laytp-search-form]").click();
                if ($("#search-form").css("display") === "none") {
                    $("[lay-event='show-hidden-search-form']").html(" 展开搜索");
                } else {
                    $("[lay-event='show-hidden-search-form']").html(" 隐藏搜索");
                }
            });
        },

        /**
         * 监听拥有layerTips样式的节点，渲染layerTips组件
         */
        layerTips: function () {
            $(document).on('mouseover', '.layerTips', function () {
                let obj = $(this);
                let color = (typeof obj.attr('color') != 'undefined') ? obj.attr('color') : '#3595CC';
                let time = (typeof obj.attr('time') != 'undefined') ? obj.attr('time') : 800;
                let text = $(this).data('text');
                layui.layer.tips(text, this, {
                    tips: [1, color],
                    time: time
                });
            });
        },
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
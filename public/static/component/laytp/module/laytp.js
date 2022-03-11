/**
 * laytp极速后台框架layUI的js扩展，后台界面使用
 * @version: 2.0
 * @Author:  JunStar
 * @Date:    2018年09月20日11:47:00
 * @Last Modified by:   JunStar
 * @Last Modified time: 2021-09-22 17:19:40
 */
layui.define([
	"facade",           // 助手函数
    "laytpForm",        // 表单元素组件
    "treeTable",        // 树形表格组件
    "laytpDropdown",    // 数据表格顶部批量操作下拉组件
    "laytpTable",       // 数据表格处理完成后需要执行的操作组件
    "button",           // 按钮点击过渡效果组件
], function (exports) {
    const MOD_NAME = "laytp";

    let $ = layui.jquery
        , facade = layui.facade
        , laytp = {}
    ;

    window.$ = $;

    //数据表格，格式化展示数据
    laytp.tableFormatter = {
        custom: ["#FF5722", "#009688", "#FFB800", "#2F4056", "#1E9FFF", "#393D49", "#999999", "#0b1bf8", "#7a0bf8", "#f00bf8", "#5FB878", "#1E9FFF", "#2F4056"],
        status: function (field, value, json, isTreeTable) {
            let customIndex = 0, key;
            if(value){
                for (key in json['value']) {
                    if (value.toString() === json['value'][key].toString()) {
                        if(isTreeTable){
                            return '<span class="layui-icon layui-icon-circle-dot" style="color:' + laytp.tableFormatter.custom[customIndex] + ';font-size:14px;">' + json['text'][key] + '</span>';
                        }else{
                            return '<a href="javascript:void(0);" class="laytpClickSearch layerTips layui-icon layui-icon-circle-dot" data-field="' + field + '" data-val="' + value + '" data-text="点击搜索 ' + json['text'][key] + '" data-tipsColor="#393D49" style="color:' + laytp.tableFormatter.custom[customIndex] + ';font-size:14px;">' + json['text'][key] + '</a>';
                        }
                    }
                    customIndex++;
                }
            }
            return '';
        },
        colorPicker: function (value) {
            return "<span class='layui-badge' style='background-color:" + value + ";font-size:14px;'>&nbsp;&nbsp;</span>\n\t\t\t";
        },
        //form_type=checkbox
        flag: function (value, dataList) {
            let html = '';
            let customIndex = 0, key, v;
            let valueArr = value.toString().split(',');
            for (key in dataList.value) {
                for (v in valueArr) {
                    if (valueArr[v].toString() === dataList.value[key].toString()) {
                        customIndex = key % laytp.tableFormatter.custom.length;
                        html += '<span class="layui-btn layui-btn-xs" style="background-color: ' + laytp.tableFormatter.custom[customIndex] + '">' + dataList.text[key] + '</span>';
                    }
                }
            }
            return html;
        },
        images: function (value) {
            let html = "";
            if (value) {
                let valueArr = value.split(", "), key;
                for (key in valueArr) {
                    html += "<a target='_blank' href='" + valueArr[key] + "'><img src='" + valueArr[key] + "' style='width:30px;height:30px;' /></a> ";
                }
            }
            return html;
        },
        video: function (value) {
            let html = '';
            if (value) {
                let i = 1;
                let valueArr = value.split(','), key;
                for (key in valueArr) {
                    html += '<a href="javascript:void(0);" onclick="layui.laytp.tableFormatter.showVideo(\'' + valueArr[key] + '\')" class=\"layui-link\">视频' + i + '</a> ';
                    i++;
                }
            }
            return html;
        },
        showVideo: function (url) {
            layui.facade.popupDiv({
                title: '查看视频',
                path: "/admin/showVideo.html?video_url=" + window.btoa(url)
            });
        },
        audio: function (value) {
            let html = '';
            if (value) {
                let valueArr = value.split(','), key;
                for (key in valueArr) {
                    html += '<audio src="' + valueArr[key] + '" width="200px" height="30px" controls="controls"></audio>';
                }
            }
            return html;
        },
        file: function (value) {
            let html = '';
            if (value) {
                let i = 1;
                let valueArr = value.split(','), key;
                for (key in valueArr) {
                    html += '<a target="_blank" href="' + valueArr[key] + '" title="点击下载" class="layui-link">文件' + i + '</a> ';
                    i++;
                }
            }
            return html;
        },
    };

    /**
     * 监听数据表格顶部左侧默认按钮点击事件
     */
    laytp.tableToolbar = function (obj, isTreeTable) {
        if (obj.event === "add") {
            let options = {};
            options.title = "添 加";
            options.path = facade.compatibleHtmlPath(window.htmlPrefix) + "add.html";
            facade.popupDiv(options);
        } else if (obj.event === "edit") {
            let checkData;
            if (isTreeTable) {
                checkData = laytpTreeTable.checkStatus(false);
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
                    path: facade.compatibleHtmlPath(window.htmlPrefix) + "edit.html?id=" + checkData[key].id
                });
            }
        } else if (obj.event === "del") {
            let checkData;
            if (isTreeTable) {
                checkData = laytpTreeTable.checkStatus(false);
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
            if(isTreeTable){
                facade.popupConfirm({text: "确定删除么?", route: window.apiPrefix + "del", data: {ids: ids}}, function(res){
                    if(res.code === 0){
                        if(typeof obj.del === "function"){
                            obj.del();
                        }else{
                            funController.tableRender();
                        }
                    }
                });
            }else{
                facade.popupConfirm({text: "确定删除么?", route: window.apiPrefix + "del", data: {ids: ids}});
            }
        } else if (obj.event === "search") {
            if ($("#search-form").css("display") === "none") {
                $("#search-form").show();
            } else {
                $("#search-form").hide();
            }
        } else if (obj.event === "recycle-search") {
            if ($("#recycle-search-form").css("display") === "none") {
                $("#recycle-search-form").show();
            } else {
                $("#recycle-search-form").hide();
            }
        } else if (obj.event === "recycle") {
            let options = {};
            options.title = "回收站";
            options.path = facade.compatibleHtmlPath(window.htmlPrefix) + "recycle.html";
            facade.popupDiv(options);
        } else if (obj.event === "restore") {
            let checkData;
            if (isTreeTable) {
                checkData = laytpTreeTable.checkStatus(false);
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
            facade.ajax({
                route: window.apiPrefix + "restore",
                data: {ids: ids},
                showLoading: true
            }).done(function (res) {
                if (res.code === 0) {
                    parent.funController.tableRender();
                    funRecycleController.tableRender();
                }
            });
        } else if (obj.event === "true-del") {
            let checkData;
            if (isTreeTable) {
                checkData = laytpTreeTable.checkStatus(false);
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
                route: window.apiPrefix + "trueDel",
                data: {ids: ids}
            });
        } else if (obj.event === "refresh"){
            if(typeof funController !== "undefined"){
                funController.tableRender(layui.form.val("layui-form"),$(".layui-laypage-em").next().html());
            }
        } else if(obj.event === "recycle-refresh"){
            if(typeof funRecycleController !== "undefined"){
                funRecycleController.tableRender(layui.form.val("layui-form"),$(".layui-laypage-em").next().html());
            }
        }
    };

    /**
     * 监听数据表格操作列，默认按钮点击事件
     */
    laytp.tableTool = function (obj, isTreeTable) {
        if (obj.event === "del") {
            if(isTreeTable){
                facade.popupConfirm({text: "真的删除么?", route: window.apiPrefix + "del", data: {ids: [obj.data.id]}},function(res){
                    if(res.code === 0){
                        obj.del();
                    }
                });
            }else{
                facade.popupConfirm({text: "真的删除么?", route: window.apiPrefix + "del", data: {ids: [obj.data.id]}});
            }
        } else if (obj.event === "edit") {
            let options = {};
            options.title = "编 辑";
            options.path = facade.compatibleHtmlPath(window.htmlPrefix) + "edit.html?id=" + obj.data.id;
            facade.popupDiv(options);
        } else if (obj.event === "restore") {
            facade.ajax({route: window.apiPrefix + "restore", data: {ids: [obj.data.id]}}).done(function (res) {
                if (res.code === 0) {
                    parent.funController.tableRender();
                    funRecycleController.tableRender();
                }
            });
        } else if (obj.event === "true-del") {
            facade.popupConfirm({
                text: "真的在回收站删除么？此次删除将不能还原",
                route: window.apiPrefix + "trueDel",
                data: {ids: [obj.data.id]}
            });
        }
    };

    //初始化
    laytp.init = {
        /**
         * 全局ajax设置
         * - 设置默认的headers
         * - ajax过度效果设置
         */
        ajaxSet: function () {
            let commonHeaders = {
                "LayTp-Admin-Token": facade.getCookie("laytpAdminToken"),
                "Cache-Control": "no-cache"
            };
            $.ajaxSetup({
                timeout: 30000,
                headers: commonHeaders,
            });
        },

        /**
         * 点击按钮弹出表单层
         * 所有拥有popupDiv类名的节点，点击后都会弹出层
         *  data-name 弹窗标题，默认为当前节点的html()
         *  data-url 必填,弹窗展示的静态文件url
         *  data-btn 弹出层底部按钮，只有两种设置方式，1.不设置，表示弹出一个表单，底部展示['确定','取消']两个按钮，点击确定按钮可以提交表单。2.设置为空字符串，表示弹出一个非表单层，仅展示页面，底部没有按钮，其他复杂的弹出层，比如有三个或者三个以上按钮，请自行编写JS实现
         *  data-width 属性为弹窗的宽度百分比，非必填，默认值在/a/index.html中设置
         *  data-height 属性为弹窗的高度百分比，非必填，默认值在/a/index.html中设置
         *  data-shade 属性为弹窗背景阴影，非必填，默认值在/a/index.html中设置
         */
        popupDiv: function () {
            $(document).off("click", ".popupDiv").on("click", ".popupDiv", function () {
                let options = {};
                options.title = $(this).data("title") ? $(this).data("title") : $(this).html();
                options.shade = $(this).data("shade");
                if (!options.shade) options.shade = 0.01;
                options.width = $(this).data("width");
                options.height = $(this).data("height");
                options.path = $(this).data("url");
                facade.popupDiv(options);
            });
        },

        /**
         * 点击按钮打开新的tab menu
         * 所有拥有tabMenu类名的节点，点击后都会打开新的tab menu
         *  data-id 菜单ID，用于菜单自动选中
         *  data-url 必填,弹窗展示的静态文件url
         *  data-title tab菜单的名称
         */
        openTabMenu: function(){
            $(document).off("click", ".openTabMenu").on("click", ".openTabMenu", function () {
                if (parent.config.tab.muiltTab) {
                    parent.bodyTab.addTabOnly({
                        id: $(this).data("id"),
                        title: $(this).data("title"),
                        url: $(this).data("url"),
                        icon: "",
                        close: true
                    }, 300);
                } else {
                    parent.bodyFrame.changePage($(this).data("url"), "", true);
                }
            });
        },

        /**
         * 监听拥有laytpClickSearch样式的节点，点击进行表单搜索
         */
        laytpClickSearch: function () {
            $(document).off("click", ".laytpClickSearch").on("click", ".laytpClickSearch", function () {
                let field = $(this).data('field');
                if (!field) facade.error("tab的字段名未定义");
                let value = $(this).data('val');
                $("#" + field).val(value);
                layui.form.render('select');
                $("button[lay-filter=laytp-search-form]").click();
                $("button[lay-filter=laytp-recycle-search-form]").click();
                if ($("#search-form").css("display") === "none") {
                    $("#search-form").show();
                }
                if ($("#recycle-search-form").css("display") === "none") {
                    $("#recycle-search-form").show();
                }
            });
        },

        /**
         * 监听.delArrayItem节点，删除表格中的一行
         *  .delArrayItem节点存在的位置举例：系统配置，数组类型配置，删除某一个数组元素
         */
        delArrayItem: function(){
            $(document).off("click", ".delArrayItem").on('click','.delArrayItem',function(){
                let clickObj = $(this);
                let childrenLength = clickObj.parent().parent().parent().children().length;
                if(childrenLength>2){
                    clickObj.parent().parent().remove();
                }else{
                    facade.error('请留下这唯一的一行数据');
                }
            });
        },

        /**
         * 监听.addArrayItem节点，追加表格中的一行
         *  .addArrayItem节点存在的位置举例：系统配置，数组类型配置，追加一个数组元素
         */
        addArrayItem: function(){
            $(document).off("click", ".addArrayItem").on('click','.addArrayItem',function(){
                let clickObj = $(this);
                var itemHtml = clickObj.parent().parent().prev().prop("outerHTML");
                clickObj.parent().parent().before(itemHtml);
            });
        },

        /**
         * 监听.tableDnd节点，允许拖动排序
         *  .tableDnd节点存在的位置举例：系统配置，数组类型配置，拖动排序数组元素
         *
         *  要求
         *      - 页面上要加载jquery_3.3.1.js和jquery.tablednd.js两个js库
         *      - table的class要加上tableDnd
         *
         *  注意
         *      - 使用任意layui.js不存在的第三方jQuery库都需要加载
         *      ```
         *      /static/component/jquery_3.3.1.js
         *      ```
         *      - 然后再加载第三方jQuery库，比如当前是表格排序库
         *      ```
         *      /static/component/jquery.tablednd.js
         *      ```
         *      - 且一定要注意加载顺序，layui.js要优先加载，否则layui可能会报错
         *          - 先加载layui.js
         *          - 然后加载layuiConfig.js
         *          - 再加载jquery_3.3.1.js
         *          - 最后加载第三方jQuery库jquery.tablednd.js
         */
        tableDnd:function(){
            layui.each($(".tableDnd"),function(key,item) {
                $(item).tableDnD({});
            });
        },

        /**
         * 监听拥有layerTips样式的节点，渲染layerTips组件
         * data-color 弹窗背景颜色
         * data-time 弹窗展示时间，单位秒
         * data-text 弹窗内文本
         */
        layerTips: function () {
            var tipsIndex;
            // 鼠标进入层就展示Tips
            $(document).off('mouseover', '.layerTips').on('mouseover', '.layerTips', function () {
                let obj = $(this);
                let color = (typeof obj.data('color') != 'undefined') ? obj.data('color') : '#3595CC';
                let time = (typeof obj.data('time') != 'undefined') ? obj.data('time') : 5000;
                let text = $(this).data('text');
                tipsIndex = layui.layer.tips(text, this, {
                    tips: [4, color]
                    , time: time
                });
            });

            // 鼠标离开层就关闭Tips
            $(document).off('mouseout', '.layerTips').on('mouseout', '.layerTips', function () {
                layui.layer.close(tipsIndex);
            });
        },

        //锁屏
        lockScreen: function(){
            let lockScreenStatus = localStorage.getItem("laytpLockScreen");
            $(document).ready(function(){
                if( lockScreenStatus === "locked" ){
                    layui.facade.popupDiv({
                        title: '锁屏',
                        path: '/admin/lockScreen.html',
                        shade: 0.5
                    });
                }
            });


            $(document).on('click','#lockScreen',function(){
                layui.layer.confirm('锁屏后，需要使用登录密码解锁，确定锁屏么?', {title: "锁屏确认继续", skin: "laytp"}, function(index){
                    layui.layer.close(index);
                    localStorage.setItem("laytpLockScreen", "locked");
                    layui.facade.popupDiv({
                        title: '锁屏',
                        path: '/admin/lockScreen.html',
                        shade: 0.5
                    });
                });
            });
        },
    };

    layui.each(laytp.init, function (key, item) {
        if (typeof item === "function") {
            item();
        }
    });

    layui.laytpForm.render();

    //输出模块
    exports(MOD_NAME, laytp);

    layui.laytp = laytp;

    window.laytp = laytp;
    window.$ = $;
});
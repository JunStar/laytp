layui.use(["laytp"], function () {
    const funController = {};
    //静态页面地址前缀
    window.htmlPrefix = facade.compatibleHtmlPath("/admin/menu/");
    //后端接口地址前缀
    window.apiPrefix  = facade.compatibleApiRoute("/admin.menu/");

    //表格渲染
    funController.tableRender = function (where) {
        //treeTable必须赋值，批量操作需要使用这个值获取复选框选中的数据
        laytpTreeTable = layui.treeTable.render({
            elem: "#laytp-tree-table"
            , url: facade.url("/admin.menu/index",{is_tree:1})
            , where: where
            , method: "GET"
            , toolbar: "#menu-toolbar"
            , defaultToolbar: [{
                title: '刷新',
                layEvent: 'refresh',
                icon: 'layui-icon-refresh',
            }, 'filter', 'print', 'exports']
            , skin: 'line'
            , tree: {
                iconIndex: 2        // 折叠图标显示在第几列
                , arrowType: 'arrow2'   // 自定义箭头风格
                , getIcon: function (d) {  // 自定义图标
                    // d是当前行的数据
                    if (d.children && d.children.length > 0) {  // 判断是否有子集
                        return '<i class="laytp-tree-icon laytp-tree-icon-folder"></i>';
                    } else {
                        return '<i class="laytp-tree-icon laytp-tree-icon-file"></i>';
                    }
                }
            }
            , parseData: function (res) {
                return facade.parseTableData(res, false);
            }
            , cols: [[ //表头
                {type: "checkbox"}
                , {field: "id", title: "ID", align: "center", width: 80}
                , {field: "name", title: "名称"}
                , {field: "rule", title: "路由规则", width: 200}
                , {field: "icon", title: "图标", align: "center", width: 80, templet: function (d) {
                    return "<i class=\"" + d.icon + "\"></i>";
                }}
                , {field: "sort", title: "排序", width: 80, align: "center", templet:function(d){
                    return laytpForm.tableForm.editInput('sort',d,'/admin.menu/setSort');
                }}
                , {
                    field: "is_menu", title: "菜单", align: "center", width: 90, templet: function (d) {
                        return laytpForm.tableForm.switch("is_menu", d, {
                            "open": {"value": 1, "text": "是"},
                            "close": {"value": 2, "text": "否"}
                        }, 'laytp-table-switch',true);
                    }
                }
                , {
                    field: "is_show", title: "显示", align: "center", width: 90, templet: function (d) {
                        return laytpForm.tableForm.switch("is_show", d, {
                            "open": {"value": 1, "text": "是"},
                            "close": {"value": 2, "text": "否"}
                        }, 'laytp-table-switch',true);
                    }
                }
                , {
                    field: "operation",
                    title: "操作",
                    toolbar: "#default-bar",
                    fixed: "right",
                    align: "center",
                    width: 160
                }
            ]]
        });

        //监听数据表格顶部左侧按钮点击事件
        layui.treeTable.on("toolbar(laytp-tree-table)", function (obj) {
            if (obj.event === "del") {
                let checkData = laytpTreeTable.checkStatus(false);
                if (checkData.length === 0) {
                    facade.error("请选择数据");
                    return false;
                }
                let ids = [];
                let key;
                for (key in checkData) {
                    ids.push(checkData[key].id);
                }
                facade.popupConfirm({text: "确定删除么?", route: window.apiPrefix + "del", data: {ids: ids}}, function(){
                    funController.tableRender();
                    parent.parent.renderMenu();//重新渲染菜单
                });
            }else{
                //默认按钮点击事件，包括添加按钮和回收站按钮
                var defaultTableToolbar = layui.context.get("defaultTableToolbar");
                if (defaultTableToolbar.indexOf(obj.event) !== -1) {
                    laytp.tableToolbar(obj, true);
                    //其他自定义按钮点击事件
                } else {
                    //自定义按钮点击事件
                    switch(obj.event){
                        //复制
                        case "copy":
                            var checkData = laytpTreeTable.checkStatus(false);
                            if (checkData.length === 0) {
                                facade.error("请选择数据");
                                return false;
                            }
                            facade.popupDiv({
                                title: "复制菜单",
                                path: facade.compatibleHtmlPath(window.htmlPrefix) + "copy.html"
                            });
                            break;
                        //移动
                        case "move":
                            var checkData = laytpTreeTable.checkStatus(false);
                            if (checkData.length === 0) {
                                facade.error("请选择数据");
                                return false;
                            }
                            facade.popupDiv({
                                title: "移动菜单",
                                path: facade.compatibleHtmlPath(window.htmlPrefix) + "move.html"
                            });
                            break;
                    }
                }
            }
        });

        //监听数据表格[操作列]按钮点击事件
        layui.treeTable.on("tool(laytp-tree-table)", function (obj) {
            if (obj.event === "del") {
                facade.popupConfirm({text: "真的删除么?", route: window.apiPrefix + "del", data: {ids: [obj.data.id]}},function(){
                    obj.del();
                    parent.parent.renderMenu();//重新渲染菜单
                });
            }else{
                var defaultTableTool = layui.context.get("defaultTableTool");
                if (defaultTableTool.indexOf(obj.event) !== -1) {
                    laytp.tableTool(obj);
                } else {
                    // //自定义按钮点击事件
                    // switch(obj.event){
                    // //自定义按钮点击事件
                    // case "":
                    //
                    //     break;
                    // }
                }
            }
        });
    };

    funController.tableRender();

    window.funController = funController;
});
layui.use(["laytp"], function () {
    const funController = {};
    //静态页面地址前缀
    window.htmlPrefix = facade.compatibleHtmlPath("/admin/user/");
    //后端接口地址前缀
    window.apiPrefix  = facade.compatibleApiRoute("/admin.user/");

    //表格渲染
    funController.tableRender = function (where, page) {
        layui.table.render({
            elem: "#laytp-table"
            , id: "laytp-table"
            , url: facade.url("/admin.user/index")
            , toolbar: "#default-toolbar"
            , defaultToolbar: [{
                title: '刷新',
                layEvent: 'refresh',
                icon: 'layui-icon-refresh',
            }, 'filter', 'print', 'exports']
            , where: where
            , autoSort: false
            , method: "GET"
            , cellMinWidth: 80
            , skin: 'line'
            , loading: false
            , page: {
                curr: page
            }
            , parseData: function (res) { //res 即为原始返回的数据
                return facade.parseTableData(res, true);
            }
            , done: function(){
                layui.laytpTable.done();
            }
            , cols: [[
                {type: "checkbox"}
                , {field: "id", title: "ID", align: "center", width: 80}
                , {field: "username", title: "用户名", align: "center"}
                , {field: "nickname", title: "昵称", align: "center"}
                , {
                    field: "avatar", title: "头像", align: "center", templet: function (d) {
                        return d.avatar_file ? laytp.tableFormatter.images(d.avatar_file.path) : laytp.tableFormatter.images("/static/admin/images/avatar.jpg");
                    }
                }
                , {
                    field: "is_super_manager", title: "是否超管", align: "center", templet: function (d) {
                        if (d.id === 1) {
                            return (d.is_super_manager === 1) ? "是" : "否";
                        } else {
                            return laytpForm.tableForm.switch("is_super_manager", d, {
                                "open": {"value": 1, "text": "是"},
                                "close": {"value": 2, "text": "否"}
                            });
                        }
                    }
                }
                , {
                    field: "status", title: "账号状态", align: "center", templet: function (d) {
                        if (d.id === 1) {
                            return (d.status === 1) ? "正常" : "禁用";
                        } else {
                            return laytpForm.tableForm.switch("status", d, {
                                "open": {"value": 1, "text": "正常"},
                                "close": {"value": 2, "text": "禁用"}
                            });
                        }
                    }
                }
                , {field: "create_time", title: "创建时间", align: "center", width: 160}
                , {field: "operation", title: "操作", align: "center", fixed: 'right', toolbar: "#default-bar", width: 140}
            ]]
        });

        //监听数据表格顶部左侧按钮点击事件
        layui.table.on("toolbar(laytp-table)", function (obj) {
            //默认按钮点击事件，包括添加按钮和回收站按钮
            var defaultTableToolbar = layui.context.get("defaultTableToolbar");
            if (defaultTableToolbar.indexOf(obj.event) !== -1) {
                laytp.tableToolbar(obj);
            } else {
                // //自定义按钮点击事件
                // switch(obj.event){
                // //自定义按钮点击事件
                // case "":
                //
                //     break;
                // }
            }
        });

        //监听数据表格[操作列]按钮点击事件
        layui.table.on("tool(laytp-table)", function (obj) {
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
        });

        //监听表头排序事件
        layui.table.on('sort(laytp-table)', function(obj){
            layui.table.reload('laytp-table', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。
                , where: {
                    "order_param" : {
                        "field" : obj.field,
                        "type" : obj.type
                    }
                }
            });
        });
    };

    funController.tableRender();

    window.funController = funController;
});
layui.use(["layTp"], function () {
    const funController = {};

    defaultHeightPopupDiv = "620px"; //覆盖弹出层默认高度，设置当前操作页面的所有弹出层默认高度

    //表格渲染
    funController.tableRender = function (where, page) {
        layui.table.render({
            elem: "#laytp-table"
            , id: controller
            , url: facade.url(apiPrefix + "index")
            , toolbar: "#default_toolbar"
            , where: where
            , even: true
            , method: "POST"
            , cellMinWidth: 80
            , loading: false
            , page: {
                curr: page
            }
            , parseData: function (res) { //res 即为原始返回的数据
                return {
                    "code": res.code, //解析接口状态
                    "msg": res.msg, //解析提示文本
                    "count": res.data.total, //解析数据长度
                    "data": res.data.data //解析数据列表
                };
            }
            , cols: [[
                {type: "checkbox"}
                , {field: "id", title: "ID", align: "center", width: 80}
                , {field: "username", title: "用户名", align: "center"}
                , {field: "nickname", title: "昵称", align: "center"}
                , {
                    field: "avatar", title: "头像", align: "center", templet: function (d) {
                        return layTp.tableFormatter.images(d.avatar);
                    }
                }
                , {
                    field: "is_super_manager", title: "是否超管", align: "center", templet: function (d) {
                        if (d.id === 1) {
                            return (d.is_super_manager === 1) ? "是" : "否";
                        } else {
                            return layTpForm.tableForm.switch("is_super_manager", d, {
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
                            return layTpForm.tableForm.switch("status", d, {
                                "open": {"value": 1, "text": "正常"},
                                "close": {"value": 2, "text": "禁用"}
                            });
                        }
                    }
                }
                , {field: "create_time", title: "创建时间", align: "center", width: 160}
                , {field: "operation", title: "操作", align: "center", toolbar: "#default_operation", width: 140}
            ]]
        });

        //监听数据表格顶部左侧按钮点击事件
        layui.table.on("toolbar(laytp-table)", function (obj) {
            //默认按钮点击事件，包括添加按钮和回收站按钮
            if (defaultTableToolbar.indexOf(obj.event) !== -1) {
                layTp.tableToolbar(obj);
                //其他自定义按钮点击事件
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
            if (defaultTableTool.indexOf(obj.event) !== -1) {
                layTp.tableTool(obj);
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

        //监听鼠标双击行事件，双击行表示进行编辑
        layui.table.on("rowDouble(laytp-table)", function (obj) {
            obj.event = "edit";
            layTp.tableTool(obj);
        });
    };

    funController.tableRender();

    window.funController = funController;
});
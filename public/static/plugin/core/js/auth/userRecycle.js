layui.use(['layTp'], function () {
    const
        funRecycleController = {}
        , facade = layui.facade
        , layTpForm = layui.layTpForm
    ;

    //表格渲染
    funRecycleController.tableRender = function (where, page) {
        layui.table.render({
            elem: "#laytp-recycle-table"
            , url: facade.url(apiPrefix + "recycle")
            , where: where
            , method: "POST"
            , toolbar: "#recycle-toolbar"
            , even: true
            , loading: false
            , page: {
                theme: "var(--laytp-layui-laypage-em)"
                , curr: page
            }
            , parseData: function (res) {
                return {
                    "code": res.code, //解析接口状态
                    "msg": res.msg, //解析提示文本
                    "count": res.data.total, //解析数据长度
                    "data": res.data.data //解析数据列表
                };
            }
            , cols: [[ //表头
                {type: "checkbox"}
                , {field: "id", title: "ID", align: "center", width: 80}
                , {field: "username", title: "用户名", align: "center", width: 80}
                , {field: "nickname", title: "昵称", align: "center", width: 80}
                , {
                    field: "avatar", title: "头像", align: "center", templet: function (d) {
                        return layTp.tableFormatter.images(d.avatar);
                    }
                }
                , {
                    field: "is_super_manager", title: "是否超管", align: "center", width: 100, templet: function (d) {
                        return layTpForm.tableForm.recycleSwitch("is_super_manager", d, {
                            "open": {"value": 1, "text": "是"},
                            "close": {"value": 2, "text": "否"}
                        });
                    }
                }
                , {
                    field: "status", title: "账号状态", align: "center", width: 100, templet: function (d) {
                        return layTpForm.tableForm.recycleSwitch("status", d, {
                            "open": {"value": 1, "text": "正常"},
                            "close": {"value": 2, "text": "禁用"}
                        });
                    }
                }
                , {field: "create_time", title: "创建时间", align: "center", width: 160}
                , {
                    field: 'operation',
                    title: '操作',
                    toolbar: '#recycle-operation',
                    fixed: 'right',
                    align: 'center',
                    width: 140
                }
            ]]
        });

        //监听数据表格顶部左侧按钮点击事件
        layui.table.on("toolbar(laytp-recycle-table)", function (obj) {
            if (defaultTableToolbar.indexOf(obj.event) !== -1) {
                //默认按钮点击事件
                layTp.tableToolbar(obj);
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
        layui.table.on('tool(laytp-recycle-table)', function (obj) {
            if (defaultTableTool.indexOf(obj.event) !== -1) {
                layTp.tableTool(obj);
            } else {
                //自定义按钮
                // switch(obj.event){
                // case '':
                //
                //     break;
                // }
            }
        });
    };

    funRecycleController.tableRender();

    window.funRecycleController = funRecycleController;
});
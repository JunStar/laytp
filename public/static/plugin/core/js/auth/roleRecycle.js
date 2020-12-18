layui.use(['layTp'], function () {
    const
        funRecycleController = {}
        , layTp = layui.layTp
        , facade = layui.facade
        , layTpForm = layui.layTpForm
    ;

    //表格渲染
    funRecycleController.tableRender = function (where, page) {
        layui.table.render({
            elem: "#laytp-recycle-table"
            , id: controller + "recycle"
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
                , {field: "name", title: "角色名"}
                , {
                    field: "operation",
                    title: "操作",
                    toolbar: "#recycle-operation",
                    fixed: "right",
                    align: "center",
                    width: 140
                }
            ]]
        });

        //监听数据表格顶部左侧按钮点击事件
        layui.table.on('toolbar(laytp-recycle-table)', function (obj) {
            //默认按钮点击事件，包括添加按钮和回收站按钮
            if (defaultTableToolbar.indexOf(obj.event) !== -1) {
                layTp.tableToolbar(obj);
                //其他自定义按钮点击事件
            } else {
                // //自定义按钮点击事件
                // switch(obj.event){
                // //自定义按钮点击事件
                // case '':
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
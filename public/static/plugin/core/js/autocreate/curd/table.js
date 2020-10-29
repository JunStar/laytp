layui.use(["layTp"], function () {
    const funController = {};

    defaultHeightPopupDiv = "620px"; //覆盖弹出层默认高度，设置当前操作页面的所有弹出层默认高度

    //表格渲染
    funController.tableRender = function (where, page) {
        layui.table.render({
            elem: "#mysql-table"
            , id: controller + "mysql-table" //一个页面展示多个数据表格时，此配置要不同，如果相同，在使用监听方法时，匿名回调的参数obj会取最后一个，也就是会被覆盖
            , url: facade.url(apiPrefix + "index")
            , toolbar: "#mysql_table_toolbar"
            , defaultToolbar: []
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
                , {field: "database", title: "数据库", align: "center", width: 100}
                , {field: "table", title: "表名", align: "center", width: 150}
                , {field: "comment", title: "表注释", align: "center"}
                , {field: "engine", title: "存储引擎", align: "center", width: 100}
                , {field: "collation", title: "字符集/排序规则", align: "center", width: 150}
                , {field: "operation", title: "操作", align: "center", toolbar: "#mysql_table_operation", width: 360}
            ]]
        });

        //监听数据表格顶部左侧按钮点击事件
        layui.table.on("toolbar(mysql-table)", function (obj) {
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
        layui.table.on("tool(mysql-table)", function (obj) {
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
    };

    funController.tableRender();

    window.funController = funController;
});
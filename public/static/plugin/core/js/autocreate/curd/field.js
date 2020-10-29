layui.use(["layTp"], function () {
    const funController = {};

    defaultHeightPopupDiv = "600px"; //覆盖弹出层默认高度，设置当前操作页面的所有弹出层默认高度

    //表格渲染
    funController.tableRender = function (where, page) {
        layui.table.render({
            elem: "#mysql-table"
            , id: controller
            , url: facade.url(apiPrefix + "index")
            , toolbar: "#field_toolbar"
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
                , {field: "table_id", title: "数据表ID", align: "center", width: 100}
                , {field: "field", title: "字段名", align: "center", width: 150}
                , {field: "comment", title: "字段注释", align: "center"}
                , {field: "form_type", title: "表单元素", align: "center", width: 100}
                , {field: "sort", title: "排序", align: "center", width: 150}
                , {field: "operation", title: "操作", align: "center", toolbar: "#field_operation", width: 360}
            ]]
        });

        //监听数据表格顶部左侧按钮点击事件
        layui.table.on("toolbar(field-table)", function (obj) {
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
        layui.table.on("tool(field-table)", function (obj) {
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
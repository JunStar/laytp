layui.use(["layTp"],function() {
    const funcController = {};

    defaultHeightPopupDiv = "520px"; //覆盖弹出层默认高度，设置当前操作页面的所有弹出层默认高度

    //定义下拉菜单列表
    let batchActionOptions = [
        {
            action: "edit"
            ,title: "编辑"
            ,icon: "layui-icon layui-icon-edit"
            ,node: "admin/" + controller + "/edit"
            ,switch_type: "popupDiv"
            ,table_id:controller
        }
        ,{
            action: "del"
            ,title: "删除"
            ,icon: "layui-icon layui-icon-delete"
            ,node: "admin/" + controller + "/del"
            ,switch_type: "popupConfirm"
            ,table_id:controller
        }
    ];
    //节点id="batch-action"绑定点击事件，点击后展示下拉菜单
    facade.dropDown(batchActionOptions, true, "#batch-action");

    //表格渲染
    funcController.tableRender = function (where, page) {
        layui.table.render({
            elem: "#laytp-table"
            , id: controller
            , url: "/admin/"+controller+"/index.html"
            , toolbar: "#default_toolbar"
            , loading: false
            , where: where
            , even: true
            , method: "GET"
            , cellMinWidth: 80
            , page: {
                curr: page
            }
            , parseData: function(res){ //res 即为原始返回的数据
                return {
                    "code": res.code, //解析接口状态
                    "msg": res.msg, //解析提示文本
                    "count": res.data.total, //解析数据长度
                    "data": res.data.data //解析数据列表
                };
            }
            , cols: [[
                {type:"checkbox"}
                ,{field: "id", title: "ID", align: "center",width:80}
                , {field: "name", title: "角色名"}
                , {field: "operation", title: "操作", toolbar: "#default_operation", fixed: "right", align: "center",width:140}
            ]]
        });

        //监听数据表格顶部左侧按钮点击事件
        layui.table.on("toolbar(laytp-table)", function(obj){
            //默认按钮点击事件，包括添加按钮和回收站按钮
            if(defaultTableToolbar.indexOf(obj.event) !== -1){
                layTp.tableToolbar(obj);
                //其他自定义按钮点击事件
            }else{
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
        layui.table.on("tool(laytp-table)", function(obj){
            if(defaultTableTool.indexOf(obj.event) !== -1){
                layTp.tableTool(obj);
            }else{
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
        layui.table.on("rowDouble(laytp-table)", function(obj){
            obj.event = "edit";
            layTp.tableTool(obj);
        });
    };

    funcController.tableRender();

    window.funcController = funcController;
});
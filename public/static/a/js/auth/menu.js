layui.use(["layTp"], function () {
    const funcController = {};

    defaultHeightPopupDiv = "480px"; //覆盖弹出层默认高度，设置当前操作页面的所有弹出层默认高度

    //定义下拉菜单列表
    let batchActionOptions = [
        {
            action: "edit"
            , title: "编辑"
            , icon: "layui-icon layui-icon-edit"
            , node: "admin/" + controller + "/edit"
            , switch_type: "popupDiv"
            , table_id: "laytp-tree-table"
        }
        , {
            action: "del"
            , title: "删除"
            , icon: "layui-icon layui-icon-delete"
            , node: "admin/" + controller + "/del"
            , switch_type: "popupConfirm"
            , table_id: "laytp-tree-table"
        }
    ];
    //节点id="batch-action"绑定点击事件，点击后展示下拉菜单
    facade.dropDown(batchActionOptions, true, "#batch-action");

    //表格渲染
    funcController.tableRender = function (where) {
        //treeTable必须赋值，批量操作需要使用这个值获取复选框选中的数据
        layTpTreeTable = layui.treeTable.render({
            elem: "#laytp-tree-table"
            , url: "/admin/" + controller + "/index.html"
            , where: where
            , method: "POST"
            , toolbar: "#toolbar"
            , even: true
            , tree: {
                iconIndex: 2        // 折叠图标显示在第几列
                , isPidData: true   // 是否是id、pid形式数据
                , idName: "id"      // id字段名称
                , pidName: "pid"    // pid字段名称
            }
            , cols: [[ //表头
                {type: "checkbox"}
                , {field: "id", title: "ID", align: "center", width: 80}
                , {field: "name", title: "名称"}
                , {field: "rule", title: "路由规则"}
                , {field: "icon", title: "图标", align: "center", templet: "#show_icon", width: 80}
                , {field: "sort", title: "排序", align: "center", width: 80}
                , {
                    field: "is_menu", title: "菜单", align: "center", width: 90, templet: function (d) {
                        return layTpForm.tableForm.switch("is_menu", d, {
                            "open": {"value": 1, "text": "是"},
                            "close": {"value": 2, "text": "否"}
                        });
                    }
                }
                , {
                    field: "is_show", title: "显示", align: "center", width: 90, templet: function (d) {
                        return layTpForm.tableForm.switch("is_show", d, {
                            "open": {"value": 1, "text": "是"},
                            "close": {"value": 2, "text": "否"}
                        });
                    }
                }
                , {field: "operation", title: "操作", toolbar: "#operation", fixed: "right", align: "center", width: 140}
            ]]
        });

        //监听数据表格顶部左侧按钮点击事件
        layui.treeTable.on("toolbar(laytp-tree-table)", function (obj) {
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
        layui.treeTable.on("tool(laytp-tree-table)", function (obj) {
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
        layui.treeTable.on("rowDouble(laytp-tree-table)", function (obj) {
            obj.event = "edit";
            layTp.tableTool(obj);
        });
    };

    funcController.tableRender();

    window.funcController = funcController;

    var tabIndex;

    /**
     * 需要解绑后再绑定，否则每多点击一次左侧 [菜单管理] 菜单后，再点击[添加] -> [选择图标]， 就会多弹一个框
     */
    $(document).off("click", "#select-icon").on("click", "#select-icon", function () {
        var width = "45%";
        var height = "50%";
        var left = (document.body.offsetWidth - 230 - facade.rtrim(width, "%") / 100 * document.body.offsetWidth) / 2 + 230;
        var layuiIconsHtml = "";
        var fontAwesomeIconsHtml = "";
        $.ajax({
            "url": "/static/a/data/layuiIcons.json",
            "async": false,
            "dataType": "json",
            "success": function (res) {
                layuiIconsHtml = layui.laytpl($("#choose_icon_tpl").html()).render(res.data);
            }
        });

        $.ajax({
            "url": "/static/a/data/fontAwesomeIcons.json",
            "async": false,
            "dataType": "json",
            "success": function (res) {
                fontAwesomeIconsHtml = layui.laytpl($("#choose_icon_tpl").html()).render(res.data);
            }
        });

        tabIndex = layer.tab({
            area: [width, height]
            , shade:0.1
            , offset: ["190px", left + "px"]
            , tab: [{
                title: "LayUI",
                content: layuiIconsHtml
            }, {
                title: "font-awesome",
                content: fontAwesomeIconsHtml
            }]
        });
    });

    //监听点击某个图标事件，关闭弹出层，将值输入Input并把图标展示在input后面的i标签内
    $(document).off("click", ".pop-select-to-input").on("click", ".pop-select-to-input", function () {
        var value = $(this).data("input_value");
        $("#icon_i").attr("class", value);
        $("#icon").val(value);
        layer.close(tabIndex);
    });

    //监听图标input的input事件，修改图标Input后面的i标签class
    $(document).off("input", "#icon").on("input", "#icon", function () {
        var icon_val = $(this).val();
        $("#icon_i").attr("class", icon_val);
    });
});
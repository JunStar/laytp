layui.use(["layTp"], function () {
    const funController = {};

    defaultHeightPopupDiv = "520px"; //覆盖弹出层默认高度，设置当前操作页面的所有弹出层默认高度

    //表格渲染
    funController.tableRender = function (where) {
        //treeTable必须赋值，批量操作需要使用这个值获取复选框选中的数据
        layTpTreeTable = layui.treeTable.render({
            elem: "#laytp-tree-table"
            , url: facade.url(apiPrefix + "index")
            , where: where
            , method: "POST"
            , toolbar: "#menu_toolbar"
            , even: true
            , tree: {
                iconIndex: 2        // 折叠图标显示在第几列
                , arrowType: 'arrow2'   // 自定义箭头风格
                , getIcon: function (d) {  // 自定义图标
                    // d是当前行的数据
                    if (d.children.length > 0) {  // 判断是否有子集
                        return '<i class="ew-tree-icon ew-tree-icon-folder"></i>';
                    } else {
                        return '<i class="ew-tree-icon ew-tree-icon-file"></i>';
                    }
                }
            }
            , parseData: function (res) {
                //默认展开三级菜单
                let key;
                for (key in res.data) {
                    res.data[key].open = true;
                    if (res.data[key].children.length > 0) {
                        let k;
                        for (k in res.data[key].children) {
                            res.data[key].children[k].open = true;
                        }
                    }
                }
                return res;
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
                , {
                    field: "operation",
                    title: "操作",
                    toolbar: "#default_operation",
                    fixed: "right",
                    align: "center",
                    width: 140
                }
            ]]
        });

        //监听数据表格顶部左侧按钮点击事件
        layui.treeTable.on("toolbar(laytp-tree-table)", function (obj) {
            //默认按钮点击事件，包括添加按钮和回收站按钮
            if (defaultTableToolbar.indexOf(obj.event) !== -1) {
                layTp.tableToolbar(obj, true);
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

    funController.tableRender();

    window.funController = funController;

    let tabIndex;

    /**
     * 需要解绑后再绑定，否则每多点击一次左侧 [菜单管理] 菜单后，再点击[添加] -> [选择图标]， 就会多弹一个框
     */
    $(document).off("click", "#select-icon").on("click", "#select-icon", function () {
        let width = "45%";
        let height = "50%";
        let left = (document.body.offsetWidth - 230 - facade.rtrim(width, "%") / 100 * document.body.offsetWidth) / 2 + 230;
        let layuiIconsHtml = "";
        let fontAwesomeIconsHtml = "";
        $.ajax({
            "url": "/static/plugin/core/data/layuiIcons.json",
            "async": false,
            "dataType": "json",
            "success": function (res) {
                layuiIconsHtml = layui.laytpl($("#choose_icon_tpl").html()).render(res.data);
            }
        });

        $.ajax({
            "url": "/static/plugin/core/data/fontAwesomeIcons.json",
            "async": false,
            "dataType": "json",
            "success": function (res) {
                fontAwesomeIconsHtml = layui.laytpl($("#choose_icon_tpl").html()).render(res.data);
            }
        });

        tabIndex = layer.tab({
            area: [width, height]
            , shade: 0.1
            , offset: ["", left + "px"]
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
        let value = $(this).data("input_value");
        $("#icon_i").attr("class", value);
        $("#icon").val(value);
        layer.close(tabIndex);
    });

    //监听图标input的input事件，修改图标Input后面的i标签class
    $(document).off("input", "#icon").on("input", "#icon", function () {
        let icon_val = $(this).val();
        $("#icon_i").attr("class", icon_val);
    });
});
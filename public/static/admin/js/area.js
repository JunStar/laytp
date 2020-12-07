layui.use(["layTp"], function () {
    const funController = {};

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
            , cellMinWidth: 100
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
                {type: 'checkbox', fixed: 'left'}
                , {field: 'id', title: 'ID', align: 'center', width: 80, fixed: 'left'}
                , {field: 'pid', title: '父id', width: 80, align: 'center', sort: true}
                , {field: 'short_name', title: '简称', width: 110, align: 'center', sort: true}
                , {field: 'merge_name', title: '全称', width: 260, align: 'center', sort: true}
                , {
                    field: 'level', title: '层级', width: 100, align: 'center', sort: true, templet: function (d) {
                        return layTp.tableFormatter.status('level', d.level, {
                            "value": ["1", "2", "3"],
                            "text": ["省", "市", "区"]
                        });
                    }
                }
                , {field: 'pinyin', title: '拼音', width: 170, align: 'center', sort: true}
                , {field: 'code', title: '长途区号', width: 100, align: 'center', sort: true}
                , {field: 'zip', title: '邮编', width: 80, align: 'center', sort: true}
                , {field: 'first', title: '首字母', width: 90, align: 'center', sort: true}
                , {field: 'lng', title: '经度', width: 100, align: 'center', sort: true}
                , {field: 'lat', title: '纬度', width: 100, align: 'center', sort: true}
                , {
                    field: 'operation',
                    title: '操作',
                    align: 'center',
                    toolbar: '#default_operation',
                    width: 140,
                    fixed: 'right'
                }
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
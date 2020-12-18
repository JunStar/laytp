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
                {type: 'checkbox'}
                , {field: 'id', title: 'ID', align: 'center', width: 60}
                , {field: 'pid', title: '上级ID', align: 'center', width: 80}
                , {field: 'name', title: '名称'}
                , {field: 'rule', title: '规则'}
                , {field: 'icon', title: '图标', align: 'center', templet: '#show_icon', width: 60}
                , {field: 'sort', title: '排序', align: 'center', width: 60}
                , {
                    field: 'is_menu', title: '菜单', align: 'center', width: 80, templet: function (d) {
                        return layTpForm.tableForm.recycleSwitch('is_menu', d, {
                            "open": {"value": 1, "text": "是"},
                            "close": {"value": 0, "text": "否"}
                        });
                    }
                }
                , {
                    field: 'is_show', title: '显示', align: 'center', width: 80, templet: function (d) {
                        return layTpForm.tableForm.recycleSwitch('is_show', d, {
                            "open": {"value": 1, "text": "是"},
                            "close": {"value": 0, "text": "否"}
                        });
                    }
                }
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
        layui.table.on('toolbar(laytp-recycle-table)', function (obj) {
            if (defaultTableToolbar.indexOf(obj.event) !== -1) {
                //默认按钮点击事件
                layTp.tableToolbar(obj);
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
                //自定义按钮点击事件
                // case '':
                //
                //     break;
                //默认自定义按钮点击事件
                // default:
                //     facade.popupDiv(obj.tr.context.innerText, facade.url('/admin/' + controller + '/' + obj.event), obj.data);
                //     layTpForm.render();
                // }
            }
        });
    };

    funRecycleController.tableRender();

    window.funRecycleController = funRecycleController;
});
layui.use(['layTp'],function() {
    const
        func_controller = {}
        ,layTp = layui.layTp
    ;

    //批量操作下拉展示列表设置
    let batch_dropdown_list = [
        {
            action: "edit"
            ,title: "编辑"
            ,icon: "layui-icon-edit"
            ,uri: layTp.facade.url(module + "/" + controller + "/edit")
            ,switch_type: "popup_frame"
        }
        ,{
            action: 'del',
            title: '删除'
            ,icon: "layui-icon-delete"
            ,uri: layTp.facade.url(module + "/" + controller + "/del")
            ,switch_type: "confirm_action"
        }
    ];

    layTp.facade.dropdown_set(batch_dropdown_list,true);

    //表格渲染
    func_controller.table_render = function (where) {
        let toolbar_node ='';
        if(where == undefined){
            where = {'is_menu':1};
        }
        if(where.is_menu == 'all'){
            toolbar_node = '#menu_toolbar_is_menu';
        }else{
            toolbar_node = '#menu_toolbar_is_menu_all';
        }
        layui.table.render({
            elem: '.laytp-table'
            , id: table_id
            , url: window.location.href
            , toolbar: toolbar_node
            , even: true
            , where: where
            , method: 'GET'
            , cellMinWidth: 80 //全局定义常规单元格的最小宽度，layui 2.2.1 新增
            // , page: true //开启分页
            , cols: [[ //表头
                {type:'checkbox'}
                ,{field: 'id', title: 'ID', sort: true, align: 'center',width:80}
                , {field: 'name', title: '标题'}
                , {field: 'rule', title: '规则'}
                , {field: 'icon', title: '图标', align: 'center', templet: '#show_icon',width:80}
                , {field: 'sort', title: '排序', align: 'center',width:80}
                , {field: 'is_menu', title: '菜单', align: 'center',width:80, templet: function(d){
                    return layTp.facade.formatter.switch('is_menu',d,{"open":{"value":1,"text":"是"},"close":{"value":0,"text":"否"}});
                }}
                , {field: 'is_hide', title: '隐藏', align: 'center',width:80, templet: function(d){
                    return layTp.facade.formatter.switch('is_hide',d,{"open":{"value":1,"text":"是"},"close":{"value":0,"text":"否"}});
                }}
                , {field: 'operation', title: '操作', toolbar: '#operation', fixed: 'right', align: 'center', width: 100}
            ]]
        });

        //监听默认工具条
        layui.table.on('tool(default)', function(obj){
            if(default_table_tool.indexOf(obj.event) != -1){
                layTp.facade.table_tool(obj);
            }else{
                //新增的其他操作按钮在这里来写
                //switch(obj.event){
                //    case '':
                //
                //        break;
                //}
            }
        });
    }

    func_controller.table_render();

    window.func_controller = func_controller;
});
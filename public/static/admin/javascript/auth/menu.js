layui.use(['layTp'],function() {
    const
        func_controller = {}
        ,layTp = layui.layTp
        ,$ = layui.jquery
    ;

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
            elem: '.layui-hide-sm'
            , url: window.location.href
            , toolbar: toolbar_node
            , even: true
            , where: where
            , method: 'GET'
            , cellMinWidth: 80 //全局定义常规单元格的最小宽度，layui 2.2.1 新增
            // , page: true //开启分页
            , cols: [[ //表头
                {field: 'id', title: 'ID', sort: true, align: 'center'}
                , {field: 'name', title: '标题', width: 300}
                , {field: 'rule', title: '规则', width: 220}
                , {field: 'icon', title: '图标', align: 'center', templet: '#show_icon'}
                , {field: 'sort', title: '排序', align: 'center'}
                , {field: 'is_menu', title: '是否菜单', templet: '#switch_is_menu', align: 'center'}
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

        //监听是否菜单操作
        layui.form.on('switch(set_is_menu)', function(obj){
            var is_menu_list = {1:true,0:false};
            for(key in is_menu_list){
                if(is_menu_list[key] == obj.elem.checked){
                    var post_data = {field:this.name,field_val:key,id:this.value};
                    $.ajax({
                        url: layTp.facade.url(module + '/' + controller + '/set_status/'),
                        method: 'POST',
                        data: post_data,
                        success: function(res){
                            if(res.code == 1){
                                layTp.facade.success(res.msg);
                                func_controller.table_render();
                            }else{
                                layTp.facade.error(res.msg);
                                func_controller.table_render();
                            }
                        },
                    });
                }
            }
        });
    }

    func_controller.table_render();

    window.func_controller = func_controller;
});
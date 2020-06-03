layui.use(['layTp'],function() {
    const
        func_controller = {}
        ,layTp = layui.layTp
        ,$ = layui.jquery
    ;

    func_controller.table_render = function (where) {
        layui.table.render({
            elem: '.laytp-table'
            , url: window.location.href
            , toolbar: '#menu_toolbar'
            , where: where
            , even: true
            , method: 'GET'
            , cellMinWidth: 80 //全局定义常规单元格的最小宽度，layui 2.2.1 新增
            , page: true //开启分页
            , cols: [[ //表头
                {field: 'id', title: 'ID', sort: true, align: 'center',width:80}
                , {field: 'first_menu_id', title: '所属一级菜单', align: 'center',templet:'<div>{{# if(d.first_menu){ }}{{d.first_menu.name}}{{# }else{ }}-{{# } }}</div>'}
                , {field: 'second_menu_id', title: '所属二级菜单', align: 'center',templet:'<div>{{# if(d.second_menu){ }}{{d.second_menu.name}}{{# }else{ }}-{{# } }}</div>'}
                , {field: 'controller', title: '控制器文件名', align: 'center'}
                , {field: 'create_time', title: '生成时间', align: 'center'}
                , {field: 'operation', title: '操作', toolbar: '#operation', fixed: 'right', align: 'center', width: 100}
            ]]
        });

        //监听默认工具条
        layui.table.on('tool(default)', function(obj){
            let data = obj.data;
            //点击删除按钮
            if(obj.event === 'del'){
                layui.layer.confirm('真的删除么?', function(index){
                    $.ajax({
                        type: 'POST',
                        url: layTp.facade.addon_url('autocreate',module + '/' + controller +'/del'),
                        data: {id:data.id},
                        dataType: 'json',
                        success: function (res) {
                            if( res.code == 1 ){
                                obj.del();
                            }else{
                                layTp.facade.error(res.msg);
                            }
                            layui.layer.close(index);
                        },
                        error: function (xhr) {
                            if( xhr.status == '500' ){
                                layTp.facade.error('本地网络问题或者服务器错误');
                            }else if( xhr.status == '404' ){
                                layTp.facade.error('请求地址不存在');
                            }
                        }
                    });
                });
                //点击编辑按钮
            }else if(obj.event === 'edit'){
                var url = layTp.facade.addon_url('autocreate',module + '/' + controller + '/edit',{id:data.id});
                layTp.facade.popup_frame('编辑', url, '800px', '500px');
            }else if(obj.event === 're_create'){
                $.ajax({
                    type: 'POST',
                    url: layTp.facade.addon_url('autocreate',module + '/' + controller +'/re_create'),
                    data: {id:data.id},
                    dataType: 'json',
                    success: function (res) {
                        if( res.code == 1 ){
                            layTp.facade.success(res.msg);
                            func_controller.table_render();
                        }else{
                            layTp.facade.error(res.msg);
                        }
                    },
                    error: function (xhr) {
                        if( xhr.status == '500' ){
                            layTp.facade.error('本地网络问题或者服务器错误');
                        }else if( xhr.status == '404' ){
                            layTp.facade.error('请求地址不存在');
                        }
                    }
                });
            }
        });
    }

    func_controller.table_render();

    window.func_controller = func_controller;

});
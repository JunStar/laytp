layui.use(['junAdmin'],function() {
    const
        func_controller = {}
        ,junAdmin = layui.junAdmin
        ,$ = layui.jquery
    ;

    func_controller.table_render = function (where) {
        layui.table.render({
            elem: '.layui-hide-sm'
            , url: window.location.href
            , toolbar: '#menu_toolbar'
            , even: true
            , where: where
            , method: 'POST'
            , cellMinWidth: 80 //全局定义常规单元格的最小宽度，layui 2.2.1 新增
            // , page: true //开启分页
            , cols: [[ //表头
                {field: 'id', title: 'ID', sort: true, fixed: 'left', align: 'center'}
                , {field: 'name', title: '角色名'}
                , {field: 'operation', title: '操作', toolbar: '#operation', fixed: 'right', align: 'center'}
            ]]
        });

        //监听工具条
        layui.table.on('tool(default)', function(obj){
            var data = obj.data;
            //点击删除按钮
            if(obj.event === 'del'){
                layer.confirm('真的删除行么', function(index){
                    $.ajax({
                        type: 'POST',
                        url: junAdmin.facade.url('admin/core.menu/del'),
                        data: {id:data.id},
                        dataType: 'json',
                        success: function (res) {
                            if( res.code == 1 ){
                                obj.del();
                            }else{
                                junAdmin.facade.error(res.msg);
                            }
                            layer.close(index);
                        },
                        error: function (xhr) {
                            if( xhr.status == '500' ){
                                junAdmin.facade.error('本地网络问题或者服务器错误');
                            }else if( xhr.status == '404' ){
                                junAdmin.facade.error('请求地址不存在');
                            }
                        }
                    });
                });
                //点击编辑按钮
            }else if(obj.event === 'edit'){
                var url = junAdmin.facade.url(module + '/' + controller + '/edit',{id:data.id});
                junAdmin.facade.popup_frame('添加', url, '800px', '500px');
            }
        });

        //监听是否菜单操作
        layui.form.on('switch(set_is_menu)', function(obj){
            var is_menu_list = {1:true,0:false};
            for(key in is_menu_list){
                if(is_menu_list[key] == obj.elem.checked){
                    var post_data = {field:this.name,field_val:key,id:this.value};
                    $.ajax({
                        url: junAdmin.facade.url(module + '/' + controller + '/set_status/'),
                        method: 'POST',
                        data: post_data,
                        success: function(res){
                            if(res.code == 1){
                                junAdmin.facade.success(res.msg);
                                func_controller.table_render();
                            }else{
                                junAdmin.facade.error(res.msg);
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
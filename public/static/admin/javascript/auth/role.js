layui.use(['layTp'],function() {
    const
        func_controller = {}
        ,layTp = layui.layTp
        ,$ = layui.jquery
    ;

    //批量操作渲染
    layui.dropdown.render({
        elem: '.action-more',
        options: [
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
        ]
    });

    //表格渲染
    func_controller.table_render = function (where) {
        layui.table.render({
            elem: '.laytp-table'
            , id: table_id
            , url: window.location.href
            , toolbar: '#role_toolbar'
            , even: true
            , where: where
            , method: 'GET'
            , cellMinWidth: 80 //全局定义常规单元格的最小宽度，layui 2.2.1 新增
            // , page: true //开启分页
            , cols: [[ //表头
                {type:'checkbox'}
                ,{field: 'id', title: 'ID', sort: true, align: 'center',width:80}
                , {field: 'name', title: '角色名'}
                , {field: 'operation', title: '操作', toolbar: '#operation', fixed: 'right', align: 'center',width:100}
            ]]
        });

        //监听工具条
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
            // var data = obj.data;
            // //点击删除按钮
            // if(obj.event === 'del'){
            //     layer.confirm('真的删除行么', function(index){
            //         $.ajax({
            //             type: 'POST',
            //             url: layTp.facade.url('admin/auth.role/del'),
            //             data: {ids:data.id},
            //             dataType: 'json',
            //             success: function (res) {
            //                 if( res.code == 1 ){
            //                     obj.del();
            //                 }else{
            //                     layTp.facade.error(res.msg);
            //                 }
            //                 layer.close(index);
            //             },
            //             error: function (xhr) {
            //                 if( xhr.status == '500' ){
            //                     layTp.facade.error('本地网络问题或者服务器错误');
            //                 }else if( xhr.status == '404' ){
            //                     layTp.facade.error('请求地址不存在');
            //                 }
            //             }
            //         });
            //     });
            //     //点击编辑按钮
            // }else if(obj.event === 'edit'){
            //     var url = layTp.facade.url(module + '/' + controller + '/edit',{id:data.id});
            //     layTp.facade.popup_frame('添加', url, '800px', '500px');
            // }
        });
    }

    func_controller.table_render();

    window.func_controller = func_controller;
});
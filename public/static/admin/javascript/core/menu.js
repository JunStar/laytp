(function func_controller() {
    var controller = {};

    controller.table_render = function (where) {
        table.render({
            elem: '.layui-hide-sm'
            , url: window.location.href
            , where: where
            , method: 'POST'
            , cellMinWidth: 80 //全局定义常规单元格的最小宽度，layui 2.2.1 新增
            , page: true //开启分页
            , cols: [[ //表头
                {field: 'id', title: 'ID', sort: true, fixed: 'left', align: 'center'}
                , {field: 'name', title: '标题', align: 'center'}
                , {field: 'rule', title: '规则', sort: true, align: 'center'}
                , {field: 'icon', title: '图标', align: 'center'}
                , {field: 'sort', title: '排序', align: 'center'}
                , {field: 'operation', title: '操作', toolbar:'#operation', fixed: 'right', align: 'center'}
            ]]
        });

        //监听工具条
        table.on('tool(default)', function(obj){
            var data = obj.data;
            if(obj.event === 'del'){
                layer.confirm('真的删除行么', function(index){
                    $.ajax({
                        type: 'POST',
                        url: facade.url('admin/core.menu/del'),
                        data: {id:data.id},
                        dataType: 'json',
                        success: function (res) {
                            if( res.code == 1 ){
                                obj.del();
                            }else{
                                facade.error(res.msg);
                            }
                            layer.close(index);
                        },
                        error: function (xhr) {
                            if( xhr.status == '500' ){
                                facade.error('本地网络问题或者服务器错误');
                            }else if( xhr.status == '404' ){
                                facade.error('请求地址不存在');
                            }
                        }
                    });
                });
            }else if(obj.event === 'edit'){
                layer.alert('编辑行：<br>'+ JSON.stringify(data));
            }
        });
    }

    controller.table_render();

    window.controller = controller;
})()
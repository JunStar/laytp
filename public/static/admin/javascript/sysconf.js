layui.use(['layTp'],function() {
    const
        layTp = layui.layTp
        , $ = layui.jquery
    ;


    $(document).on('click','.add_array_item',function(){
        let click_obj = $(this);
        let config_key = click_obj.attr('config_key');
        layui.laytpl($('#array_item_html').html()).render({
            config_key: config_key
        }, function(string){
            click_obj.parent().parent().before(string);
        });
    });

    $(document).on('click','.del_array_item',function(){
        let click_obj = $(this);
        click_obj.parent().parent().remove();
    });

    $(document).on('click','.delete-item',function(){
        let click_obj = $(this);
        layui.layer.confirm('确定删除么?', function(index){
            $.ajax({
                type: 'POST',
                url: layTp.facade.url('admin/sysconf/del'),
                data: {ids:click_obj.data('id')},
                dataType: 'json',
                success: function (res) {
                    if( res.code == 1 ){
                        click_obj.parent().parent().remove();
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
    });

    //删除分组
    $(document).on('click','#del-group',function(){
        layui.layer.confirm('确定删除么?', function(index){
            $.ajax({
                type: 'POST',
                url: layTp.facade.url('admin/sysconf/del_group'),
                data: {group:$('#group').val()},
                dataType: 'json',
                success: function (res) {
                    if( res.code == 1 ){
                        parent.parent.location.reload();
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
    });
});
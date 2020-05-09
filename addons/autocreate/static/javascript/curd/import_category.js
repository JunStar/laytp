layui.use(['layTp'],function(){
    const
        func_controller = {}
        ,layTp = layui.layTp
        ,$ = layTp.$
    ;

    let select_table_name = '';

    func_controller.init = function(){
        //监听表单元素下拉框onchange事件
        layui.form.on('select(select_table)',function(data){
            select_table_name = data.value;

            $.ajax({
                type: 'GET',
                url: layTp.facade.url('/' + module + '/' + controller + '/get_fields_by_table_name'),
                data: {table_name:select_table_name},
                dataType: 'json',
                success: function(res){
                    let html = '';
                    for(key in res.data){
                        html += '<option value="'+res.data[key]+'">'+res.data[key]+'</option>';
                    }
                    $('#parent_field').html(html);
                    $('#name_field').html(html);
                    let s_html = '<option>请选择</option>';
                    $('#sort_field').html(s_html + html);
                    layui.form.render('select');
                }
                ,error: function (xhr) {
                    if( xhr.status == '500' ){
                        layTp.facade.error('本地网络问题或者服务器错误');
                    }else if( xhr.status == '404' ){
                        layTp.facade.error('请求地址不存在');
                    }
                }
            });
        });

        //点击提交按钮
        $(document).on('click','#curd_import_category_btn', function(){
            let parent_field = $('#parent_field').val();
            let name_field = $('#name_field').val();
            let sort_field = $('#sort_field').val();
            let common_model = $('#common_model:checked').val();
            common_model = (typeof common_model == "undefined") ? 0 : common_model;
            if(!select_table_name){
                layTp.facade.error('请选择表');
            }
            if(!parent_field){
                layTp.facade.error('请选择父级字段');
            }
            $.ajax({
                type: 'POST',
                url: layTp.facade.url('/' + module + '/' + controller + '/import_category'),
                data: {table_name:select_table_name,parent_field:parent_field,name_field:name_field,sort_field:sort_field,common_model:common_model},
                dataType: 'json',
                success: function(res){
                    if(res.code == 1){
                        layTp.facade.success(res.msg);
                        if(parent.window.location.href.indexOf('import_category.html') == -1){
                            parent.func_controller.table_render();
                            parent.layui.layer.closeAll();
                        }
                    }else{
                        layTp.facade.error(res.msg);
                    }
                }
                ,error: function (xhr) {
                    if( xhr.status == '500' ){
                        layTp.facade.error('本地网络问题或者服务器错误');
                    }else if( xhr.status == '404' ){
                        layTp.facade.error('请求地址不存在');
                    }
                }
            });
            return false;
        });
    };

    func_controller.init();

    window.func_controller = func_controller;
});

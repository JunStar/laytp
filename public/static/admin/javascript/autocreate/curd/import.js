(function func_controller() {
    var func_controller = {};

    layui.use(['selectM'],function(){
        var selectM = layui.selectM;
        selectM({
            //元素容器【必填】
            elem: '#select_fields'
            //候选数据【必填】
            ,data: [{id:1,name:'test'},{id:2,name:'test1'}]
            ,max:2
            ,width:400
            //添加验证
            ,verify:'required'
            ,click_dd_after:function(){
                console.log('选中数据后，执行回调函数');
            }
        });
    });

    //监听选择表下拉框onchange事件
    form.on('select(select_table)',function(data){
        var post_data = {'table_name':data.value};
        $.ajax({
            type: 'POST',
            url: facade.url('/' + module + '/' + controller + '/get_fields_by_table_name'),
            data: post_data,
            dataType: 'json',
            success: function (res) {
                if( res.code == 1 ){
                    for(key in res.data){
                        $('#select_fields').append('<option value="'+res.data[key]['field_name']+'">'+res.data[key]['field_name']+'</option>');
                        form.render('select');
                    }
                }else{
                    facade.error(res.msg);
                }
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

    func_controller.table_render = function () {
        table.render({
            elem: '.layui-hide-sm'
            ,cellMinWidth: 100
            ,text:{none:'请选择需要显示的字段'}
            ,data:[]
            ,cols: [
                [
                    {title:'数据库字段查看', width:100, align: 'center', colspan: 3}
                    ,{title:'表单设置,影响添加编辑表单', align: 'center', colspan: 3}
                    ,{title:'列表设置', align: 'center', colspan: 5}
                ]
                ,[
                    //数据库字段设置
                    {title:'序号', type:'numbers', align: 'center'}
                    ,{field:'field_name', title:'字段名称', width:100, align: 'center', edit: 'text'}
                    ,{field:'field_comment', title:'字段注释(表头和表单中显示的文字)', width:100, align: 'center', edit: 'text'}
                    //表单设置
                    ,{field:'form_type', title:'表单元素', templet: "#form_type", align: 'center', width: 170}
                    ,{field:'form_additional', title:'附加设置', templet: "#form_additional", align: 'center', width: 220}
                    ,{field:'form_required', title:'允许为空', templet: "#form_required", align: 'center', width: 140}
                    //列表设置
                    ,{field:'table_width', title:'绝对列宽(数字或者百分比)', align: 'center', edit: 'text'}
                    ,{field:'table_min_width', title:'最小列宽(数字或者百分比)', align: 'center', edit: 'text'}
                    ,{field:'table_type', title:'列类型', templet: '#table_type', align: 'center', width: 120}
                    ,{field:'table_align', title:'排列方式', templet: '#table_align', align: 'center', width: 120}
                    ,{field:'table_additional', title:'附加选项', templet: "#table_additional", align: 'center', width: 400}
                ]
            ]
            ,done:function(res){
                func_controller.data = res.data;
            }
        });

        //监听表单元素下拉框onchange事件
        form.on('select(form_type)',function(data){
            var id = data.elem.id;
            var id_arr = id.split('_');
            var field_name = id_arr[id_arr.length - 1];
            var value = data.elem.value;
            func_controller.form_type_select_after(field_name, value);
            form.render('select');
            return true;
        });
    }

    func_controller.table_render();

    func_controller.form_type_select_after = function(field_name, value){
        var input_html = '<select name="form_additional_input_'+field_name+'" id="form_additional_input_'+field_name+'">' +
            '<option value="">不限制</option>' +
            '<option value="email">Email</option>' +
            '<option value="required|phone">手机号码</option>' +
            '<option value="number">数字</option>' +
            '<option value="url">链接</option>' +
            '<option value="identity">身份证</option>' +
            '</select>';
        var set_value_html = '<input type="text" class="layui-input layui-input-inline" placeholder="value1=text1,value2=text2..." name="form_additional_set_value_input_'+field_name+'" id="form_additional_set_value_input_'+field_name+'" />';
        var select_page_html = '<select name="form_additional_select_page_table_'+field_name+'" id="form_additional_select_page_table_'+field_name+'">' +
            '<option value="">搜索的表名</option>' +
            '<option value="ja_test">ja_test</option>' +
            '</select>' +
            '<select name="form_additional_select_page_search_field_'+field_name+'" id="form_additional_select_page_search_field_'+field_name+'">' +
            '<option value="">搜索的字段</option>' +
            '<option value="ja_test">ja_test</option>' +
            '</select>' +
            '<select name="form_additional_select_page_show_field_'+field_name+'" id="form_additional_select_page_show_field_'+field_name+'">' +
            '<option value="">显示的字段</option>' +
            '<option value="ja_test">ja_test</option>' +
            '</select>' +
            '<select name="form_additional_select_page_save_field_'+field_name+'" id="form_additional_select_page_save_field_'+field_name+'">' +
            '<option value="">存入的字段</option>' +
            '<option value="ja_test">ja_test</option>' +
            '</select>';
        var time_html = '<select name="form_additional_time_'+field_name+'" id="form_additional_time_'+field_name+'">' +
            '<option value="Y-m-d H:i:s">年-月-日 时:分:秒</option>' +
            '<option value="Y-m-d H:i">年-月-日 时:分</option>' +
            '<option value="Y-m-d H">年-月-日 时</option>' +
            '<option value="Y-m-d">年-月-日</option>' +
            '<option value="Y">年</option>' +
            '<option value="m">月</option>' +
            '<option value="d">日</option>' +
            '<option value="Y-m">年-月</option>' +
            '<option value="m-d">月-日</option>' +
            '</select>';
        var city_html = '';
        var upload_html = '';
        var textarea_html = '';
        var editor_html = '';
        var type_arr = ['input','select_page','time','city','upload','textarea','editor'];
        var set_value_input_type = ['radio','checkbox','select'];
        if(set_value_input_type.indexOf(value) != -1){
            $('#form_additional_' + field_name).html(set_value_html);
        }else if(type_arr.indexOf(value) != -1){
            $('#form_additional_' + field_name).html(eval(value+'_html'));
        }
    }

    func_controller.curd_set_config = function(){
        $('#curd_set_config_btn').on('click', function(){
            var table_data_arr = table.cache[1];
            var field_name
                ,field_comment
                ,form_type
                ,form_additional
                ,form_required
                ,table_width
                ,table_min_width
                ,table_type
                ,table_align
                ,table_additional_unresize
                ,table_additional_sort
                ,table_additional_edit
                ;
            var post_data = {'field_list':{},'global':{}};
            for(key in table_data_arr){
                field_name = table_data_arr[key]['field_name'];
                field_comment = table_data_arr[key]['field_comment'];
                form_type = $('#form_type_'+field_name).val();
                form_additional = get_form_additional_val(field_name,form_type);
                form_required = $('#form_required_'+field_name+':checked').val();
                form_required = (typeof form_required == "undefined") ? 0 : form_required;
                table_width = table_data_arr[key]['table_width'];
                table_min_width = table_data_arr[key]['table_min_width'];
                table_type = $('#table_type_'+field_name).val();
                table_align = $('#table_align_'+field_name).val();
                table_additional_unresize = $('#table_additional_unresize_'+field_name+':checked').val();
                table_additional_unresize = (typeof table_additional_unresize == "undefined") ? 0 : table_additional_unresize;
                table_additional_sort = $('#table_additional_sort_'+field_name+':checked').val();
                table_additional_sort = (typeof table_additional_sort == "undefined") ? 0 : table_additional_sort;
                table_additional_edit = $('#table_additional_edit_'+field_name+':checked').val();
                table_additional_edit = (typeof table_additional_edit == "undefined") ? 0 : table_additional_edit;
                post_data['field_list'][key] = {
                    'field_name':field_name
                    ,'field_comment':field_comment
                    ,'form_type':form_type
                    ,'form_additional':form_additional
                    ,'form_required':form_required
                    ,'table_width':table_width
                    ,'table_min_width':table_min_width
                    ,'table_type':table_type
                    ,'table_align':table_align
                    ,'table_additional_unresize':table_additional_unresize
                    ,'table_additional_sort':table_additional_sort
                    ,'table_additional_edit':table_additional_edit
                };
            }
            var common_model
                ,hide_pk
                ,create_number;
            common_model = $('#common_model:checked').val();
            common_model = (typeof common_model == "undefined") ? 0 : common_model;
            hide_pk = $('#hide_pk:checked').val();
            hide_pk = (typeof hide_pk == "undefined") ? 0 : hide_pk;
            create_number = $('#create_number:checked').val();
            create_number = (typeof create_number == "undefined") ? 0 : create_number;
            post_data['global'] = {
                'common_model':common_model
                ,'hide_pk':hide_pk
                ,'create_number':create_number
            };
            console.log(post_data);
            $.ajax({
                type: 'POST',
                url: window.location.href,
                data: post_data,
                dataType: 'json',
                success: function (res) {
                    if( res.code == 1 ){
                        facade.success(res.msg);
                        parent.func_controller.table_render();
                        parent.layer.closeAll();
                    }else{
                        facade.error(res.msg);
                    }
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

        function get_form_additional_val(field_name,value){
            var type_arr = ['input','select_page','time','city','upload','textarea','editor'];
            var set_value_input_type = ['radio','checkbox','select'];
            if(set_value_input_type.indexOf(value) != -1){
                return $('#form_additional_set_value_input_' + field_name).val();
            }else if(type_arr.indexOf(value) != -1){
                if(value != 'select_page'){
                    return $('#form_additional_' + value + '_' + field_name).val();
                }else{

                }
            }
        }
    }
    func_controller.curd_set_config();

    window.func_controller = func_controller;
})()
layui.use(['junAdmin'],function(){
    const
        func_controller = {}
        ,junAdmin = layui.junAdmin
        ,$ = junAdmin.$
        ,facade = junAdmin.facade
        ,form = junAdmin.form
        ,table = junAdmin.table
        ,select_multi = junAdmin.select_multi
    ;

    //渲染表格
    func_controller.table_render = function (data) {
        table.render({
            elem: '.layui-hide-sm'
            ,cellMinWidth: 100
            ,text:{none:'请选择需要显示的字段'}
            ,data:data
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
                    ,{field:'table_min_width', title:'最小列宽(数字或者百分比)', align: 'center', edit: 'text', width: 120}
                    ,{field:'table_type', title:'列类型', templet: '#table_type', align: 'center', width: 120}
                    ,{field:'table_align', title:'排列方式', templet: '#table_align', align: 'center', width: 120}
                    ,{field:'table_additional', title:'附加选项', templet: "#table_additional", align: 'center', width: 400}
                ]
            ]
        });

        //监听表单元素下拉框onchange事件
        form.on('select(form_type)',function(data){
            let field_name = $('#'+data.elem.id).data('field_name');
            let value = data.elem.value;
            func_controller.form_type_select_after(field_name, value);
            form.render('select');
            return true;
        });
    }

    //初始化方法，页面加载完毕立即执行的内容
    func_controller.init = function(){
        //渲染空表格
        func_controller.table_render([]);

        //显示字段渲染多选下拉框
        select_multi.render({
            elem: '#select_fields',data: []
        });

        //搜索字段渲染多选下拉框
        select_multi.render({
            elem: '#search_fields',data: []
        });

        //监听选择表下拉框onchange事件
        form.on('select(select_table)',function(data){
            let post_data = {'table_name':data.value};
            $.ajax({
                type: 'POST',
                url: facade.url('/' + module + '/' + controller + '/get_fields_by_table_name'),
                data: post_data,
                dataType: 'json',
                success: function (res) {
                    if( res.code == 1 ){
                        let selected_data = [];
                        layui.each(res.data,function(k,i){
                            selected_data.push(i['field_name']);
                        });
                        //重新渲染多选下拉框
                        select_multi.render({
                            elem: '#select_fields'
                            ,data: res.data
                            ,max: res.data.length
                            ,verify: 'required'
                            ,field: {idName:'field_name',titleName:'field_name'}
                            ,selected: selected_data
                            ,click_dd_after: function(){
                                let select_fields = $('input[name="select_fields"]').val();
                                let select_fields_arr = select_fields.split(',');
                                let table_render_data = [];
                                let res_data_map = facade.array_to_map(res.data, 'field_name');
                                layui.each(select_fields_arr,function (key,item) {
                                    if(typeof res_data_map[item] != "undefined"){
                                        table_render_data.push(res_data_map[item]);
                                    }
                                });
                                func_controller.table_render(table_render_data);
                            }
                        });
                        //重新渲染多选下拉框
                        select_multi.render({
                            elem: '#search_fields'
                            ,data: res.data
                            ,max: res.data.length
                            ,verify: 'required'
                            ,field: {idName:'field_name',titleName:'field_name'}
                            ,selected: selected_data
                            ,click_dd_after: function(){}
                        });
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
    }

    func_controller.init();

    func_controller.form_type_select_after = function(field_name, value){
        let input_html = '<select name="form_additional_input_'+field_name+'" id="form_additional_input_'+field_name+'">' +
            '<option value="">不限制</option>' +
            '<option value="email">Email</option>' +
            '<option value="required|phone">手机号码</option>' +
            '<option value="number">数字</option>' +
            '<option value="url">链接</option>' +
            '<option value="identity">身份证</option>' +
            '</select>';
        let set_value_html = '<input type="text" class="layui-input layui-input-inline" placeholder="value1=text1,value2=text2..." name="form_additional_set_value_input_'+field_name+'" id="form_additional_set_value_input_'+field_name+'" />';
        let select_page_html = '<select name="form_additional_select_page_table_'+field_name+'" id="form_additional_select_page_table_'+field_name+'">' +
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
        let time_html = '<select name="form_additional_time_'+field_name+'" id="form_additional_time_'+field_name+'">' +
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
        let city_html = '';
        let upload_html = '';
        let textarea_html = '';
        let editor_html = '';
        let type_arr = ['input','select_page','time','city','upload','textarea','editor'];
        let set_value_input_type = ['radio','checkbox','select'];
        if(set_value_input_type.indexOf(value) != -1){
            $('#form_additional_' + field_name).html(set_value_html);
        }else if(type_arr.indexOf(value) != -1){
            $('#form_additional_' + field_name).html(eval(value+'_html'));
        }
    }

    func_controller.curd_import = function(){
        $(document).on('click','#curd_import_btn', function(){
            let cache_count = facade.get_count(table.cache);
            let table_data_arr = table.cache[cache_count];
            let field_name
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
            let post_data = {'field_list':{},'global':{}};
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
            let common_model
                ,hide_pk
                ,create_number
                ,table_name
                ,fields_name
                ,close_page
                ,search_mode
                ,search_fields
                ,cell_min_width
            ;
            common_model = $('#common_model:checked').val();
            common_model = (typeof common_model == "undefined") ? 0 : common_model;
            hide_pk = $('#hide_pk:checked').val();
            hide_pk = (typeof hide_pk == "undefined") ? 0 : hide_pk;
            create_number = $('#create_number:checked').val();
            create_number = (typeof create_number == "undefined") ? 0 : create_number;
            table_name = $('#select_table').val();
            fields_name = $('input[name="select_fields"]').val();
            close_page = $('#close_page:checked').val();
            close_page = (typeof close_page == "undefined") ? 0 : close_page;
            search_mode = $('#search_mode').val();
            search_fields = $('input[name="search_fields"]').val();
            cell_min_width = $('#cell_min_width').val();
            post_data['global'] = {
                'common_model':common_model
                ,'hide_pk':hide_pk
                ,'create_number':create_number
                ,'table_name':table_name
                ,'fields_name':fields_name
                ,'close_page':close_page
                ,'search_mode':search_mode
                ,'search_fields':search_fields
                ,'cell_min_width':cell_min_width
            };
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
            let type_arr = ['input','select_page','time','city','upload','textarea','editor'];
            let set_value_input_type = ['radio','checkbox','select'];
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
    func_controller.curd_import();

    window.func_controller = func_controller;
});
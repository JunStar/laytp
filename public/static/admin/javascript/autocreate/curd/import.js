layui.use(['junAdmin'],function(){
    const
        func_controller = {}
        ,junAdmin = layui.junAdmin
        ,$ = junAdmin.$
    ;

    //渲染表格
    func_controller.table_render = function (data) {
        layui.table.render({
            elem: '.layui-hide-sm'
            ,cellMinWidth: 100
            ,text:{none:'请选择数据表和需要显示的字段'}
            ,limit: 10000
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
                    ,{field:'form_type', title:'表单元素', templet: "#form_type", align: 'center', width: 190}
                    ,{field:'form_additional', title:'附加设置', templet: "#form_additional", align: 'center', width: 220}
                    ,{field:'form_empty', title:'允许为空', templet: "#form_empty", align: 'center', width: 140}
                    //列表设置
                    ,{field:'table_width', title:'绝对列宽(数字或者百分比)', align: 'center', edit: 'text'}
                    ,{field:'table_min_width', title:'最小列宽(数字或者百分比)', align: 'center', edit: 'text', width: 120}
                    ,{field:'table_templet', title:'列类型', templet: '#table_templet', align: 'center', width: 140}
                    ,{field:'table_align', title:'排列方式', templet: '#table_align', align: 'center', width: 120}
                    ,{field:'table_additional', title:'附加选项', templet: "#table_additional", align: 'center', width: 400}
                ]
            ]
        });

        //监听表单元素下拉框onchange事件
        layui.form.on('select(form_type)',function(data){
            let field_name = $('#'+data.elem.id).data('field_name');
            let value = data.elem.value;
            func_controller.form_type_select_after(field_name, value);
            layui.form.render('select');
            return true;
        });
    }

    let select_table_name = '';
    //初始化方法，页面加载完毕立即执行的内容
    func_controller.init = function(){
        //渲染空表格
        func_controller.table_render([]);

        //显示字段渲染多选下拉框
        layui.select_multi.render({
            elem: '#select_fields',data: []
        });

        //搜索字段渲染多选下拉框
        layui.select_multi.render({
            elem: '#search_fields',data: []
        });

        //监听选择表下拉框onchange事件
        layui.form.on('select(select_table)',function(data){
            select_table_name = data.value;
            let post_data = {'table_name':data.value};
            $.ajax({
                type: 'POST',
                url: junAdmin.facade.url('/' + module + '/' + controller + '/get_fields_by_table_name'),
                data: post_data,
                dataType: 'json',
                success: function (res) {
                    if( res.code == 1 ){
                        let selected_data = [];
                        layui.each(res.data,function(k,i){
                            selected_data.push(i['field_name']);
                        });
                        //重新渲染多选下拉框
                        layui.select_multi.render({
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
                                let res_data_map = junAdmin.facade.array_to_map(res.data, 'field_name');
                                layui.each(select_fields_arr,function (key,item) {
                                    if(typeof res_data_map[item] != "undefined"){
                                        table_render_data.push(res_data_map[item]);
                                    }
                                });
                                func_controller.table_render(table_render_data);
                            }
                        });
                        //重新渲染多选下拉框
                        layui.select_multi.render({
                            elem: '#search_fields'
                            ,data: res.data
                            ,max: res.data.length
                            ,verify: 'required'
                            ,field: {idName:'field_name',titleName:'field_name'}
                            ,selected: selected_data
                            ,click_dd_after: function(){}
                        });
                    }else{
                        junAdmin.facade.error(res.msg);
                    }
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
    }

    func_controller.init();

    func_controller.form_type_select_after = function(field_name, value){
        let input_html =
            '<select name="form_additional_set_value_input_'+field_name+'" id="form_additional_set_value_input_'+field_name+'">' +
                '<option value="">不限制</option>' +
                '<option value="junAdmin_email">Email</option>' +
                '<option value="junAdmin_phone">手机号码</option>' +
                '<option value="junAdmin_number">数字</option>' +
                '<option value="junAdmin_url">链接</option>' +
                '<option value="junAdmin_identity">身份证</option>' +
            '</select>';
        let set_value_html = '<input type="text" class="layui-input layui-input-inline" placeholder="value1=text1,value2=text2,default=value..." name="form_additional_set_value_input_'+field_name+'" id="form_additional_set_value_input_'+field_name+'" />';
        let select_single_multi =
            '<select name="form_additional_select_single_multi_'+field_name+'" id="form_additional_select_single_multi_'+field_name+'">' +
                '<option value="single">单选</option>' +
                '<option value="multi">多选</option>' +
            '</select>';
        let select_html =  select_single_multi + '<input type="text" class="layui-input layui-input-inline" placeholder="最多可选个数，多选才有效，默认不限制" name="form_additional_select_max_'+field_name+'" id="form_additional_select_max_'+field_name+'" /><br/>' + set_value_html;
        let select_page_html = select_single_multi + '<input type="text" class="layui-input layui-input-inline" placeholder="最多可选个数，多选才有效，默认不限制" name="form_additional_select_max_'+field_name+'" id="form_additional_select_max_'+field_name+'" /><br/>' +
            '<select name="form_additional_select_page_table_'+field_name+'" id="form_additional_select_page_table_'+field_name+'" lay-filter="form_additional_select_page_table_'+field_name+'">' +
            '<option value="">搜索的表名</option>' +
            '</select>' +
            '<select name="form_additional_select_page_search_field_'+field_name+'" id="form_additional_select_page_search_field_'+field_name+'">' +
            '<option value="">搜索的字段</option>' +
            '</select>' +
            '<select name="form_additional_select_page_show_field_'+field_name+'" id="form_additional_select_page_show_field_'+field_name+'">' +
            '<option value="">显示的字段</option>' +
            '</select>';
        // let time_html = '<input class="layui-input layui-input-inline" placeholder="输入时间格式，比如，Y-m-d H:i:s" value="Y-m-d H:i:s" name="form_additional_time_\'+field_name+\'" id="form_additional_time_\'+field_name+\'" />';
        let time_html =
            '<select name="form_additional_set_value_input_'+field_name+'" id="form_additional_set_value_input_'+field_name+'">' +
                '<option value="datetime">年-月-日 时:分:秒</option>' +
                '<option value="month">年-月</option>' +
                '<option value="year">年</option>' +
                '<option value="Y-m-d">年-月-日</option>' +
                '<option value="time">时:分:秒</option>' +
            '</select>';
        let province_html =
            '<select name="form_additional_default_province_id_'+field_name+'" id="form_additional_default_province_id_'+field_name+'">' +
                '<option value="">请选择默认省份</option>' +
            '</select>' +
            '<select name="form_additional_linkage_city_field_'+field_name+'" id="form_additional_linkage_city_field_'+field_name+'">' +
                '<option value="">请选择联动城市字段</option>' +
            '</select>';
        let city_html =
            '<select name="form_additional_set_value_input_'+field_name+'" id="form_additional_set_value_input_'+field_name+'">' +
                '<option value="">请选择联动区县字段</option>' +
            '</select>';
        let county_html = '';
        let upload_html =
            '<select name="form_additional_upload_single_multi_'+field_name+'" id="form_additional_upload_single_multi_'+field_name+'" lay-filter="form_additional_upload_single_multi_'+field_name+'">' +
                '<option value="single">单个文件</option>' +
                '<option value="multi">多个文件</option>' +
            '</select>' +
            '<select name="form_additional_upload_accept_'+field_name+'" id="form_additional_upload_accept_'+field_name+'" lay-filter="form_additional_upload_accept_'+field_name+'">' +
                '<option value="images">图片</option>' +
                '<option value="video">视频</option>' +
                '<option value="audio">音频</option>' +
                '<option value="file">所有文件类型</option>' +
            '</select>';
        let textarea_html = '';
        let editor_html =
            '<select name="form_additional_set_value_input_'+field_name+'" id="form_additional_set_value_input_'+field_name+'" lay-filter="form_additional_set_value_input_'+field_name+'">' +
                '<option value="ueditor">UEditor</option>' +
            '</select>';
        let type_arr = ['input','select','select_page','time','province','city','county','upload','textarea','editor'];
        let set_value_input_type = ['radio','checkbox'];
        if(set_value_input_type.indexOf(value) != -1){
            $('#form_additional_' + field_name).html(set_value_html);
        }else if(type_arr.indexOf(value) != -1){
            $('#form_additional_' + field_name).html(eval(value+'_html'));
            switch (value) {
                case 'select_page':
                    $.ajax({
                        type: 'POST',
                        url: junAdmin.facade.url('admin/autocreate.curd/get_table_list'),
                        data: {},
                        dataType: 'json',
                        success: function (res) {
                            func_controller.set_select_page_table_name(field_name, res.data);
                            layui.form.on('select(form_additional_select_page_table_'+field_name+')',function(data){
                                let table_name = data.value;
                                $.ajax({
                                    type: 'POST',
                                    url: junAdmin.facade.url('admin/autocreate.curd/get_fields_by_table_name'),
                                    data: {table_name:table_name},
                                    dataType: 'json',
                                    success: function (res) {
                                        func_controller.set_select_page_search_field_name(field_name, res.data);
                                        func_controller.set_select_page_show_field_name(field_name, res.data);

                                        layui.form.render('select');
                                    }
                                });
                            });

                            layui.form.render('select');
                        },
                        error: function (xhr) {
                            if( xhr.status == '500' ){
                                junAdmin.facade.error('本地网络问题或者服务器错误');
                            }else if( xhr.status == '404' ){
                                junAdmin.facade.error('请求地址不存在');
                            }
                        }
                    });
                    break;
                case 'province':
                    $.ajax({
                        type: 'POST',
                        url: junAdmin.facade.url('admin/ajax/area'),
                        data: {table_name:select_table_name},
                        dataType: 'json',
                        success: function (res) {
                            func_controller.set_default_province_list(field_name, res.data);

                            layui.form.render('select');
                        }
                    });
                    $.ajax({
                        type: 'POST',
                        url: junAdmin.facade.url('admin/autocreate.curd/get_fields_by_table_name'),
                        data: {table_name:select_table_name},
                        dataType: 'json',
                        success: function (res) {
                            func_controller.set_select_linkage_city_field(field_name, res.data);

                            layui.form.render('select');
                        }
                    });
                    break;
                case 'city':
                    $.ajax({
                        type: 'POST',
                        url: junAdmin.facade.url('admin/autocreate.curd/get_fields_by_table_name'),
                        data: {table_name:select_table_name},
                        dataType: 'json',
                        success: function (res) {
                            func_controller.set_county_city_id_field(field_name, res.data);

                            layui.form.render('select');
                        }
                    });
                    break;
            }
        }
    }

    //设置搜索下拉框待搜索表名
    func_controller.set_select_page_table_name = function(field_name, data, selected){
        let option_html;
        for(key in data){
            option_html = '<option value="'+data[key]['TABLE_NAME']+'">'+data[key]['TABLE_NAME']+'</option>';
            $('#form_additional_select_page_table_'+field_name).append(option_html);
        }
    }

    //设置搜索下拉框待搜索的字段
    func_controller.set_select_page_search_field_name = function(field_name, data, selected){
        $('#form_additional_select_page_search_field_'+field_name).empty();
        let option_html;
        for(key in data){
            option_html = '<option value="'+data[key]['field_name']+'">'+data[key]['field_name']+'</option>';
            $('#form_additional_select_page_search_field_'+field_name).append(option_html);
        }
    }

    //设置搜索下拉框显示的字段
    func_controller.set_select_page_show_field_name = function(field_name, data, selected){
        $('#form_additional_select_page_show_field_'+field_name).empty();
        let option_html;
        for(key in data){
            option_html = '<option value="'+data[key]['field_name']+'">'+data[key]['field_name']+'</option>';
            $('#form_additional_select_page_show_field_'+field_name).append(option_html);
        }
    }

    //设置默认省份待选列表
    func_controller.set_default_province_list = function(field_name, data){
        $('#form_additional_default_province_id_'+field_name).empty();
        let option_1 = '<option value="">请选择默认省份</option>';
        $('#form_additional_default_province_id_'+field_name).append(option_1);
        let option_html;
        let key;
        for(key in data){
            option_html = '<option value="'+data[key]['id']+'">'+data[key]['name']+'</option>';
            $('#form_additional_default_province_id_'+field_name).append(option_html);
        }
    }

    //设置省份联动的城市字段
    func_controller.set_select_linkage_city_field = function(field_name, data){
        $('#form_additional_linkage_city_field_'+field_name).empty();
        let option_1 = '<option value="">请选择联动城市字段</option>';
        $('#form_additional_linkage_city_field_'+field_name).append(option_1);
        let option_html;
        let key;
        for(key in data){
            option_html = '<option value="'+data[key]['field_name']+'">'+data[key]['field_name']+'</option>';
            $('#form_additional_linkage_city_field_'+field_name).append(option_html);
        }
    }

    //设置城市联动的区县字段
    func_controller.set_county_city_id_field = function(field_name, data){
        $('#form_additional_set_value_input_'+field_name).empty();
        let option_1 = '<option value="">请选择联动区县字段</option>';
        $('#form_additional_set_value_input_'+field_name).append(option_1);
        let option_html;
        let key;
        for(key in data){
            option_html = '<option value="'+data[key]['field_name']+'">'+data[key]['field_name']+'</option>';
            $('#form_additional_set_value_input_'+field_name).append(option_html);
        }
    }

    func_controller.curd_import = function(){
        $(document).on('click','#curd_import_btn', function(){
            let cache_count = junAdmin.facade.get_count(layui.table.cache);
            let table_data_arr = layui.table.cache[cache_count];
            let field_name
                ,field_comment
                ,form_type
                ,form_additional
                ,form_empty
                ,table_width
                ,table_min_width
                ,table_templet
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
                form_empty = $('#form_empty_'+field_name+':checked').val();
                form_empty = (typeof form_empty == "undefined") ? 0 : form_empty;
                table_width = table_data_arr[key]['table_width'];
                table_min_width = table_data_arr[key]['table_min_width'];
                table_templet = $('#table_templet_'+field_name).val();
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
                    ,'form_empty':form_empty
                    ,'table_width':table_width
                    ,'table_min_width':table_min_width
                    ,'table_templet':table_templet
                    ,'table_align':table_align
                    ,'table_additional_unresize':table_additional_unresize
                    ,'table_additional_sort':table_additional_sort
                    ,'table_additional_edit':table_additional_edit
                };
            }
            let common_model
                ,hide_pk
                ,hide_del
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
            hide_del = $('#hide_del:checked').val();
            hide_del = (typeof hide_del == "undefined") ? 0 : hide_del;
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
                ,'hide_del':hide_del
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
                        junAdmin.facade.success(res.msg);
                        parent.func_controller.table_render();
                        // parent.layer.closeAll();
                    }else{
                        junAdmin.facade.error(res.msg);
                    }
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

        function get_form_additional_val(field_name,form_type){
            let no_form_additionnal_arr = ['textarea'];
            let type_arr = ['select','select_page','province','upload','editor'];
            let set_value_input_type = ['input','time','radio','city','checkbox','editor'];
            if(set_value_input_type.indexOf(form_type) != -1){
                return $('#form_additional_set_value_input_' + field_name).val();
            }else if(type_arr.indexOf(form_type) != -1){
                switch (form_type) {
                    case 'select':
                        return {
                            'single_multi' : $('#form_additional_select_single_multi_' + field_name).val(),
                            'max' : $('#form_additional_select_max_' + field_name).val(),
                            'values' : $('#form_additional_set_value_input_' + field_name).val()
                        };
                        break;
                    case 'select_page':
                        return {
                            'single_multi' : $('#form_additional_select_single_multi_' + field_name).val(),
                            'max' : $('#form_additional_select_max_' + field_name).val(),
                            'table_name' : $('#form_additional_select_page_table_' + field_name).val(),
                            'search_field_name' : $('#form_additional_select_page_search_field_' + field_name).val(),
                            'show_field_name' : $('#form_additional_select_page_show_field_' + field_name).val()
                        };
                        break;
                    case 'upload':
                        return {
                            'single_multi' : $('#form_additional_upload_single_multi_' + field_name).val(),
                            'accept' : $('#form_additional_upload_accept_' + field_name).val()
                        };
                        break;
                    case 'province':
                        return {
                            'default_province_id' : $('#form_additional_default_province_id_' + field_name).val(),
                            'change_linkage_id' : $('#form_additional_linkage_city_field_' + field_name).val()
                        };
                        break;
                    default:
                        return "";
                        break;
                }
            }else if(no_form_additionnal_arr.indexOf(form_type) != -1){
                return "";
            }
        }
    }
    func_controller.curd_import();

    window.func_controller = func_controller;
});

layui.use(['layTp'],function(){
    const
        func_controller = {}
        ,layTp = layui.layTp
        ,$ = layTp.$
    ;

    //渲染表格
    func_controller.table_render = function (data) {
        layui.table.render({
            elem: '.laytp-table'
            ,cellMinWidth: 100
            ,text:{none:'请选择数据表和需要显示的字段'}
            ,limit: 10000
            ,data:data
            ,cols: [
                [
                    {title:'数据库字段查看', align: 'center', colspan: 3}
                    ,{title:'表单设置,影响添加编辑表单', align: 'center', colspan: 3}
                    ,{title:'显示设置', align: 'center',rowspan:2, templet: "#field_show", align: 'center', width:350}
                ]
                ,[
                    //数据库字段设置
                    {title:'序号', type:'numbers', align: 'center'}
                    ,{field:'field_name', title:'字段名称', align: 'center', edit: 'text', width:120}
                    ,{field:'field_comment', title:'字段注释', align: 'center', edit: 'text', width:150}
                    //表单设置
                    ,{field:'form_type', title:'表单元素', templet: "#form_type", align: 'center', width:180}
                    ,{field:'form_additional', title:'附加设置', templet: "#form_additional", align: 'center'}
                    ,{field:'form_empty', title:'允许为空', templet: "#form_empty", align: 'center', width:150}
                ]
            ]
        });

        //监听表单元素下拉框onchange事件
        layui.form.on('select(form_type)',function(data){
            let field_name = $('#'+data.elem.id).data('field_name');
            let form_type = data.elem.value;
            func_controller.form_type_select_after(field_name, form_type, '');
            layui.form.render('select');
            return true;
        });
    }

    let select_table_name = '';
    let fields_list = '';
    let select_table_fields = '';
    let relation_model_num = 0;
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

        //监听[选择表]下拉框onchange事件
        layui.form.on('select(select_table)',function(data){
            //全局模型，隐藏主键列等渲染成非选中状态
            $('#common_model').attr('checked',false);
            $('#hide_pk').attr('checked',false);
            $('#create_number').attr('checked',false);
            $('#close_page').attr('checked',false);
            $('#hide_del').attr('checked',false);

            //关联模型设为非选中状态
            $('#relation_model').attr('checked',false);

            //将关联属性设置置空
            let add_item_td = $("#relation_list tr:last").prop("outerHTML");
            $("#relation_list").html(add_item_td);

            layui.form.render('checkbox');

            //隐藏关联模型设置fieldset
            $("#relation_model_fieldset").hide();

            let ajax_data = {'table_name':data.value};
            if(!data.value){
                return true;
            }
            select_table_name = data.value;

            $.ajax({
                type: 'GET',
                url: layTp.facade.url('/' + module + '/' + controller + '/get_fields_by_table_name'),
                data: {table_name:select_table_name},
                dataType: 'json',
                success: function(res){
                    select_table_fields = res.data;
                }
            });

            $.ajax({
                type: 'GET',
                url: layTp.facade.url('/' + module + '/' + controller + '/get_curd_info'),
                data: ajax_data,
                dataType: 'json',
                success: function (res) {
                    fields_list = res.data.fields_list;
                    if( res.code == 1 ){
                        let selected_data = [];
                        layui.each(res.data.selected_list,function(k,i){
                            selected_data.push(i['field_name']);
                        });
                        //重新渲染多选下拉框，显示字段
                        layui.select_multi.render({
                            elem: '#select_fields'
                            ,data: res.data.all_fields
                            ,max: res.data.all_fields.length
                            ,verify: 'required'
                            ,field: {idName:'field_name',titleName:'field_name'}
                            ,selected: selected_data
                            ,click_dd_after: function(){
                                let select_fields = $('input[name="select_fields"]').val();
                                let select_fields_arr = select_fields.split(',');
                                let table_render_data = [];
                                let res_data_map = layTp.facade.array_to_map(res.data.all_fields, 'field_name');
                                layui.each(select_fields_arr,function (key,item) {
                                    if(typeof res_data_map[item] != "undefined"){
                                        table_render_data.push(res_data_map[item]);
                                    }
                                });
                                func_controller.table_render(table_render_data);

                                layui.each(select_fields_arr,function (key,item) {
                                    if(typeof res_data_map[item] != "undefined"){
                                        let field_name = res_data_map[item].field_name;
                                        let form_type = res_data_map[item].form_type;
                                        let form_additional = res_data_map[item].form_additional;
                                        func_controller.form_type_select_after(field_name, form_type, form_additional);
                                    }
                                });
                                layui.form.render('select');
                            }
                        });
                        //重新渲染多选下拉框，搜索字段
                        layui.select_multi.render({
                            elem: '#search_fields'
                            ,data: res.data.all_fields
                            ,max: res.data.all_fields.length
                            ,verify: 'required'
                            ,field: {idName:'field_name',titleName:'field_name'}
                            ,selected: selected_data
                            ,click_dd_after: function(){}
                        });
                        //关联模型相关渲染
                        let relation_model = res.data.relation_model;
                        if(relation_model && relation_model.length>0){
                            $('#relation_model').attr('checked',true);

                            $('#relation_model_fieldset').show();
                            let show_fields_relation = [];
                            for(key in relation_model){
                                layui.laytpl($('#relation_model_item').html()).render({
                                    selected_table:relation_model[key].table_name,
                                    select_table_fields: select_table_fields,
                                    select_relation_primary_key: fields_list[relation_model[key].table_name],
                                    selected_relation_primary_key: relation_model[key].primary_key,
                                    selected_relation_foreign_key: relation_model[key].foreign_key,
                                    relation_model_num:relation_model_num,
                                    relation_function_name:(typeof relation_model[key].relation_function_name != "undefined") ? relation_model[key].relation_function_name : ""
                                }, function(string){
                                    $('#relation_list').prepend(string);
                                });
                                show_fields_relation = [];
                                for(k in fields_list[relation_model[key].table_name]){
                                    show_fields_relation.push({'field_name':fields_list[relation_model[key].table_name][k]});
                                }
                                //关联模型显示的字段
                                layui.select_multi.render({
                                    elem: '#relation_show_field_'+relation_model_num
                                    ,data: show_fields_relation
                                    ,max: show_fields_relation.length
                                    ,verify: 'required'
                                    ,field: {idName:'field_name',titleName:'field_name'}
                                    ,selected: relation_model[key].show_field.split(',')
                                    ,click_dd_after: function(){}
                                });

                                relation_model_num += 1;
                            }
                            layui.form.render('select');
                        }
                        //全局模型，隐藏主键列等渲染是否选中
                        let global = res.data.global;
                        if(typeof global != "undefined"){
                            if(global.common_model == 1){
                                $('#common_model').attr('checked',true);
                            }
                            if(global.hide_pk == 1){
                                $('#hide_pk').attr('checked',true);
                            }
                            if(global.create_number == 1){
                                $('#create_number').attr('checked',true);
                            }
                            if(global.close_page == 1){
                                $('#close_page').attr('checked',true);
                            }
                            if(global.hide_del == 1){
                                $('#hide_del').attr('checked',true);
                            }
                        }
                        layui.form.render('checkbox');
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
        });

        //关联模型复选框绑定点击事件
        layui.form.on('checkbox(relation_model)',function(data){
            if(!data.elem.checked){
                $('#relation_model_fieldset').hide();
            }else{
                $('#relation_model_fieldset').show();
            }
        });

        //追加关联模型
        $(document).on('click','.add_relation_model',function(){
            if(!select_table_name){
                layTp.facade.error('请选择表');
                return false;
            }
            let click_obj = $(this);
            layui.laytpl($('#relation_model_item').html()).render({
                select_table_fields: select_table_fields,
                relation_model_num:relation_model_num,
                relation_function_name:""
            }, function(string){
                click_obj.parent().parent().before(string);
                layui.form.render('select');
            });
            relation_model_num += 1;
        });

        //删除关联模型的一行
        $(document).on('click','.del_relation_model',function(){
            $(this).parent().parent().remove();
        });

        //选择关联数据表下拉框的onchange事件
        let select_relation_table = '';
        layui.form.on('select(select_relation_table)',function(data){
            let num = $(data.elem).attr('num');

            select_relation_table = data.elem.value;

            //显示的字段
            $.ajax({
                type: 'GET',
                url: layTp.facade.url('admin/autocreate.curd/get_fields_by_table_name'),
                data: {table_name:select_relation_table},
                dataType: 'json',
                success: function (res) {
                    //主键
                    let html = '<select name="select_relation_primary_key[]" lay-filter="select_relation_primary_key">';
                    let selected_relation_data = [];
                    for(key in res.data){
                        selected_relation_data.push({'field_name':res.data[key]});
                        html += '<option value="'+res.data[key]+'">'+res.data[key]+'</option>';
                    }
                    html += '</select>';
                    data.othis.parent().parent().find('td:eq(3)').html(html);
                    layui.form.render('select');

                    //关联模型显示的字段
                    layui.select_multi.render({
                        elem: '#relation_show_field_'+num
                        ,data: selected_relation_data
                        ,max: selected_relation_data.length
                        ,verify: 'required'
                        ,field: {idName:'field_name',titleName:'field_name'}
                        ,click_dd_after: function(){}
                    });
                }
            });
        });
    }

    func_controller.init();

    func_controller.form_type_select_after = function(field_name, value, form_additional){
        let input_html =
            '<select name="form_additional_set_value_input_'+field_name+'" id="form_additional_set_value_input_'+field_name+'">' +
                '<option value="">不限制</option>' +
                '<option value="layTp_email" ' + ((form_additional == "layTp_email") ? 'selected="selected"' : '') + '>Email</option>' +
                '<option value="layTp_phone" ' + ((form_additional == "layTp_phone") ? 'selected="selected"' : '') + '>手机号码</option>' +
                '<option value="layTp_number" ' + ((form_additional == "layTp_number") ? 'selected="selected"' : '') + '>数字</option>' +
                '<option value="layTp_url" ' + ((form_additional == "layTp_url") ? 'selected="selected"' : '') + '>链接</option>' +
                '<option value="layTp_identity" ' + ((form_additional == "layTp_identity") ? 'selected="selected"' : '') + '>身份证</option>' +
            '</select>';
        let password_html = '';
        let set_value_html = '<input type="text" class="layui-input layui-input-inline" value="'+((typeof form_additional == 'object')?form_additional['values']:form_additional)+'" placeholder="value1=text1,value2=text2,default=value..." name="form_additional_set_value_input_'+field_name+'" id="form_additional_set_value_input_'+field_name+'" />';
        let select_single_multi =
            '<select name="form_additional_select_single_multi_'+field_name+'" id="form_additional_select_single_multi_'+field_name+'">' +
                '<option value="single" ' + (((typeof form_additional == 'object') && (form_additional['single_multi'] == "single")) ? 'selected="selected"' : '') + '>单选</option>' +
                '<option value="multi" ' + (((typeof form_additional == 'object') && (form_additional['single_multi'] == "multi")) ? 'selected="selected"' : '') + '>多选</option>' +
            '</select>';
        let select_html =  select_single_multi + '<input type="text" class="layui-input layui-input-inline" value="'+((typeof form_additional == 'object')?form_additional['max']:form_additional)+'" placeholder="最多可选个数，多选才有效，默认不限制" name="form_additional_select_max_'+field_name+'" id="form_additional_select_max_'+field_name+'" /><br/>' + set_value_html;
        let select_page_html = select_single_multi + '<input type="text" class="layui-input layui-input-inline" value="'+((typeof form_additional == 'object')?form_additional['max']:form_additional)+'" placeholder="最多可选个数，多选才有效，默认不限制" name="form_additional_select_max_'+field_name+'" id="form_additional_select_max_'+field_name+'" /><br/>' +
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
        let select_relation_html = '<input type="text" class="layui-input layui-input-inline" value="'+((typeof form_additional == 'object')?form_additional['group_name']:form_additional)+'" placeholder="分组名，例：地区设置" name="form_additional_group_name_'+field_name+'" id="form_additional_group_name_'+field_name+'" /><br/>' +
            '<select name="form_additional_select_relation_table_'+field_name+'" id="form_additional_select_relation_table_'+field_name+'" lay-filter="form_additional_select_relation_table_'+field_name+'">' +
            '<option value="">搜索的表名,例:lt_area</option>' +
            '</select>' +
            '<select name="form_additional_select_relation_left_field_'+field_name+'" id="form_additional_select_relation_left_field_'+field_name+'">' +
            '<option value="">左关联字段,不选表示第一个下拉框</option>' +
            '</select>' +
            '<select name="form_additional_select_relation_right_field_'+field_name+'" id="form_additional_select_relation_right_field_'+field_name+'">' +
            '<option value="">右联动的字段,不选表示最后一个下拉框</option>' +
            '</select>';
        let time_html =
            '<select name="form_additional_set_value_input_'+field_name+'" id="form_additional_set_value_input_'+field_name+'">' +
                '<option value="datetime" ' + ((form_additional == "datetime") ? 'selected="selected"' : '') + '>年-月-日 时:分:秒</option>' +
                '<option value="month" ' + ((form_additional == "month") ? 'selected="selected"' : '') + '>年-月</option>' +
                '<option value="year" ' + ((form_additional == "year") ? 'selected="selected"' : '') + '>年</option>' +
                '<option value="date" ' + ((form_additional == "date") ? 'selected="selected"' : '') + '>年-月-日</option>' +
                '<option value="time" ' + ((form_additional == "time") ? 'selected="selected"' : '') + '>时:分:秒</option>' +
            '</select>';
        let upload_html =
            '<select name="form_additional_upload_single_multi_'+field_name+'" id="form_additional_upload_single_multi_'+field_name+'" lay-filter="form_additional_upload_single_multi_'+field_name+'">' +
                '<option value="single" ' + (((typeof form_additional == 'object') && form_additional['single_multi'] == "single") ? 'selected="selected"' : '') + '>单个文件</option>' +
                '<option value="multi" ' + (((typeof form_additional == 'object') && form_additional['single_multi'] == "multi") ? 'selected="selected"' : '') + '>多个文件</option>' +
            '</select>' +
            '<select name="form_additional_upload_accept_'+field_name+'" id="form_additional_upload_accept_'+field_name+'" lay-filter="form_additional_upload_accept_'+field_name+'">' +
                '<option value="images" ' + (((typeof form_additional == 'object') && form_additional['accept'] == "images") ? 'selected="selected"' : '') + '>图片</option>' +
                '<option value="video" ' + (((typeof form_additional == 'object') && form_additional['accept'] == "video") ? 'selected="selected"' : '') + '>视频</option>' +
                '<option value="audio" ' + (((typeof form_additional == 'object') && form_additional['accept'] == "audio") ? 'selected="selected"' : '') + '>音频</option>' +
                '<option value="file" ' + (((typeof form_additional == 'object') && form_additional['accept'] == "file") ? 'selected="selected"' : '') + '>所有文件类型</option>' +
            '</select>';
        let textarea_html = '';
        let editor_html =
            '<select name="form_additional_set_value_input_'+field_name+'" id="form_additional_set_value_input_'+field_name+'" lay-filter="form_additional_set_value_input_'+field_name+'">' +
                '<option value="ueditor" ' + ((form_additional == "ueditor") ? 'selected="selected"' : '') + '>UEditor</option>' +
            '</select>';
        let type_arr = ['input','password','select','select_page','select_relation','time','province','city','county','upload','textarea','editor'];
        let set_value_input_type = ['radio','checkbox'];
        if(set_value_input_type.indexOf(value) != -1){
            $('#form_additional_' + field_name).html(set_value_html);
        }else if(type_arr.indexOf(value) != -1){
            $('#form_additional_' + field_name).html(eval(value+'_html'));
            switch (value) {
                case 'select_page':
                    let selected_table = (typeof form_additional == 'object') ? form_additional['table_name'] : "";
                    func_controller.set_select_page_table_name(field_name, table_list, selected_table);
                    layui.form.on('select(form_additional_select_page_table_'+field_name+')',function(data){
                        let table_name = data.value;
                        if(!table_name){
                            return true;
                        }
                        $.ajax({
                            type: 'GET',
                            url: layTp.facade.url('admin/autocreate.curd/get_fields_by_table_name'),
                            data: {table_name:table_name},
                            dataType: 'json',
                            success: function (res) {
                                func_controller.set_select_page_search_field_name(field_name, res.data);
                                func_controller.set_select_page_show_field_name(field_name, res.data);

                                layui.form.render('select');
                            }
                        });
                    });

                    let search_field_name = (typeof form_additional == 'object') ? form_additional['search_field_name'] : "";
                    let show_field_name = (typeof form_additional == 'object') ? form_additional['show_field_name'] : "";
                    if(selected_table != ""){
                        for(key in fields_list){
                            if(key == selected_table){
                                let temp_fields_list = fields_list[key];
                                func_controller.set_select_page_search_field_name(field_name, temp_fields_list,search_field_name);
                                func_controller.set_select_page_show_field_name(field_name, temp_fields_list,show_field_name);
                            }
                        }
                    }
                    break;
                case 'select_relation':
                    func_controller.set_select_relation_table_name(field_name, table_list, form_additional['table_name']);
                    let left_field = (typeof form_additional == 'object') ? form_additional['left_field'] : "";
                    let right_field = (typeof form_additional == 'object') ? form_additional['right_field'] : "";
                    for(key in fields_list){
                        if(key == select_table_name){
                            let temp_fields_list = fields_list[key];
                            func_controller.set_select_relation_left_field_name(field_name, temp_fields_list, left_field);
                            func_controller.set_select_relation_right_field_name(field_name, temp_fields_list, right_field);
                        }
                    }
                    break;
            }
        }
    }

    //设置搜索下拉框待搜索表名
    func_controller.set_select_page_table_name = function(field_name, data, selected_value){
        let option_html;
        for(key in data){
            option_html = '<option value="'+data[key]['TABLE_NAME']+'"' + ((selected_value == data[key]['TABLE_NAME']) ? "selected='selected'" : "")+'>'+data[key]['TABLE_NAME']+'</option>';
            $('#form_additional_select_page_table_'+field_name).append(option_html);
        }
    }

    //设置搜索下拉框待搜索的字段
    func_controller.set_select_page_search_field_name = function(field_name, data, selected_value){
        $('#form_additional_select_page_search_field_'+field_name).empty();
        let option_html;
        for(key in data){
            option_html = '<option value="'+data[key]+'"' + ((selected_value == data[key]) ? "selected='selected'" : "")+'>'+data[key]+'</option>';
            $('#form_additional_select_page_search_field_'+field_name).append(option_html);
        }
    }

    //设置搜索下拉框显示的字段
    func_controller.set_select_page_show_field_name = function(field_name, data, selected_value){
        $('#form_additional_select_page_show_field_'+field_name).empty();
        let option_html;
        for(key in data){
            option_html = '<option value="'+data[key]+'"' + ((selected_value == data[key]) ? "selected='selected'" : "")+'>'+data[key]+'</option>';
            $('#form_additional_select_page_show_field_'+field_name).append(option_html);
        }
    }

    //设置联动下拉框待搜索的表名
    func_controller.set_select_relation_table_name = function(field_name, data, selected_value){
        let option_html;
        for(key in data){
            option_html = '<option value="'+data[key]['TABLE_NAME']+'" ' + ((selected_value == data[key]['TABLE_NAME']) ? "selected='selected'" : "")+'>'+data[key]['TABLE_NAME']+'</option>';
            $('#form_additional_select_relation_table_'+field_name).append(option_html);
        }
    }

    //设置联动下拉框左关联字段
    func_controller.set_select_relation_left_field_name = function(field_name, data, selected_value){
        let option_html;
        for(key in data){
            option_html = '<option value="'+data[key]+'" ' + ((selected_value == data[key]) ? "selected='selected'" : "")+'>'+data[key]+'</option>';
            $('#form_additional_select_relation_left_field_'+field_name).append(option_html);
        }
    }

    //设置联动下拉框右关联字段
    func_controller.set_select_relation_right_field_name = function(field_name, data, selected_value){
        let option_html;
        for(key in data){
            option_html = '<option value="'+data[key]+'" ' + ((selected_value == data[key]) ? "selected='selected'" : "")+'>'+data[key]+'</option>';
            $('#form_additional_select_relation_right_field_'+field_name).append(option_html);
        }
    }

    func_controller.curd_import = function(){
        $(document).on('click','#curd_import_btn', function(){
            let cache_count = layTp.facade.get_count(layui.table.cache);
            let table_data_arr = layui.table.cache[cache_count];
            let field_name
                ,field_comment
                ,form_type
                ,form_additional
                ,form_empty
                ,field_show_index
                ,field_show_add
                ,field_show_edit
            ;
            let post_data = {'field_list':{},'global':{}};
            for(key in table_data_arr){
                field_name = table_data_arr[key]['field_name'];
                field_comment = table_data_arr[key]['field_comment'];
                form_type = $('#form_type_'+field_name).val();
                form_additional = get_form_additional_val(field_name,form_type);
                form_empty = $('#form_empty_'+field_name+':checked').val();
                form_empty = (typeof form_empty == "undefined") ? 0 : form_empty;
                field_show_index = $('#field_show_index_'+field_name+':checked').val();
                field_show_index = (typeof field_show_index == "undefined") ? 0 : field_show_index;
                field_show_add = $('#field_show_add_'+field_name+':checked').val();
                field_show_add = (typeof field_show_add == "undefined") ? 0 : field_show_add;
                field_show_edit = $('#field_show_edit_'+field_name+':checked').val();
                field_show_edit = (typeof field_show_edit == "undefined") ? 0 : field_show_edit;

                post_data['field_list'][key] = {
                    'field_name':field_name
                    ,'field_comment':field_comment
                    ,'form_type':form_type
                    ,'form_additional':form_additional
                    ,'form_empty':form_empty
                    ,'field_show_index':field_show_index
                    ,'field_show_add':field_show_add
                    ,'field_show_edit':field_show_edit
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
                ,tabs_field
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
            tabs_field = $('#tabs_field').val();
            post_data['global'] = {
                'common_model':common_model
                ,'hide_pk':hide_pk
                ,'hide_del':hide_del
                ,'create_number':create_number
                ,'table_name':table_name
                ,'fields_name':fields_name
                ,'close_page':close_page
                ,'tabs_field':tabs_field
                ,'search_mode':search_mode
                ,'search_fields':search_fields
                ,'cell_min_width':cell_min_width
            };

            //关联模型需要提交的数据
            let temp_relation_model = {};
            post_data['relation_model'] = [];
            layui.each($('#relation_list tr'),function(i,item){
                if(i < $('#relation_list tr').length - 1){
                    temp_relation_model = {
                        'table_name' : $(item).find('select:eq(0)').val(),
                        'relation_way' : $(item).find('select:eq(1)').val(),
                        'foreign_key' : $(item).find('select:eq(2)').val(),
                        'primary_key' : $(item).find('select:eq(3)').val(),
                        'show_field' : $('input[name="'+$(item).find('td:eq(4)').attr('id')+'"]').val(),
                        'relation_function_name' : $(item).find('input:eq(5)').val(),
                    }
                    post_data['relation_model'].push(temp_relation_model);
                }
            });

            $.ajax({
                type: 'POST',
                url: window.location.href,
                data: post_data,
                dataType: 'json',
                success: function (res) {
                    if( res.code == 1 ){
                        layTp.facade.success(res.msg);
                        if(parent.window.location.href.indexOf('import.html') == -1){
                            parent.func_controller.table_render();
                            parent.layui.layer.closeAll();
                        }
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
        });

        function get_form_additional_val(field_name,form_type){
            let no_form_additionnal_arr = ['textarea'];
            let type_arr = ['select','select_page','select_relation','upload','editor'];
            let set_value_input_type = ['input','time','radio','county','checkbox','editor'];
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
                    case 'select_relation':
                        return {
                            'group_name' : $('#form_additional_group_name_' + field_name).val(),
                            'table_name' : $('#form_additional_select_relation_table_' + field_name).val(),
                            'left_field' : $('#form_additional_select_relation_left_field_' + field_name).val(),
                            'right_field' : $('#form_additional_select_relation_right_field_' + field_name).val()
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

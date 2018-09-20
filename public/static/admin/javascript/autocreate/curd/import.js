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
                    ,{field:'table_min_width', title:'最小列宽(数字或者百分比)', align: 'center', edit: 'text'}
                    ,{field:'table_type', title:'列类型', templet: '#table_type', align: 'center', width: 120}
                    ,{field:'table_align', title:'排列方式', templet: '#table_align', align: 'center', width: 120}
                    ,{field:'table_additional', title:'附加选项', templet: "#table_additional", align: 'center', width: 400}
                ]
            ]
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

    //初始化方法，页面加载完毕立即执行的内容
    func_controller.init = function(){
        //渲染空表格
        func_controller.table_render([]);

        //渲染多选下拉框
        select_multi.render({
            elem: '#select_fields',data: []
        });

        //渲染多选下拉框
        select_multi.render({
            elem: '#search_fields',data: []
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

    window.func_controller = func_controller;
});
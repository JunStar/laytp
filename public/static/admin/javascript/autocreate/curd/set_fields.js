(function func_controller() {
    var func_controller = {};

    func_controller.data = [];

    func_controller.table_render = function (data) {
        table.render({
            elem: '.layui-hide-sm'
            ,url:''
            ,cellMinWidth: 100
            ,cols: [
                [
                    {title:'数据库字段设置', width:100, align: 'center', colspan: 9}
                    ,{title:'表单设置,影响添加编辑表单', align: 'center', colspan: 2}
                    ,{title:'列表设置', align: 'center', colspan: 5}
                ]
                ,[
                    //数据库字段设置
                    {field:'operation', title:'操作', toolbar: '#operation', fixed: 'left', align: 'center', width: 200}
                    ,{title:'序号', type:'numbers', align: 'center'}
                    ,{field:'field_name', title:'字段名称', width:100, align: 'center', edit: 'text'}
                    ,{field:'field_comment', title:'字段注释(同时用于显示在表头和表单中的文字)', width:100, align: 'center', edit: 'text'}
                    ,{field:'field_type', title:'字段类型', templet: '#field_type', align: 'center', width: 190}
                    ,{field:'field_length', title:'长度', align: 'center', edit: 'text'}
                    ,{field:'field_decimal', title:'小数点', align: 'center', edit: 'text'}
                    ,{field:'field_additional', title:'附加选项', templet: "#field_additional", align: 'center', width: 370}
                    ,{field:'field_val', title:'值', toolbar: '#field_val', align: 'center', edit: 'text', width: 160}
                    //表单设置
                    ,{field:'form_type', title:'表单元素', templet: "#form_type", align: 'center', width: 280}
                    ,{field:'form_required', title:'附加选项', templet: "#form_additional", align: 'center', width: 120}
                    //列表设置
                    ,{field:'table_width', title:'绝对列宽', align: 'center', edit: 'text'}
                    ,{field:'table_min_width', title:'最小列宽', align: 'center', edit: 'text'}
                    ,{field:'table_type', title:'列类型', templet: '#table_type', align: 'center', width: 120}
                    ,{field:'table_align', title:'排列方式', templet: '#table_align', align: 'center', width: 120}
                    ,{field:'table_additional', title:'附加选项', templet: "#table_additional", align: 'center', width: 520}
                ]
            ]
            ,data:data
            ,done:function(res){
                func_controller.data = res.data;
            }
        });

        form.on('select(field_type)',function(data){
            console.log(data);
            console.log($(data).data('field_name'));
            var sel_val = data.value;
            //有int附加选项才有自增,主键,无符号,必填
            return true;
        });
    }

    func_controller.table_render([
        {
            field_name:"id"
            ,field_comment:"ID"
            ,field_type:"int"
            ,field_length:"11"
            ,field_decimal:"0"
            ,field_val:""
            ,table_width:"100"
            ,table_min_width:"60"
            ,table_type:"60"
        }
    ]);

    window.func_controller = func_controller;
})()
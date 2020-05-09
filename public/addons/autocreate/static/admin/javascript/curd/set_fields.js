(function func_controller() {
    var func_controller = {};

    func_controller.default_data = [
        {
            field_name:"id"//字段名称
            ,field_comment:"ID"//字段注释
            ,field_type:"int"//字段类型
            ,field_length:"11"//长度
            ,field_decimal:"0"//小数点
            ,field_ai_show:true//字段附加选项-自增是否显示
            ,field_ai:"1"//字段附加选项-自增
            ,field_pk_show:true//字段附加选项-主键是否显示
            ,field_pk:"1"//字段附加选项-主键
            ,field_unsigned_show:true//字段附加选项-无符号是否显示
            ,field_unsigned:"1"//字段附加选项-无符号
            ,field_required_show:true//字段附加选项-必填是否显示
            ,field_required:"1"//字段附加选项-必填
            ,field_optional_val:"0=未匹配,1=待支付,2=已取消,3=已付款,4=已取消,5=已完成"//可选值
            ,field_default_val:"1"//默认值
            ,form_type:"1"//表单元素
            ,form_required:""//附加选项
            ,table_width:"100"//绝对列宽
            ,table_min_width:"60"//最小列宽
            ,table_type:"60"//列类型
            ,table_align:"60"//排列方式
            ,table_additional:"60"//附加选项
        }
    ];

    func_controller.table_render = function (data) {
        table.render({
            elem: '.laytp-table'
            ,url:''
            ,cellMinWidth: 100
            ,cols: [
                [
                    {title:'数据库字段设置', width:100, align: 'center', colspan: 10}
                    ,{title:'表单设置,影响添加编辑表单', align: 'center', colspan: 2}
                    ,{title:'列表设置', align: 'center', colspan: 5}
                ]
                ,[
                    //数据库字段设置
                    {field:'operation', title:'操作', toolbar: '#operation', align: 'center', width: 200}
                    ,{title:'序号', type:'numbers', align: 'center'}
                    ,{field:'field_name', title:'字段名称', width:100, align: 'center', edit: 'text'}
                    ,{field:'field_comment', title:'字段注释(同时用于显示在表头和表单中的文字)', width:100, align: 'center', edit: 'text'}
                    ,{field:'field_type', title:'字段类型', templet: '#field_type', align: 'center', width: 190}
                    ,{field:'field_length', title:'长度', align: 'center', edit: 'text'}
                    ,{field:'field_decimal', title:'小数点', align: 'center', edit: 'text'}
                    ,{field:'field_additional', title:'附加选项', templet: "#field_additional", align: 'center', width: 370}
                    ,{field:'field_optional_val', title:'可选值', align: 'center', edit: 'text', width: 160}
                    ,{field:'field_default_val', title:'默认值', align: 'center', edit: 'text', width: 160}
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
            // console.log(table.cache[1]);

            //有int附加选项才有自增,主键,无符号,必填
            return true;
        });
    }

    func_controller.table_render(func_controller.default_data);

    window.func_controller = func_controller;
})()
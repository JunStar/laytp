layui.use(['layTp'],function() {
    const
        func_controller = {}
        ,layTp = layui.layTp
        ,$ = layui.jquery
    ;

    //表格渲染
    func_controller.table_render = function (where) {
        layui.table.render({
            elem: '.layui-hide-sm'
            , id: table_id
            , url: window.location.href
            , parseData: function(res){ //res 即为原始返回的数据
                return {
                    "code": 0, //解析接口状态
                    "msg": res.msg, //解析提示文本
                    "count": res.data.list.total, //解析数据长度
                    "data": res.data.list.data //解析数据列表
                };
            }
            , toolbar: '#default_toolbar'
            , where: where
            , even: true
            , method: 'GET'
            , cellMinWidth: 320
            , page: true
            , cols: [[
                {field:'name',title:'插件名称',align:'center'}
                ,{field:'description',title:'简介',align:'center'}
                ,{field:'operation',title:'操作',align:'center',toolbar:'#operation',width:100}
            ]]
        });

        //监听默认工具条
        layui.table.on('tool(default)', function(obj){
            if(default_table_tool.indexOf(obj.event) != -1){
                layTp.facade.table_tool(obj);
            }
        });
    }

    func_controller.table_render();

    window.func_controller = func_controller;

    $(document).on('click','.charge_status',function(){
        let obj = $(this);
        let field = obj.attr('field');
        let field_val = obj.attr('field_val');
        let click_field_val = parseInt( field_val );
        if( isNaN(click_field_val) ){
            click_field_val = "";
        }
        let data = {"charge_status":click_field_val};
        layui.laytpl($('#template_default_toolbar').html()).render(data, function(html){
            $('#default_toolbar').html(html);
            //搜索框的值设置成对应值
            $('#'+field).val(field_val);
            layui.form.render('select');
            $('[lay-submit]').click();
        });
        // layui.each($(".charge_status"),function(key,item){
        //     let field_val = $(item).attr('field_val');
        //     console.log(field_val);
        // });
        // func_controller.table_render();
    });
});
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
            // , cellMinWidth: 180
            , page: true
            , cols: [[
                {field:'name',title:'插件名称',align:'center',width:180}
                ,{field:'description',title:'简介',align:'center',width:180}
                ,{field:'author',title:'作者',align:'center',width:100}
                ,{field:'price',title:'价格',align:'center',width:100,templet:function(d){
                    if(d.charge_status == 1){
                        return '<text style="color: red;">￥' + d.price + '</text>';
                    }else{
                        return '<text style="color: green">免费</text>';
                    }
                }}
                ,{field:'download_num',title:'下载',align:'center',width:100}
                ,{field:'latest_version',title:'最新版本',align:'center',width:100}
                ,{field:'status',title:'状态',align:'center',width:100}
                ,{field:'operation',title:'操作',align:'center',toolbar:'#operation',width:380}
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
    });
});
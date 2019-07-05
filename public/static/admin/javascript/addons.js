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

});
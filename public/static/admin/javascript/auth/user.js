layui.use(['junAdmin'],function() {
    const
        func_controller = {}
        ,junAdmin = layui.junAdmin
        ,$ = layui.jquery
    ;

    //搜索表单渲染
    func_controller.search_form_render = function(){
        junAdmin.facade.select_multi();
    }

    func_controller.search_form_render();

    //表格渲染
    func_controller.table_render = function (where) {
        layui.table.render({
            elem: '.layui-hide-sm'
            , url: window.location.href
            , toolbar: '#default_toolbar'
            , where: where
            , even: true
            , method: 'POST'
            , cellMinWidth: 80
            , page: true
            , cols: [[
                {field:'id',title:'ID',fixed:'left',align:'center',width:80}
				,{field:'username',title:'用户名',align:'center'}
				,{field:'name',title:'姓名',align:'center'}
				,{field:'avatar',title:'头像',align:'center'}
				,{field:'is_super_manage',title:'是否为超管',align:'center'}
				,{field:'create_time',title:'创建时间',align:'center'}
				,{field:'operation',title:'操作',align:'center',toolbar:'#operation',fixed:'right',width:200}
            ]]
        });

        //监听默认工具条
        layui.table.on('tool(default)', function(obj){
            if(default_table_tool.indexOf(obj.event) != -1){
                junAdmin.facade.table_tool(obj);
            }
        });
    }

    func_controller.table_render();

    window.func_controller = func_controller;

});
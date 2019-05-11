layui.use(['layTp'],function() {
    const
        func_controller = {}
        ,layTp = layui.layTp
        ,$ = layui.jquery
        ,dropdown = layui.dropdown
    ;

    //搜索表单渲染
    func_controller.search_form_render = function(){
        layTp.facade.select_multi();
    }

    func_controller.search_form_render();

    //批量操作渲染
    //批量操作渲染
    dropdown.render({
        elem: '.action-more',
        options: [
            {
                action: 'del',
                title: '删除'
                ,icon: "layui-icon-delete"
                ,uri: layTp.facade.url(module + "/" + controller + "/del")
                ,switch_type: "confirm_action"
            }
        ]
    });

    //表格渲染
    func_controller.table_render = function (where) {
        layui.table.render({
            elem: '.laytp-table'
            , id: table_id
            , url: window.location.href
            , toolbar: '#default_toolbar'
            , where: where
            , even: true
            , method: 'GET'
            , cellMinWidth: 80
            , page: true
            , cols: [[
                {type:'checkbox'}
				,{field:'id',title:'ID',align:'center',width:80}
				,{field:'admin_id',title:'管理员姓名',align:'center'}
				,{field:'url',title:'操作页面',align:'center'}
				,{field:'title',title:'菜单位置',align:'center'}
				,{field:'content',title:'参数',align:'center'}
				,{field:'ip',title:'IP',align:'center'}
				,{field:'user_agent',title:'浏览器',align:'center'}
				,{field:'create_time',title:'操作时间',align:'center'}
				,{field:'operation',title:'操作',align:'center',toolbar:'#operation',fixed:'right',width:100}
            ]]
        });

        //监听默认工具条
        layui.table.on('tool(default)', function(obj){
            if(default_table_tool.indexOf(obj.event) != -1){
                layTp.facade.table_tool(obj);
            }else{
                //新增的其他操作按钮在这里来写
                switch(obj.event){
                   case 'detail':
                       let data = obj.data;
                       let url = layTp.facade.url(module + '/' + controller + '/detail',{id:data.id});
                       layTp.facade.popup_frame('详情', url);
                       break;
                }
            }
        });
    }

    func_controller.table_render();

    window.func_controller = func_controller;

});
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
    dropdown.render({
        elem: '.action-more',
        options: [
            {
                action: "edit"
                ,title: "编辑"
                ,icon: "layui-icon-edit"
                ,uri: layTp.facade.url(module + "/" + controller + "/edit")
                ,switch_type: "popup_frame"
            }
            ,{
                action: 'recycle'
                ,title: '放入回收站'
                ,icon: "layui-icon-fonts-del"
                ,uri: layTp.facade.url(module + '/' + controller + '/set_status')
                ,field: "is_del"
                ,field_val: "1"
                ,switch_type: "confirm_action"
            }
            ,{
                action: 'renew',
                title: '恢复数据'
                ,icon: "layui-icon-list"
                ,uri: layTp.facade.url(module + '/' + controller + '/set_status')
                ,field: "is_del"
                ,field_val: "0"
                ,switch_type: "confirm_action"
            }
            ,{
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
            elem: '.layui-hide-sm'
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
				,{field:'username',title:'用户名',align:'center'}
				,{field:'name',title:'姓名',align:'center'}
				,{field:'avatar',title:'头像',align:'center'}
				,{field:'is_super_manager',title:'是否为超管',align:'center'}
				,{field:'status',title:'账号状态',align:'center'}
				,{field:'is_del',title:'在回收站',align:'center'}
				,{field:'create_time',title:'创建时间',align:'center'}
				,{field:'operation',title:'操作',align:'center',toolbar:'#operation',fixed:'right',width:200}
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
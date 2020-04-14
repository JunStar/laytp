layui.use(['layTp'],function() {
    const
        func_controller = {}
        ,layTp = layui.layTp
        ,$ = layui.jquery
        ,dropdown = layui.dropdown
    ;

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
                action: 'del',
                title: '删除'
                ,icon: "layui-icon-delete"
                ,uri: layTp.facade.url(module + "/" + controller + "/del")
                ,switch_type: "confirm_action"
            }
        ]
    });

    //表格渲染
    func_controller.table_render = function (where, page) {
        layui.table.render({
            elem: '.laytp-table'
            , id: table_id
            , url: window.location.href
            , toolbar: '#default_toolbar'
            , where: where
            , even: true
            , method: 'GET'
            , cellMinWidth: 80
            , page: {
                curr: page
            }
            , cols: [[
                {type:'checkbox'}
                ,{field:'id',title:'ID',align:'center',width:80}
				,{field:'username',title:'用户名',align:'center'}
				,{field:'nickname',title:'昵称',align:'center'}
				,{field:'avatar',title:'头像',align:'center',templet:function(d){
                    return layTp.facade.formatter.images(d.avatar);
                }}
				,{field:'is_super_manager',title:'是否为超管',align:'center',templet:function(d){
                    return layTp.facade.formatter.switch('is_super_manager',d,{"open":{"value":1,"text":"是"},"close":{"value":0,"text":"否"}});
                }}
				,{field:'status',title:'账号状态',align:'center',templet:function(d){
                    return layTp.facade.formatter.switch('status',d,{"open":{"value":1,"text":"正常"},"close":{"value":0,"text":"冻结"}});
                }}
				,{field:'create_time',title:'创建时间',align:'center',width:180}
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
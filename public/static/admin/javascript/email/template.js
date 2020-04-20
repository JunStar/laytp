layui.use(['layTp'],function() {
    const
        func_controller = {}
        ,layTp = layui.layTp
        ,$ = layui.jquery
    ;

    //批量操作下拉展示列表设置
    let batch_dropdown_list = [
        {
            action: "edit"
            ,title: "编辑"
            ,icon: "layui-icon-edit"
            ,node: module + "/" + controller + "/edit"
            ,switch_type: "popup_frame"
        }
        ,{
            action: 'del'
            ,title: '删除'
            ,icon: "layui-icon-delete"
            ,node: module + "/" + controller + "/del"
            ,switch_type: "confirm_action"
        }
    ];

    layTp.facade.dropdown_set(batch_dropdown_list,true);

    //表格渲染
    func_controller.table_render = function (where,page) {
        layui.table.render({
            elem: '.layui-hide-sm'
            , id: table_id
            , url: window.location.href
            , toolbar: '#default_toolbar'
            , where: where
            , even: true
            , method: 'GET'
            , cellMinWidth: 180
            , page: {
                curr: page
            }
            , cols: [[
                {type:'checkbox'}
				,{field:'id',title:'ID',align:'center',width:80}
				,{field:'event',title:'事件名称',align:'center'}
				,{field:'title',title:'模板标题',align:'center'}
				,{field:'template',title:'模板内容，支持html标签',align:'center'}
				,{field:'ishtml',title:'模板是否为html',align:'center',templet:function(d){
					return layTp.facade.formatter.switch('ishtml',d,{"open":{"value":1,"text":"是"},"close":{"value":2,"text":"不是"}});
				}}
				,{field:'expire',title:'过期时长，单位秒，0表示永不过期',align:'center'}
				//,{field:'create_time',title:'创建时间',align:'center'}
				//,{field:'update_time',title:'更新时间',align:'center'}
				//,{field:'delete_time',title:'删除时间',align:'center'}
				,{field:'operation',title:'操作',align:'center',toolbar:'#operation',width:100,fixed:'right'}
            ]]
        });

        //监听默认工具条
        layui.table.on('tool(default)', function(obj){
            if(default_table_tool.indexOf(obj.event) != -1){
                layTp.facade.table_tool(obj);
            }else{
                //新增的其他操作按钮在这里来写
                //switch(obj.event){
                //    case '':
                //
                //        break;
                //}
            }
        });
    }

    func_controller.table_render();

    window.func_controller = func_controller;

});
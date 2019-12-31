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
    func_controller.table_render = function (where) {
        layui.table.render({
            elem: '.layui-hide-sm'
            , id: table_id
            , url: window.location.href
            , toolbar: '#default_toolbar'
            , where: where
            , even: true
            , method: 'GET'
            , cellMinWidth: 180
            , page: true
            , cols: [[
                {type:'checkbox'}
				,{field:'id',title:'ID',align:'center',width:80}
				,{field:'template_id',title:'模板ID',align:'center'}
				,{field:'event',title:'事件名称',align:'center'}
				,{field:'params',title:'邮件内容参数',align:'center'}
				,{field:'content',title:'邮件内容',align:'center'}
				,{field:'from',title:'发件人邮箱',align:'center'}
				,{field:'to',title:'收件人邮箱',align:'center'}
				,{field:'status',title:'状态',align:'center',templet:function(d){
					return layTp.facade.formatter.status('status',d.status,{"1":"未使用","2":"已使用","3":"已过期"});
				}}
				,{field:'expire_time',title:'过期时间，0表示永不过期',align:'center'}
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
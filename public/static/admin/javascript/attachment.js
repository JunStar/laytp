layui.use(['layTp'],function() {
    const
        func_controller = {}
        ,layTp = layui.layTp
        ,$ = layui.jquery
    ;

    //批量操作渲染
    layui.dropdown.render({
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
    func_controller.table_render = function (where) {
        layui.table.render({
            elem: '.layui-hide-sm'
            , id: table_id
            , url: window.location.href
            , toolbar: '#default_toolbar'
            , where: where
            , even: true
            , method: 'GET'
            , cellMinWidth: 320
            , page: true
            , cols: [[
                {type:'checkbox'}
				,{field:'id',title:'ID',align:'center',width:80}
				,{field:'file_path',title:'文件',align:'center',templet:function(d){
                    if(d.file_type == 'images'){
                        return layTp.facade.formatter.images(d.file_path);
                    }else if(d.file_type == 'video'){
                        return layTp.facade.formatter.video(d.file_path);
                    }else if(d.file_type == 'audio'){
                        return layTp.facade.formatter.audio(d.file_path);
                    }else{
                        return layTp.facade.formatter.file(d.file_path);
                    }
                }}
				,{field:'file_type',title:'文件类型',width:280,align:'center',templet:function(d){
					return layTp.facade.formatter.status('file_type',d.file_type,{"images":"图片","video":"视频","audio":"音频","file":"文件"});
				}}
				,{field:'create_time',title:'创建时间',align:'center'}
				//,{field:'delete_time',title:'删除时间',align:'center'}
				,{field:'operation',title:'操作',align:'center',toolbar:'#operation',width:100}
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
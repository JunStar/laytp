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
    ];

    layTp.facade.dropdown_set(batch_dropdown_list,true);

    //表格渲染
    func_controller.table_render = function (where, page) {
        layui.table.render({
            elem: '.layui-hide-sm'
            , id: table_id
            , url: window.location.href
            , toolbar: '#default_toolbar'
            , where: where
            , even: true
            , method: 'GET'
            , cellMinWidth: 320
            , page: {
                curr: page
            }
            , cols: [[
                {type:'checkbox'}
				,{field:'id',title:'ID',align:'center',width:80}
				,{field:'pid',title:'上级名称',align:'center',templet:'<div>{{# if(d.area){ }}{{d.area.name}}{{# }else{ }}-{{# } }}</div>'}
				,{field:'short_name',title:'简称',align:'center'}
				,{field:'name',title:'名称',align:'center'}
				,{field:'merge_name',title:'全称',align:'center'}
				,{field:'level',title:'层级',align:'center',templet:function(d){
					return layTp.facade.formatter.status('level',d.level,{"1":"省","2":"市","3":"区县"});
				}}
				,{field:'pinyin',title:'拼音',align:'center'}
				,{field:'code',title:'长途区号',align:'center'}
				,{field:'zip',title:'邮编',align:'center'}
				,{field:'first',title:'首字母',align:'center'}
				,{field:'lng',title:'经度',align:'center'}
				,{field:'lat',title:'纬度',align:'center'}
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
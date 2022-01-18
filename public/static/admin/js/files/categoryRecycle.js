layui.use(['laytp'], function () {
    const funRecycleController = {};
    //静态页面地址前缀
        window.htmlPrefix = facade.compatibleHtmlPath("/admin/files/category/");
        //后端接口地址前缀
        window.apiPrefix  = facade.compatibleApiRoute("/admin.files.category/");

    //表格渲染
    funRecycleController.tableRender = function (where, page) {
        layui.table.render({
            elem: "#laytp-recycle-table"
            , id: "laytp-recycle-table"
            , url: facade.url("/admin.files.category/recycle")
            , toolbar: "#recycle-default-toolbar"
            , defaultToolbar: [{
                title: '刷新',
                layEvent: 'recycle-refresh',
                icon: 'layui-icon-refresh',
            }, 'filter', 'print', 'exports']
            , where: where
            , method: "GET"
            , cellMinWidth: 100
            , skin: 'line'
            , loading: false
            , page: {
                curr: page
            }
            , parseData: function (res) { //res 即为原始返回的数据
                return facade.parseTableData(res, true);
            }
            , done: function(){
                layui.laytpTable.done();
            }
            , cols: [[ //表头
                {type:'checkbox',fixed:'left'}
				,{field:'id',title:'ID',align:'center',width:80,fixed:'left'}
				,{field:'name',title:'分类名称'}
				,{field:'pid',title:'父级',align:'center',templet:'<div>{{# if(d.parent){ }}{{d.parent.name}}{{# }else{ }}-{{# } }}</div>'}
				,{field:'create_time',title:'创建时间',align:'center'}
				,{field:'sort',title:'排序',align:'center',templet:function(d){
                        return laytpForm.tableForm.recycleEditInput('sort',d,'/admin.files.category/setSort');
                    }}
                ,{field:'operation',title:'操作',align:'center',toolbar:'#recycle-default-bar',width:140,fixed:'right'}
            ]]
        });

        //监听数据表格顶部左侧按钮点击事件
        layui.table.on("toolbar(laytp-recycle-table)", function (obj) {
            var defaultTableToolbar = layui.context.get("defaultTableToolbar");
            if (defaultTableToolbar.indexOf(obj.event) !== -1) {
                //默认按钮点击事件
                laytp.tableToolbar(obj);
            } else {
                // //自定义按钮点击事件
                // switch(obj.event){
                // //自定义按钮点击事件
                // case "":
                //
                //     break;
                // }
            }
        });

        //监听数据表格[操作列]按钮点击事件
        layui.table.on('tool(laytp-recycle-table)', function (obj) {
            var defaultTableTool = layui.context.get("defaultTableTool");
            if (defaultTableTool.indexOf(obj.event) !== -1) {
                laytp.tableTool(obj);
            } else {
                //自定义按钮
                // switch(obj.event){
                // case '':
                //
                //     break;
                // }
            }
        });
    };

    funRecycleController.tableRender();

    window.funRecycleController = funRecycleController;
});
layui.use(["laytp"], function () {
    const funController = {};
    //静态页面地址前缀
        window.htmlPrefix = facade.compatibleHtmlPath("/admin/files/category/");
        //后端接口地址前缀
        window.apiPrefix  = facade.compatibleApiRoute("/admin.files.category/");

    //表格渲染
    funController.tableRender = function (where) {
        //treeTable必须赋值，批量操作需要使用这个值获取复选框选中的数据
        laytpTreeTable = layui.treeTable.render({
            elem: "#laytp-tree-table"
            , url: facade.url("/admin.files.category/index")
            , where: where
            , method: "GET"
            , toolbar: "#default-toolbar"
            , defaultToolbar: [{
                title: '刷新',
                layEvent: 'refresh',
                icon: 'layui-icon-refresh',
            }, 'filter', 'print', 'exports']
            , skin: 'line'
            , tree: {
                iconIndex: 2        // 折叠图标显示在第几列
                , arrowType: 'arrow3'   // 自定义箭头风格
                , getIcon: function (d) {  // 自定义图标
                    // d是当前行的数据
                    if (d.children && d.children.length > 0) {  // 判断是否有子集
                        return '<i class="laytp-tree-icon laytp-tree-icon-folder"></i>';
                    } else {
                        return '<i class="laytp-tree-icon laytp-tree-icon-file"></i>';
                    }
                }
            }
            , parseData: function (res) {
                return facade.parseTableData(res, false);
            }
            , cols: [[
                {type:'checkbox',fixed:'left'}
				,{field:'id',title:'ID',align:'center',width:80,fixed:'left'}
				,{field:'name',title:'分类名称'}
				,{field:'pid',title:'父级',align:'center',templet:'<div>{{# if(d.parent){ }}{{d.parent.name}}{{# }else{ }}-{{# } }}</div>'}
				,{field:'create_time',title:'创建时间',align:'center'}
				,{field:'sort',title:'排序',align:'center',templet:function(d){
                        return laytpForm.tableForm.editInput('sort',d,'/admin.files.category/setSort');
                    }}
                ,{field:'operation',title:'操作',align:'center',toolbar:'#default-bar',width:185,fixed:'right'}
            ]]
        });

        //监听数据表格顶部左侧按钮点击事件
        layui.treeTable.on("toolbar(laytp-tree-table)", function (obj) {
            //默认按钮点击事件，包括添加按钮和回收站按钮
            var defaultTableToolbar = layui.context.get("defaultTableToolbar");
            if (defaultTableToolbar.indexOf(obj.event) !== -1) {
                laytp.tableToolbar(obj, true);
                //其他自定义按钮点击事件
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
        layui.treeTable.on("tool(laytp-tree-table)", function (obj) {
            var defaultTableTool = layui.context.get("defaultTableTool");
            if (defaultTableTool.indexOf(obj.event) !== -1) {
                laytp.tableTool(obj);
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
    };

    funController.tableRender();

    window.funController = funController;
});
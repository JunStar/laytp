layui.use(['laytp'], function () {
    const funRecycleController = {};
    //静态页面地址前缀
    window.htmlPrefix = facade.compatibleHtmlPath("/admin/files/");
    //后端接口地址前缀
    window.apiPrefix  = facade.compatibleApiRoute("/admin.files/");

    //表格渲染
    funRecycleController.tableRender = function (where, page) {
        layui.table.render({
            elem: "#laytp-recycle-table"
            , id: "laytp-recycle-table"
            , url: facade.url("/admin.files/recycle")
            , toolbar: "#recycle-default-toolbar"
            , defaultToolbar: [{
                title: '刷新',
                layEvent: 'recycle-refresh',
                icon: 'layui-icon-refresh',
            }, 'filter', 'print', 'exports']
            , where: where
            , method: "GET"
            , cellMinWidth: 120
            , skin: 'line'
            , loading: false
            , page: {
                curr: page
            }
            , parseData: function (res) { //res 即为原始返回的数据
                return facade.parseTableData(res, true);
            }
            , cols: [[ //表头
                {type:'checkbox',fixed:'left'}
				,{field:'id',title:'ID',align:'center',width:80,fixed:'left'}
				,{field:'category_id',title:'所属分类',align:'center',templet:'<div>{{# if(d.category){ }}{{d.category.name}}{{# }else{ }}-{{# } }}</div>'}
				,{field:'name',title:'文件名称',align:'center',templet:function(d){
                        return layui.laytpl('{{=d.name}}').render({name:d.name});
                    }}
				,{field:'file_type',title:'文件类型',align:'center',templet:function(d){
                    return laytp.tableFormatter.status('file_type',d.file_type,{"value":["image","video","music","file"],"text":["图片","视频","音频","文件"]});
                }}
				,{field:'path',title:'文件路径',align:'center',templet:function(d){
					return laytp.tableFormatter.file(d.path);
				}}
				,{field:'upload_type',title:'上传方式',align:'center',templet:function(d){
                    return laytp.tableFormatter.status('upload_type',d.upload_type,{"value":["local","ali-oss","qiniu-kodo"],"text":["本地上传","阿里云OSS","七牛云KODO"]});
                }}
				,{field:'create_admin_user_id',title:'创建者',align:'center',templet:'<div>{{# if(d.createAdminUser){ }}{{d.createAdminUser.nickname}}{{# }else{ }}-{{# } }}</div>'}
				,{field:'update_admin_user_id',title:'最后更新者',align:'center',templet:'<div>{{# if(d.updateAdminUser){ }}{{d.updateAdminUser.nickname}}{{# }else{ }}-{{# } }}</div>'}
				,{field:'create_time',title:'创建时间',align:'center'}
				,{field:'update_time',title:'更新时间',align:'center'}
				,{field:'delete_time',title:'删除时间',align:'center'}
                ,{field:'operation',title:'操作',align:'center',toolbar:'#recycle-default-bar',width:150,fixed:'right'}
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
                // //自定义按钮
                // switch(obj.event){
                // //自定义按钮点击事件
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
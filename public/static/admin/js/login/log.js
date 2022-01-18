layui.use(["laytp"], function () {
    const funController = {};
    //静态页面地址前缀
    window.htmlPrefix = facade.compatibleHtmlPath("/admin/admin/login/log/");
    //后端接口地址前缀
    window.apiPrefix  = facade.compatibleApiRoute("/admin.login.log/");

    //表格渲染
    funController.tableRender = function (where, page) {
        layui.table.render({
            elem: "#laytp-table"
            , id: "laytp-table"
            , url: facade.url("/admin.login.log/index")
            , toolbar: "#default-toolbar"
            , defaultToolbar: [{
                title: '刷新',
                layEvent: 'refresh',
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
            , done: function(){
                layui.laytpTable.done();
            }
            , cols: [[
                {type:'checkbox',fixed:'left'}
				,{field:'id',title:'ID',align:'center',width:80,fixed:'left'}
				,{field:'login_status',title:'登录状态',align:'center',templet:function(d){
					return laytp.tableFormatter.status('login_status',d.login_status,{"value":["1","2"],"text":["成功","失败"],"default":0});
				}}
				,{field:'admin_id',title:'登录者',align:'center',templet:'<div>{{# if(d.adminUser){ }}{{d.adminUser.nickname}}{{# }else{ }}-{{# } }}</div>'}
				,{field:'request_body',title:'请求体',align:'center',templet:function(d){
                    return layui.laytpl('{{=d.request_body}}').render({request_body:d.request_body});
                }}
				,{field:'request_header',title:'请求头',align:'center',templet:function(d){
                    return layui.laytpl('{{=d.request_header}}').render({request_header:d.request_header});
                }}
				,{field:'ip',title:'IP',align:'center',templet:function(d){
                        return layui.laytpl('{{=d.ip}}').render({ip:d.ip});
                    }}
				,{field:'create_time',title:'创建时间',align:'center'}
            ]]
        });

        //监听数据表格顶部左侧按钮点击事件
        layui.table.on("toolbar(laytp-table)", function (obj) {
            //默认按钮点击事件，包括添加按钮和回收站按钮
            var defaultTableToolbar = layui.context.get("defaultTableToolbar");
            if (defaultTableToolbar.indexOf(obj.event) !== -1) {
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
        layui.table.on("tool(laytp-table)", function (obj) {
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

        //监听表头排序事件
        layui.table.on('sort(laytp-table)', function(obj){
            layui.table.reload('laytp-table', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。
                , where: {
                    "order_param" : {
                        "field" : obj.field,
                        "type" : obj.type
                    }
                }
            });
        });
    };

    funController.tableRender();

    window.funController = funController;
});
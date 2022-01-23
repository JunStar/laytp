layui.use(["laytp"], function () {
    const funController = {};
    //静态页面地址前缀
    window.htmlPrefix = facade.compatibleHtmlPath("/admin/plugins/");
    //后端接口地址前缀
    window.apiPrefix  = facade.compatibleApiRoute("/admin.plugins/");

    //表格渲染
    funController.tableRender = function (where, page) {
        layui.table.render({
            elem: "#laytp-table"
            , id: "laytp-table"
            , url: facade.url('/plugins/index')
            , toolbar: "#default-toolbar"
            , defaultToolbar: [{
                title: '刷新',
                layEvent: 'refresh',
                icon: 'layui-icon-refresh',
            }, 'filter', 'print', 'exports']
            , where: where
            , autoSort: false
            , method: "GET"
            , cellMinWidth: 80
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
                {field: "name", title: "插件名称", align: "center", width: 140}
                , {field: "category", title: "所属分类", align: "center", width: 100, templet:function(d){
                    return d.category ? d.category.name : '-';
                }}
                , {field: "desc", title: "简介", align: "center", width: 400}
                , {field: "alias", title: "别名", align: "center"}
                , {field: "img", title: "插件图片", align: "center", templet:function(d){
                    return d.imgFile ? laytp.tableFormatter.images(d.imgFile.path) : '-';
                }}
                , {field: "author", title: "作者", align: "center"}
                , {field: "price", title: "价格", align: "center", templet:function(d){
                    return d.price > 0 ? "￥" + d.price/100 : "免费";
                }}
                , {field: "download_num", title: "下载次数", align: "center"}
                , {field: "version", title: "最新版本", align: "center", templet:function(d){
                    return d.lastVersion ? d.lastVersion.version_num : "-";
                }}
                , {field: "operation", title: "操作", align: "center", fixed: 'right', toolbar: "#default-bar", width: 140}
            ]]
        });

        //监听数据表格顶部左侧按钮点击事件
        layui.table.on("toolbar(laytp-table)", function (obj) {
            //默认按钮点击事件，包括添加按钮和回收站按钮
            var defaultTableToolbar = layui.context.get("defaultTableToolbar");
            if (defaultTableToolbar.indexOf(obj.event) !== -1) {
                laytp.tableToolbar(obj);
            } else {
                //自定义按钮点击事件
                switch(obj.event){
                    // 会员信息
                    case "user":
                        facade.popupDiv({
                            title: "会员信息",
                            path: "/admin/plugins/login.html"
                        });
                        break;
                }
            }
        });

        //监听数据表格[操作列]按钮点击事件
        layui.table.on("tool(laytp-table)", function (obj) {
            var defaultTableTool = layui.context.get("defaultTableTool");
            if (defaultTableTool.indexOf(obj.event) !== -1) {
                laytp.tableTool(obj);
            } else {
                //自定义按钮点击事件
                switch(obj.event){
                    // 卸载
                    case "uninstall":
                        facade.popupConfirm({
                            text: "真的卸载吗？卸载前请确保已经备份数据库，卸载插件的同时会将插件相关数据库表一并卸载删除",
                            route: window.apiPrefix + "uninstall",
                            data: {plugin: obj.data.alias}
                        }, function(){
                            layui.table.reload("laytp-table");
                            parent.parent.renderMenu();//重新渲染菜单
                        });
                        break;
                    // 安装
                    case "install":
                        var laytpGwToken = layui.context.get("laytpGwToken");
                        if(!laytpGwToken){
                            facade.popupDiv({
                                title: "会员信息",
                                path: "/admin/plugins/login.html"
                            });
                            return false;
                        }
                        var btnAnim = layui.button.load({elem:$(this)});
                        facade.ajax({
                            route:"/admin.plugins/install",
                            data:{"plugin":obj.data.alias, "laytpGwToken":layui.context.get("laytpGwToken").token}
                        }).done(function(res){
                            if(res.code === 0){
                                layui.table.reload("laytp-table");
                                parent.parent.renderMenu();//重新渲染菜单
                            }else if(res.code === 1){
                                facade.popupDiv({
                                    title: "会员信息",
                                    path: "/admin/plugins/login.html"
                                });
                            }else if(res.code === 2){
                                facade.popupDiv({
                                    title: "购买插件",
                                    path: "/admin/plugins/buy.html?name="+obj.data.name+"&alias="+obj.data.alias+"&price="+obj.data.price
                                });
                            }
                            btnAnim.stop();
                        }).fail(function(){
                            btnAnim.stop();
                        });

                        break;
                }
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

    // 离线安装按钮渲染上传组件
    var offLineBtnAnim = '';
    var uploadInst = layui.upload.render({
        elem: $("button[lay-event='offLineInstall']") //绑定元素
        ,accept:"file"
        ,url: facade.compatibleApiRoute('/admin.plugins/offLineInstall') //上传接口
        ,field: "laytpUploadFile"
        ,choose: function (obj) {
            offLineBtnAnim = layui.button.load({elem:$("button[lay-event='offLineInstall']")});
        }
        ,done: function(res){
            if(res.code === 0){
                facade.success(res.msg);
            }else{
                facade.error(res.msg);
            }
            offLineBtnAnim.stop();
        }
        ,error: function(){
            //请求异常回调
            offLineBtnAnim.stop();
        }
    });

    window.funController = funController;
});
layui.use(['layTp'],function() {
    const
        func_controller = {}
        ,layTp = layui.layTp
        ,$ = layui.jquery
    ;

    //表格渲染
    func_controller.table_render = function (where,page) {
        layui.table.render({
            elem: '.layui-hide-sm'
            , id: table_id
            , url: window.location.href
            , parseData: function(res){ //res 即为原始返回的数据
                return {
                    "code": 0, //解析接口状态
                    "msg": res.msg, //解析提示文本
                    "count": res.data.list.total, //解析数据长度
                    "data": res.data.list.data //解析数据列表
                };
            }
            , toolbar: '#top_toolbar'
            , where: where
            , even: true
            , method: 'GET'
            , cellMinWidth: 180
            , page: {
                curr: page
            }
            , cols: [[
                {field:'title',title:'插件名称',width:90}
                ,{field:'description',title:'简介',width:255}
                ,{field:'author',title:'作者',align:'center',width:70}
                ,{field:'price',title:'价格',align:'center',width:90,templet:function(d){
                    if(d.charge_type == 2){
                        return '<text style="color: red;">￥' + d.price + '</text>';
                    }else if(d.charge_type == 3){
                        return '<text style="color: blue;">' + parseInt(d.price) + '积分</text>';
                    }else{
                        return '<text style="color: green">免费</text>';
                    }
                }}
                ,{field:'download_num',title:'下载次数',width:90,align:'center'}
                ,{field:'version',title:'版本',width:90,align:'center',templet:function(d){
                    if(d.version < d.latest_version){
                        return '<a href="" time="4000" colour="#000000" layer-tips="发现新版本' + d.latest_version + '，点击查看更新日志">' + d.version + ' <text style="color:red;">*</text></a>';
                    }else{
                        return d.version;
                    }
                }}
                ,{field:'local_state',title:'状态',width:100,align:'center',templet:function(d){
                    let data_list = {"open":{"value":1,"text":"开启"},"close":{"value":0,"text":"关闭"}};
                    let lay_text = data_list.open.text + "|" + data_list.close.text;
                    return '<input open_value="'+data_list.open.value+'" close_value="'+data_list.close.value+'" name_val="'+d.name+'" type="checkbox" name="local_state" value="'+data_list.open.value+'" lay-skin="switch" lay-text="'+lay_text+'" lay-filter="addon_switch" ' + ( (d['local_state']==data_list.open.value) ? 'checked="checked"' : '' ) + ' />';
                    // return layTp.facade.formatter.switch('local_state',d,{"open":{"value":1,"text":"开启"},"close":{"value":0,"text":"关闭"}});
                }}
                ,{field:'operation',title:'操作',align:'right',templet:function(d){
                    let operation_html = '';
                    layui.laytpl($('#operation').html()).render(d, function(html){
                        operation_html = html;
                    });
                    return operation_html;
                }}
            ]]
        });

        //监听默认工具条
        layui.table.on('tool(default)', function(obj){
            if(default_table_tool.indexOf(obj.event) != -1){
                layTp.facade.table_tool(obj);
            }else{
                //新增的其他操作按钮在这里来写
                switch(obj.event){
                    case 'uninstall':
                        layTp.facade.popup_confirm("卸载 " +  obj.data.title + " 插件",layTp.facade.url(module + "/" + controller + "/uninstall",{name:obj.data.name}));
                        break;
                    case 'config':
                        layTp.facade.popup_frame("插件 " + obj.data.title+ " 配置",layTp.facade.url(module + "/" + controller + "/config",{name:obj.data.name}),'70%','70%');
                        break;
                    case 'install':
                        let laytp_token = layTp.facade.getcookie('laytp_token');
                        if(!laytp_token){
                            layTp.facade.popup_frame("会员信息",layTp.facade.url(module + "/" + controller + "/user"),'60%','55%');
                        }else{
                            layTp.facade.popup_frame("插件 " + obj.data.title + " 安装",layTp.facade.url(module + "/" + controller + "/install",{name:obj.data.name}),'60%','55%');
                        }
                        break;
                    case 'api':
                        layTp.facade.popup_frame("插件 " + obj.data.title + " Api文档",layTp.facade.url(module + "/" + controller + "/api",{name:obj.data.name}),'60%','55%');
                        break;
                    case 'domain':
                        layTp.facade.popup_frame("插件 " + obj.data.title + " 域名配置",layTp.facade.url(module + "/" + controller + "/domain",{name:obj.data.name}),'40%','30%');
                        break;
                }
            }
        });

        layui.form.on('switch(addon_switch)', function(obj){
            let open_value = obj.elem.attributes['open_value'].nodeValue;
            let close_value = obj.elem.attributes['close_value'].nodeValue;
            let field = obj.elem.attributes['name'].nodeValue;
            let name_val = obj.elem.attributes['name_val'].nodeValue;
            let post_data = {};
            if(obj.elem.checked){
                post_data = {field:this.name,field_val:open_value, name: name_val};
            }else{
                post_data = {field:this.name,field_val:close_value, name: name_val};
            }
            $.ajax({
                url: layTp.facade.url(module + '/' + controller + '/set_status/'),
                method: 'POST',
                data: post_data,
                success: function(res){
                    if(res.code == 1){
                        layTp.facade.success(res.msg);
                        func_controller.table_render();
                    }else{
                        layTp.facade.error(res.msg);
                        func_controller.table_render();
                    }
                },
            });
        });
    };

    func_controller.table_render();
    layTp.init.upload_render();
    window.func_controller = func_controller;

    //全部、免费、付费、积分、本地插件切换
    $(document).on('click','.charge_type',function(){
        let obj = $(this);
        let field = obj.attr('field');
        let field_val = obj.attr('field_val');
        let click_field_val = parseInt( field_val );
        if( isNaN(click_field_val) ){
            click_field_val = "";
        }
        let data = {"charge_type":click_field_val};
        layui.laytpl($('#top_toolbar_template').html()).render(data, function(html){
            $('#top_toolbar').html(html);
            //搜索框的值设置成对应值
            $('#'+field).val(field_val);
            layui.form.render('select');
            $('[lay-submit]').click();
        });
    });
});
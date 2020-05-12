/*
* @version: 1.0
* @Author:  JunStar
* @Date:    2018年09月20日11:47:00
* @Last Modified by:   JunStar
* @Last Modified time: 2019年06月09日23:23:09
*/
layui.define([
    'jquery', 'layer', 'form', 'table', 'laytpl', 'element','laydate','upload'
    ,'selectPage'
    ,'select_multi'
    ,'formSelects'
    ,'dropdown'
    ,'laytp_element'
    ,'laytp_tree'
    ,'colorpicker'
    ,'laytp_editor'
    ,'laytp_doceditor'
], function(exports){
    const MOD_NAME = 'layTp';
    let layTp = {};
    const $ = layTp.$ = layui.jquery;
    const selectPagePlugin = layui.selectPage;
    const laytp_editor = layui.laytp_editor;
    const laytp_doceditor = layui.laytp_doceditor;

    const default_popup_frame_width = '100%';
    const default_popup_frame_height = '100%';
    const default_popup_frame_shade = 0.1;

    //自定义表单验证器
    layui.form.verify({
        layTp_email:function(value){
            if(value.length > 0){
                if(!layui.form.config.verify.email[0].test(value)){
                    return layui.form.config.verify.email[1];
                }
            }
        },
        layTp_phone:function(value){
            if(value.length > 0){
                if(!layui.form.config.verify.phone[0].test(value)){
                    return layui.form.config.verify.phone[1];
                }
            }
        },
        layTp_number:function(value){
            if(value.length > 0){
                if(!value||isNaN(value)){
                    return"只能填写数字";
                }
            }
        },
        layTp_url:function(value){
            if(value.length > 0){
                if(!layui.form.config.verify.url[0].test(value)){
                    return layui.form.config.verify.url[1];
                }
            }
        },
        layTp_identity:function(value){
            if(value.length > 0){
                if(!layui.form.config.verify.identity[0].test(value)){
                    return layui.form.config.verify.identity[1];
                }
            }
        },
    });

    //助手函数
    layTp.facade = {
        //获取一个对象的元素个数
        get_count: function(param){
            let t = typeof param;
            if(t == 'string'){
                return param.length;
            }else if(t == 'object'){
                let n = 0;
                layui.each(param,function(){
                    n++;
                });
                return n;
            }
            return 0;
        },

        //多维数组的key换成item里面的某一个唯一的field的值
        array_to_map: function(array,field){
            let map = {};
            layui.each(array,function(key,item){
                map[item[field]] = item;
            });
            return map;
        },

        //获取树插件选中的项
        get_tree_checked_ids: function (jsonObj) {
            var id = "";
            $.each(jsonObj, function (index, item) {
                if (id != "") {
                    id = id + "," + item.id;
                }
                else {
                    id = item.id;
                }
                var i = layTp.facade.get_tree_checked_ids(item.children);
                if (i != "") {
                    id = id + "," + i;
                }
            });
            return id;
        },

        //跳转页面
        redirect: function(url){
            location.href = url;
        },

        //组装成url
        url: function(path, params){
            let count = 0;
            for(k in params){
                if(params.hasOwnProperty(k)){
                    count++;
                }
            }
            path = path.replace('\.html','');
            if( typeof params !== 'undefined' && count > 0 ){
                params = $.param(params);
                let reg = new RegExp('=','g');
                params = params.replace(reg,'/');
                let reg_1 = new RegExp('&','g');
                params = params.replace(reg_1,'/');
                params = '/' + params;
            }else{
                params = '';
            }
            path = '/' + path.replace(/(^\/)|(\/$)/,'');

            let url = path + params;
            return url;
        },

        //组装成插件url
        addon_url: function(addon,path, params){
            let count = 0;
            for(k in params){
                if(params.hasOwnProperty(k)){
                    count++;
                }
            }
            path = path.replace('\.html','');
            if( typeof params !== 'undefined' && count > 0 ){
                params = $.param(params);
                let reg = new RegExp('=','g');
                params = params.replace(reg,'/');
                let reg_1 = new RegExp('&','g');
                params = params.replace(reg_1,'/');
                params = '/' + params;
            }else{
                params = '';
            }
            path = '/' + path.replace(/(^\/)|(\/$)/,'');

            let url = '/addons/' + addon + path + params;
            return url;
        },

        setcookie: function(name,value,Days){
            var exp  = new Date();
            exp.setTime(exp.getTime() + Days*24*60*60*1000);
            document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString() + ";path=/";
        },

        getcookie:function(name){
            var arr = document.cookie.match(new RegExp("(^| )"+name+"=([^;]*)(;|$)"));
            if(arr != null){
                return (arr[2]);
            }else{
                return "";
            }
        },

        delcookie:function(name){
            var exp = new Date();
            exp.setTime(exp.getTime() - 1);
            var cval=getCookie(name);
            if(cval!=null) document.cookie= name + "="+cval+";expires="+exp.toGMTString();
        },

        //layEdit简易编辑器
        layEditor: function(options){
            laytp_editor.createEditor(options);
        },

        //layDocEditor
        layDocEditor: function(options){
            laytp_doceditor.createEditor(options);
        },

        //layer弹窗iFrame
        popup_frame: function(title, url, width, height, shade){
            if(width == undefined) {
                width = default_popup_frame_width;
            }
            if(height == undefined) {
                height = default_popup_frame_height;
            }
            if(shade == undefined) {
                shade = default_popup_frame_shade;
            }
            layui.layer.open({
                type : 2,
                title : title,
                content : url,
                area: [width,height],
                btn: [],//不加这个，全屏高度不会变化
                shade: shade,
                maxmin:true,
                skin:'laytp-layer',
                success: function(layero, index){
                    layTp.facade.after_popup_frame(layero,index);
                }
            });
        },

        //layer弹窗confirm
        popup_confirm: function(text,url){
            layui.layer.confirm('确定'+text+'吗?', function(index){
                $.ajax({
                    type: 'POST',
                    url: url,
                    // data: data,
                    dataType: 'json',
                    success: function (res) {
                        if( res.code == 1 ){
                            layTp.facade.success(res.msg);
                            if(typeof res.data.reload === 'boolean' && res.data.reload){
                                setTimeout(function () {
                                    parent.parent.location.reload();
                                }, 1000);
                            }
                            func_controller.table_render();
                        }else{
                            layTp.facade.error(res.msg);
                        }
                    },
                    error: function (xhr) {
                        if( xhr.status == '500' ){
                            layTp.facade.error('本地网络问题或者服务器错误');
                        }else if( xhr.status == '404' ){
                            layTp.facade.error('请求地址不存在');
                        }
                    }
                });
            });
        },

        //成功弹窗之后
        after_popup_frame: function(layero,index){
            layTp.facade.select_multi(layero,index);
        },

        //layer弹出层
        popup_div: function(title, content, width, height){
            if(width == undefined) {
                width = '500px';
            }
            if(height == undefined) {
                height = '220px';
            }
            layui.layer.open({
                type : 1,
                title : title,
                content : content,
                area: [width,height],
            });
        },

        //layer提示操作成功
        success: function(text){
            layui.layer.msg(text,{icon:1});
        },

        //layer提示操作失败
        error: function(text){
            layui.layer.msg(text,{icon:2});
        },

        //表格点击[编辑][删除][还原][彻底删除]按钮
        table_tool: function(obj){
            let data = obj.data;
            if(obj.event === 'del'){
                layui.layer.confirm('真的删除么?', function(index){
                    $.ajax({
                        type: 'POST',
                        url: layTp.facade.url(module + '/' + controller +'/del'),
                        data: {ids:data.id},
                        dataType: 'json',
                        success: function (res) {
                            if( res.code == 1 ){
                                //只删除一行不够，角色管理是多级列表，删除某个顶级列表，需要重新获取所有数据
                                obj.del();
                                func_controller.table_render();
                            }else{
                                layTp.facade.error(res.msg);
                            }
                            layui.layer.close(index);
                        },
                        error: function (xhr) {
                            if( xhr.status == '500' ){
                                layTp.facade.error('本地网络问题或者服务器错误');
                            }else if( xhr.status == '404' ){
                                layTp.facade.error('请求地址不存在');
                            }
                        }
                    });
                });
                //点击编辑按钮
            }else if(obj.event === 'edit'){
                let url = layTp.facade.url(module + '/' + controller + '/edit',{id:data.id});
                layTp.facade.popup_frame('编辑', url);
            }else if(obj.event === 'renew'){
                layui.layer.confirm('真的还原么?', function(index){
                    $.ajax({
                        type: 'POST',
                        url: layTp.facade.url(module + '/' + controller +'/renew'),
                        data: {ids:data.id},
                        dataType: 'json',
                        success: function (res) {
                            if( res.code == 1 ){
                                obj.del();
                                parent.func_controller.table_render();
                            }else{
                                layTp.facade.error(res.msg);
                            }
                            layui.layer.close(index);
                        },
                        error: function (xhr) {
                            if( xhr.status == '500' ){
                                layTp.facade.error('本地网络问题或者服务器错误');
                            }else if( xhr.status == '404' ){
                                layTp.facade.error('请求地址不存在');
                            }
                        }
                    });
                });
            }else if(obj.event === 'true_del'){
                layui.layer.confirm('真的彻底删除么?', function(index){
                    $.ajax({
                        type: 'POST',
                        url: layTp.facade.url(module + '/' + controller +'/true_del'),
                        data: {ids:data.id},
                        dataType: 'json',
                        success: function (res) {
                            if( res.code == 1 ){
                                obj.del();
                            }else{
                                layTp.facade.error(res.msg);
                            }
                            layui.layer.close(index);
                        },
                        error: function (xhr) {
                            if( xhr.status == '500' ){
                                layTp.facade.error('本地网络问题或者服务器错误');
                            }else if( xhr.status == '404' ){
                                layTp.facade.error('请求地址不存在');
                            }
                        }
                    });
                });
            }
        },

        //select_page插件
        select_page:function(obj, option){
            selectPagePlugin.selectPage(obj, option);
        },

        /**
         * 多选下拉框
         *  layero,index有值时，说明是layer弹窗中进行渲染，添加和编辑都是layer弹窗
         *  layero,index无值时，说明是非layer弹窗中进行渲染，列表页搜索表单部分使用到了
         *  html示例：<div id="hobby" class="select_multi" selected_data="['0','1','2']" options='{"value1":"text1"}' max="3" name="row[hobby]" verify="required"></div>
         * @param layero
         * @param index
         */
        select_multi:function(layero,index){
            let
                elem
                ,name
                ,data
                ,max
                ,verify
                ,field
                ,selected
            ;
            field = {idName:'id',titleName:'name'};

            //获取所有的class="select_multi"节点进行插件渲染

            //非弹窗内渲染多选下拉框
            if(layero == undefined && index == undefined){
                layui.each($('.select_multi'),function(key,item){
                    elem = '#'+$(item).attr('id');
                    name = $(item).attr('name');
                    data = $(item).attr('options') ? eval($(item).attr('options')) : eval($(item).attr('id'));
                    max = $(item).attr('max');
                    verify = $(item).attr('verify');
                    selected = eval($(item).attr('selected_data'));
                    layui.select_multi.render({
                        elem: elem
                        ,name: name
                        ,index:index
                        ,layero:layero
                        ,data: data
                        ,max: (parseInt(max) > 0) ? max : data.length
                        ,verify: verify
                        ,field: field
                        ,selected: selected
                        ,click_dd_after: function(){}
                    });
                });
                //弹窗内渲染多选下拉框
            }else{
                layui.each(layui.layer.getChildFrame('.select_multi', index),function(key,item){
                    elem = layui.layer.getChildFrame('#'+$(item).attr('id'), index);
                    name = $(item).attr('name');
                    data = eval($(item).attr('id'));
                    max = $(item).attr('max');
                    verify = $(item).attr('verify');
                    selected = eval($(item).attr('selected_data'));
                    layui.select_multi.render({
                        elem: elem
                        ,name: name
                        ,index:index
                        ,layero:layero
                        ,data: data
                        ,max: (parseInt(max) > 0) ? max : data.length
                        ,verify: verify
                        ,field: field
                        ,selected: selected
                        ,click_dd_after: function(){}
                    });
                });
            }
        },

        /*
         * 批量操作下拉展示列表设置
         * @param options array 需要展示的列表,数据格式类似:
         *  [
         *      {
                    action: "edit"//操作名称
                    ,title: "编辑"//文字标题
                    ,icon: "layui-icon-edit"//图标
                    ,node: module + "/" + controller + "/edit"
                    ,param: {}//操作节点需要传入的参数，为空可以不传
                    ,uri: layTp.facade.url(module + "/" + controller + "/edit",{id:id})//layTp.facade.url(node,param) = uri
                    ,switch_type: "popup_frame"//操作类型
                    ,width:'100%'//宽
                    ,height:'100%'//高
                    ,need_data:true//是否需要数据，默认为true，当为false时，不需要选中数据表的复选框
                    ,callback:"function_name"//回调函数名，函数可以使用window.function_name进行定义，callback有值，则node,uri,switch_type,width,height,need_data都无效，如果函数需要传参，参数使用param赋值
                }
                ,{
                    action: 'del',
                    title: '删除'
                    ,icon: "layui-icon-delete"
                    ,node: module + "/" + controller + "/del"//操作节点名称
                    ,uri: layTp.facade.url(module + "/" + controller + "/edit",{id:id})//layTp.facade.url(node,param) = uri
                    ,param: {}//操作节点需要传入的参数，为空可以不传
                    ,switch_type: "confirm_action"
                    ,width:'100%'//宽
                    ,height:'100%'//高
                    ,need_data:true//是否需要数据，默认为true，当为false时，不需要选中数据表的复选框
                    ,callback:"function_name"//回调函数名，函数可以使用window.function_name进行定义，callback有值，则node,uri,switch_type,width,height,need_data都无效，如果函数需要传参，参数使用param赋值
                }
         *  ]
         * @param elem string 渲染的document节点名称，举例：.action-more
         * @param checkAuth boolean 是否需要检测权限,true:需要检测当前用户是否有权限，false:不需要检测权限
         */
        dropdown_set: function(options,checkAuth,elem){
            if(typeof checkAuth == "undefined" || checkAuth === '' || checkAuth === 0){
                checkAuth = false;
            }
            if(typeof elem == "undefined" || (elem === '') || (elem === 0)){
                elem = ".action-more";
            }

            for(options_k in options){
                if(!options[options_k].uri){
                    options[options_k].uri = layTp.facade.url(options[options_k].node,options[options_k].param);
                }
            }

            if(!checkAuth){
                layui.dropdown.render({
                    elem: elem,
                    options: options
                });
            }else{
                if(is_super_manager){
                    layui.dropdown.render({
                        elem: elem,
                        options: options
                    });
                }else{
                    let hasAuthOptions = [];
                    for(key in options){
                        if(!options[key].hasOwnProperty("param")){
                            options[key].param = {};
                        }
                        if(!options[key].hasOwnProperty("callback")){
                            options[key].callback = '';
                        }
                        for(rk in rule_list){
                            var node_arr = options[key].node.split('/');
                            if(rule_list[rk] === node_arr[0] + '/' + node_arr[1] + '/' + node_arr[2]){
                                hasAuthOptions.push(
                                    {
                                        action: options[key].action
                                        ,title: options[key].title
                                        ,icon: options[key].icon
                                        ,uri: layTp.facade.url(options[key].node,options[key].param)
                                        ,switch_type: options[key].switch_type
                                        ,callback: options[key].callback
                                    }
                                );
                                break;
                            }
                        }
                    }
                    layui.dropdown.render({
                        elem: elem,
                        options: hasAuthOptions
                    });
                }
            }
        },

        //列表格式化展示数据
        formatter:{
            custom:["#FF5722","#009688","#FFB800","#2F4056","#1E9FFF","#393D49","#999999","#0b1bf8","#7a0bf8","#f00bf8","#5FB878","#1E9FFF","#2F4056"],
            //form_type=radio,带选项只有两个
            switch:function(field,d,data_list){
                let lay_text = data_list.open.text + "|" + data_list.close.text;
                return '<input open_value="'+data_list.open.value+'" close_value="'+data_list.close.value+'" id_val="'+d.id+'" type="checkbox" name="'+field+'" value="'+data_list.open.value+'" lay-skin="switch" lay-text="'+lay_text+'" lay-filter="laytp_switch" ' + ( (d[field]==data_list.open.value) ? 'checked="checked"' : '' ) + ' />';
            },
            //form_type=radio || select_single
            status:function(field,value,data_list){
                let custom_index = 0;
                for(key in data_list){
                    if(value == key){
                        return '<span click_search="true" field="'+field+'" field_val="'+value+'" layer-tips="点击搜索 '+data_list[key]+'" colour="#393D49" style="color:'+layTp.facade.formatter.custom[custom_index]+'"><i class="layui-icon layui-icon-circle-dot"></i>'+data_list[key]+'</span>';
                    }
                    custom_index++;
                }
                return '';
            },
            //form_type=select_multi || checkbox
            flag:function(value,data_list){
                let html = '';
                let custom_index = 0;
                if(value){
                    let value_arr = value.split(',');
                    for(key in data_list){
                        for(v in value_arr){
                            if(value_arr[v] == key){
                                custom_index = key % layTp.facade.formatter.custom.length;
                                html += '<span class="layui-btn layui-btn-sm-1" style="background-color: '+layTp.facade.formatter.custom[custom_index]+'">'+data_list[key]+'</span>';
                            }
                        }
                    }
                }
                return html;
            },
            images:function(value){
                let html = '';
                if(value) {
                    let value_arr = value.split(',');
                    for(key in value_arr){
                        html += '<a target="_blank" href="'+value_arr[key]+'"><img src="'+value_arr[key]+'" style="width:30px;height:30px;" /></a> ';
                    }
                }
                return html;
            },
            video:function(value){
                let html = '';
                if(value) {
                    let i = 1;
                    let value_arr = value.split(',');
                    for(key in value_arr){
                        html += '<a href="javascript:void(0);" class="popup-frame" data-name="查看视频" data-open="'+layTp.facade.url('admin/ajax/show_video',{'path':window.btoa(value_arr[key])})+'">视频'+i+'</a> ';
                        i++;
                    }
                }
                return html;
            },
            audio:function(value){
                let html = '';
                if(value) {
                    let value_arr = value.split(',');
                    for(key in value_arr){
                        html += '<audio src="'+value_arr[key]+'" width="200px" height="30px" controls="controls"></audio>';
                    }
                }
                return html;
            },
            file:function(value){
                let html = '';
                if(value) {
                    let i = 1;
                    let value_arr = value.split(',');
                    for(key in value_arr){
                        html += '<a href="javascript:void(0);" download="'+value_arr[key]+'" title="点击下载" class="layui-table-link">文件'+i+'</a> ';
                        i++;
                    }
                }
                return html;
            },
            dis_file:function(value){
                let html = '';
                if(value) {
                    let value_arr = value.split(',');
                    for(key in value_arr){
                        if(value_arr[key].substring(value_arr[key].length - 3) == 'mp4'){
                            html += '<a href="javascript:void(0);" class="popup-frame" data-name="查看视频" data-open="'+layTp.facade.url('admin/ajax/show_video',{'path':window.btoa(value_arr[key])})+'">视频</a> ';
                        }else{
                            html += '<a href="javascript:void(0);" class="preview_image" data-url="'+value_arr[key]+'">图片</a> ';
                        }
                    }
                }
                return html;
            }
        },
        //图片预览
        preview_image:function(url){
            var img = new Image();
            img.src = url;
            var imgHtml = "<img src='" + url + "' width='500px' height='500px'/>";
            //弹出层
            layui.layer.open({
                type: 1,
                shade: 0.8,
                offset: 'auto',
                area: [500 + 'px',550+'px'],
                shadeClose:true,
                scrollbar: false,
                title: "图片预览", //不显示标题
                content: imgHtml, //捕获的元素，注意：最好该指定的元素要存放在body最外层，否则可能被其它的相对元素所影响
                cancel: function () {
                    //layer.msg('捕获就是从页面已经存在的元素上，包裹layer的结构', { time: 5000, icon: 6 });
                }
            });
        }
    }

    //初始化
    layTp.init = {
        //jquery ajax set
        ajaxSet: function(){
            $.ajaxSetup({
                "async": false
            });
        },

        //图片预览
        preview_image:function(){
            $(document).on('click','.preview_image',function(){
                let url = $(this).data("url");
                layTp.facade.preview_image(url);
            });
        },

        //批量操作
        batch_action: function(){
            $(document).on('click','.batch-action',function(){
                //执行callback
                let callback = $(this).attr('callback');
                if(!(callback == "undefined" || callback == "")){
                    let param = $(this).attr('param');
                    eval(callback+"("+param+")");
                    return true;
                }
                let uri = $(this).attr("uri");
                if(uri == undefined){
                    return false;
                }
                let field = $(this).attr("field");
                let field_val = $(this).attr("field_val");
                let need_data = $(this).attr("need_data");
                let width = $(this).attr("width");
                let height = $(this).attr("height");
                let need_refresh = $(this).attr("need_refresh");
                let text = $(this).text();
                let switch_type = $(this).attr("switch_type");
                let checkStatus;
                let checkData;
                if( need_data == "true" ){
                    checkStatus = layui.table.checkStatus(table_id);
                    checkData = checkStatus.data;
                    if(checkData.length == 0){
                        layTp.facade.error('请选择数据');
                        return false;
                    }
                }
                let key;
                switch(switch_type){
                    case 'popup_frame':
                        if( need_data == "true" ){
                            for(key in checkData){
                                url = layTp.facade.url(uri,{id:checkData[key].id});
                                layTp.facade.popup_frame(text, url, width, height);
                            }
                        }else{
                            layTp.facade.popup_frame(text, uri, width, height);
                        }
                        break;
                    case 'confirm_action':
                        layui.layer.confirm('确定'+text+'么?', function(index){
                            let ids_arr = [];
                            let data = {};
                            if( need_data == "true" ){
                                for(key in checkData){
                                    ids_arr.push(checkData[key].id);
                                }
                                let ids = ids_arr.join(',');
                                data = {'ids':ids,'field':field,'field_val':field_val};
                            }
                            $.ajax({
                                type: 'POST',
                                url: layTp.facade.url(uri),
                                data: data,
                                dataType: 'json',
                                success: function (res) {
                                    if( res.code == 1 ){
                                        if( need_data == "true" || need_refresh == "true" ){
                                            if(typeof parent.func_controller != "undefined"){
                                                parent.func_controller.table_render();
                                            }else if(typeof func_controller != "undefined"){
                                                func_controller.table_render();
                                            }
                                        }
                                        layTp.facade.success(res.msg);
                                    }else{
                                        layTp.facade.error(res.msg);
                                    }
                                    layui.layer.close(index);
                                },
                                error: function (xhr) {
                                    if( xhr.status == '500' ){
                                        layTp.facade.error('本地网络问题或者服务器错误');
                                    }else if( xhr.status == '404' ){
                                        layTp.facade.error('请求地址不存在');
                                    }
                                }
                            });
                        });
                        break;
                }
            });
        },

        /**
         * 批量弹窗 - 使用例子：数据表格顶部的编辑按钮
         * 所有拥有batch_popup_frame的类名的节点，点击后都会弹窗
         *  节点的data-name属性为弹窗标题
         *  data-uri弹窗展示的url地址
         *  width属性为弹窗的宽度百分比
         *  height属性为弹窗的高度百分比
         */
        batch_popup_frame: function(){
            $(document).on('click','.batch-popup-frame',function(){
                let name = $(this).data("name") ? $(this).data("name") : '编辑';
                let uri = $(this).data("uri");
                let width = $(this).data("width");
                let height = $(this).data("height");
                if( !width ){
                    width = default_popup_frame_width;
                }
                if( !height ){
                    height = default_popup_frame_height;
                }
                let checkStatus = layui.table.checkStatus(table_id);
                let checkData = checkStatus.data;
                let key;
                let url;
                for(key in checkData){
                    url = layTp.facade.url(uri,{id:checkData[key].id});
                    layTp.facade.popup_frame(name, url, width, height);
                }
            });
        },

        /**
         * 弹窗 - 使用例子：数据表格顶部的添加按钮
         * 所有拥有popup-frame的类名的节点，点击后都会弹窗
         *  节点的data-name属性为弹窗标题
         *  data-open弹窗展示的url地址
         *  data-width属性为弹窗的宽度百分比
         *  data-height属性为弹窗的高度百分比
         *  data-current_node_val属性为当前节点名称，多个以逗号隔开，有这个值的话，会获取这些节点的值，拼接到url中
         */
        popup_frame: function(){
            $(document).on('click','.popup-frame',function(){
                let name = $(this).data("name") ? $(this).data("name") : '添加';
                let url = $(this).data("open");
                let width = $(this).data("width");
                let height = $(this).data("height");
                let current_node_val = $(this).data("current_node_val");
                let params = {};
                if(current_node_val){
                    let label_title = '';
                    let arr_current_node_val = current_node_val.split(',');
                    for(key in arr_current_node_val){
                        if(!$('#'+arr_current_node_val[key]).val()){
                            label_title = $('#'+arr_current_node_val[key]).parent().parent().find('label').attr('title');
                            if( !label_title ){
                                label_title = $('#'+arr_current_node_val[key]).parent().parent().parent().find('label').attr('title');
                            }
                            layTp.facade.error('请输入' + label_title);
                            return false;
                        }else{
                            params[arr_current_node_val[key]] = $('#'+arr_current_node_val[key]).val();
                        }
                    }
                }
                if( !width ){
                    width = default_popup_frame_width;
                }
                if( !height ){
                    height = default_popup_frame_height;
                }
                layTp.facade.popup_frame(name, layTp.facade.url(url, params), width, height);
            });
        },

        //筛选按钮 - 隐藏显示搜索表单
        show_hidden_search_form:function(){
            $(document).on('click','.show-hidden-search-form',function(){
                if($('#search-form').css("display") == 'none'){
                    $('#search-form').show();
                }else{
                    $('#search-form').hide();
                }
            });
        },

        //弹框二次选择 - 举例：菜单管理-》添加菜单-》图标-》搜索图标
        /**
         * class="popup-select" 表示弹窗选择的节点，绑定点击事件
         *  data-template_id="$template_id" 表示弹窗内容的模板ID，这里的模板使用laytpl的js模板
         *  data-source="$source" 表示模板内容要使用的数据源,一般是一个url地址
         *  data-input_id="表示二次选择弹出层得到的值要输入到哪个input节点的ID属性值"
         *
         * .pop-select-to-input 弹出层中的节点绑定点击事件
         *  data-input_val="value" 表示将value放到data-input_id节点中
         */
        popup_select: function(){
            $(document).on('click','.popup-select',function(){
                var title = $(this).text();
                var template_id = $(this).data('template_id');
                var source = $(this).data('source');
                pop_select_input = $(this).data('input_id');
                $.get(source,{},function(res){
                    var data = res.data;
                    layui.laytpl($('#'+template_id).html()).render(data, function(html){
                        layTp.facade.popup_div(title, html, '99%', '98%');
                    });
                });
            });

            $(document).on('click','.pop-select-to-input',function(){
                var value = $(this).data('input_value');
                $("#"+pop_select_input).val(value);
                layui.layer.closeAll();
            });
        },

        /**
         * 表单提交按钮绑定事件，包括添加表单、编辑表单和搜索表单
         * <form class="layui-form">
         *  <button class="layui-btn layui-btn-sm" lay-submit lay-filter="laytp">立即提交</button>
         * </form>
         * 重要的是
         *  1.form的class值为layui-form;
         *  2.button的属性值lay-submit lay-filter="laytp";
         */
        form_submit: function(){
            layui.form.on('submit(laytp)', function(data){
                /**
                 * 所有的表单提交都是执行ajax，ajax请求的地址都是当前的url
                 * 列表页的搜索表单要使用到layui的table控件进行ajax提交，其他表单使用jQuery的ajax提交方式
                 */
                let form_action = $(data.form).attr('action');
                //当前url的action值为index，搜索表单进行了提交
                if(module=='admin' && controller=='attachment' && action=='select'){
                    index(data);
                }else if( (action == 'index' || action == 'recycle') && typeof form_action == "undefined" ){
                    index(data);
                    //当前url的action值不是index，就ajax提交到当前url
                }else{
                    do_update(data);
                }
                return false; //阻止表单跳转。如果需要表单跳转，去掉这段即可。
            });

            //搜索表单进行提交时，触发的方法
            function index(data){
                if(typeof func_controller != "undefined"){
                    // func_controller.table_render(data.field);
                    var where = layui.form.val("laytp_search_form");
                    layui.table.reload(table_id, {
                        where: where
                        ,page: 1
                    });
                }else{
                    do_update(data);
                }
            }

            //添加和编辑的表单进行提交时触发的方法
            function do_update(data){
                let form_action = $(data.form).attr('action');
                let form_method = $(data.form).attr('method');
                let type = (typeof form_method == "undefined") ? "POST" : form_method;
                let url = (typeof form_action == "undefined") ? window.location.href : form_action;
                $.ajax({
                    type: type,
                    url: url,
                    data: data.field,
                    dataType: 'json',
                    success: function (res) {
                        if( res.code == 1 ){
                            layTp.facade.success(res.msg);
                            if(typeof res.data.reload === 'boolean' && res.data.reload){
                                setTimeout(function () {
                                    parent.parent.location.reload();
                                }, 1000);
                            }
                            if(typeof parent.func_controller != "undefined"){
                                let index = parent.layer.getFrameIndex(window.name);
                                setTimeout(function () {
                                    parent.layer.close(index);
                                    parent.func_controller.table_render(parent.layui.form.val("laytp_search_form"),parent.$(".layui-laypage-em").next().html());
                                }, 1000);
                            }
                        }else{
                            layTp.facade.error(res.msg);
                        }
                    },
                    error: function (xhr) {
                        if( xhr.status == '500' ){
                            layTp.facade.error('本地网络问题或者服务器错误');
                        }else if( xhr.status == '404' ){
                            layTp.facade.error('请求地址不存在');
                        }
                    }
                });
            }
        },

        /**
         * 渲染普通多选下拉框
         */
        select_multi: function(){
            layTp.facade.select_multi();
        },

        /**
         * 渲染时间插件
         */
        laydate_render: function(){
            layui.each($("input[laydate='true']"),function(key,item){
                let elem = '#'+$(item).attr('id');
                let laydate_type = $(item).attr('laydate_type') ? $(item).attr('laydate_type') : 'datetime';
                let laydate_range = ($(item).attr('laydate_range') == 'true') ? true : false;
                layui.laydate.render({
                    elem: elem //指定元素
                    ,type: laydate_type
                    ,range: laydate_range
                });
            });
        },

        /**
         * 渲染上传插件
         */
        upload_render: function(){
            layui.each($("button[upload='true']"),function(key,item) {
                let id = $(item).attr('id');
                let elem = '#' + id;
                let single_multi = $(item).attr('single_multi');
                let is_multiple = ( single_multi == 'multi' ) ? true : false;
                let accept = $(item).attr('accept');
                let upload_dir = $(item).attr('upload_dir') ? $(item).attr('upload_dir') : '';
                let upload_url = $(item).attr('upload_url') ? $(item).attr('upload_url') : layTp.facade.url('/admin/ajax/upload',{'accept':accept,'upload_dir':upload_dir});
                layui.upload.render({
                    elem: elem,
                    url: upload_url,
                    accept: accept,
                    multiple: is_multiple,
                    before: function (obj) {
                        if(!is_multiple || accept == 'file'){
                            $('#preview_' + id).html('');
                            $('#input_'+id).val('');
                        }
                        layer.msg('上传中...', {
                            icon: 16,
                            shade: 0.01,
                            time: 0
                        });
                    },
                    done: function (res) {
                        layer.close(layer.msg());//关闭上传提示窗口
                        if (res.code == 0) {
                            return layTp.facade.error(res.msg);
                        }
                        if(is_multiple){
                            //预览，LayUI的批量上传，其实是请求了多次上传接口
                            if(accept == 'images') {
                                $('#preview_' + id).append(
                                    '<li class="item_img">' +
                                    '<div class="operate">' +
                                    '<i class="upload_img_close layui-icon" file_url_data="' + res.data + '" node="'+id+'"></i>' +
                                    '</div>' +
                                    '<img src="' + res.data + '" class="img" >' +
                                    '</li>'
                                );
                            }else if(accept == 'video'){
                                $('#preview_' + id).append(
                                    '<li class="item_video">' +
                                    '<video src="' + res.data + '" controls="controls" width="200px" height="200px"></video>' +
                                    '<button class="layui-btn layui-btn-sm layui-btn-danger upload_delete" style="display: block; width: 100%;" file_url_data="' + res.data + '" node="'+id+'"><i class="layui-icon">&#xe640;</i></button>' +
                                    '</li>'
                                );
                            }else if(accept == 'audio'){
                                $('#preview_' + id).append(
                                    '<li class="item_audio">' +
                                    '<audio src="' + res.data + '" controls="controls" style="height:54px;"></audio>' +
                                    '<button class="layui-btn layui-btn-sm layui-btn-danger upload_delete" style="display: block; width: 100%;" file_url_data="' + res.data + '" node="'+id+'"><i class="layui-icon">&#xe640;</i></button>' +
                                    '</li>'
                                );
                            }
                            //隐藏input框增加文件值
                            let input_value = $('#input_'+id).val();
                            if(input_value){
                                $('#input_'+id).val( input_value + ',' + res.data );
                            }else{
                                $('#input_'+id).val( res.data );
                            }
                        }else{
                            //预览
                            if(accept == 'images') {
                                $('#preview_' + id).html(
                                    '<li class="item_img">' +
                                    '<div class="operate">' +
                                    '<i class="upload_img_close layui-icon" file_url_data="' + res.data + '" node="'+id+'"></i>' +
                                    '</div>' +
                                    '<img src="' + res.data + '" class="img" >' +
                                    '</li>'
                                );
                            }else if(accept == 'video'){
                                $('#preview_' + id).html(
                                    '<li class="item_video">' +
                                    '<video src="' + res.data + '" controls="controls" width="200px" height="200px"></video>' +
                                    '<button class="layui-btn layui-btn-sm layui-btn-danger upload_delete" style="display: block; width: 100%;" file_url_data="' + res.data + '" node="'+id+'"><i class="layui-icon">&#xe640;</i></button>' +
                                    '</li>'
                                );
                            }else if(accept == 'audio'){
                                $('#preview_' + id).html(
                                    '<li class="item_audio">' +
                                    '<audio src="' + res.data + '" controls="controls" style="height:54px;"></audio>' +
                                    '<button class="layui-btn layui-btn-sm layui-btn-danger upload_delete" style="display: block; width: 100%;" file_url_data="' + res.data + '" node="'+id+'"><i class="layui-icon">&#xe640;</i></button>' +
                                    '</li>'
                                );
                            }
                            //隐藏input框增加文件值
                            $('#input_'+id).val( res.data );
                        }
                        return layTp.facade.success(res.msg);
                    }
                });
                //删除已经上传的东西
                $("body").on("click", ".upload_img_close, .upload_delete", function () {
                    let node = $(this).attr('node');
                    let single_multi_val = $('#'+node).attr('single_multi');
                    let is_multiple_val = ( single_multi_val == 'multi' ) ? true : false;
                    if(is_multiple_val){
                        let file_url_value = $(this).attr('file_url_data');
                        let input_value = $('#input_'+node).val();
                        let new_input_value = "";
                        if( input_value.indexOf(file_url_value+',') != -1 ){
                            let reg = new RegExp(file_url_value + ',');
                            new_input_value = input_value.replace(reg, "");
                        }else{
                            if( input_value.indexOf(',' + file_url_value) != -1 ){
                                let reg = new RegExp(','+file_url_value);
                                new_input_value = input_value.replace(reg, "");
                            }else{
                                let reg = new RegExp(file_url_value);
                                new_input_value = input_value.replace(reg, "");
                            }
                        }
                        $('#input_'+node).val(new_input_value);
                    }else{
                        $('#input_'+node).val('');
                    }
                    $(this).closest("li").remove();
                });
            });
        },

        /**
         * 编辑器
         */
        editor_render: function(){
            //渲染UEditor
            layui.each($("script[editor='true']"),function(key,item) {
                let id = $(item).attr('id');
                UE.getEditor(id, {zIndex: 0});
            });
            //渲染layEditor
            layTp.facade.layEditor({
                elem:'.layeditor'
            });
            //渲染laydocEditor
            layTp.facade.layDocEditor({
                elem:'.laydoceditor'
            });
        },

        /**
         * 联动下拉框
         */
        linkage: function(){
            layui.each($("select[linkage='true']"),function(key,item) {
                let id = $(item).attr('id');//id属性，必须
                let table_name = $(item).attr('table_name');//要搜索的表名，必须
                let search_field = $(item).attr('search_field');//搜索的字段，默认是pid
                search_field = ( search_field  == "" || search_field == undefined ) ? 'pid' : search_field;//当搜索的字段没有设置值，或者属性不存在时，设置默认值为pid
                let show_field = $(item).attr('show_field');//显示的字段，默认是name
                show_field = ( show_field  == "" || show_field == undefined ) ? 'pid' : show_field;//当显示的字段没有设置值，或者属性不存在时，设置默认值为name
                let selected_val = $(item).attr('selected_val');//选中的值，非必填
                let search_field_val = $(item).attr('search_field_val');//搜索字段的值，默认0,如果只想选某个省下面的城市和地区可以设置这个值
                let left_field = $(item).attr('left_field');//左关联字段，为空或者不设置时，表示第一个下拉框
                let right_field = $(item).attr('right_field');//右关联字段，为空或者不设置时，表示最后一个下拉框
                let ajax_url = 'admin/ajax/linkage';
                //填充联动的第一个下拉框数据
                if(left_field == "" || left_field == undefined){
                    $.ajax({
                        type: 'GET',
                        url: layTp.facade.url(ajax_url),
                        data: {table_name:table_name,search_field:search_field,search_field_val:search_field_val,show_field:show_field},
                        dataType: 'json',
                        success: function (res) {
                            let option_1 = $(item).children().first().prop("outerHTML");
                            $(item).empty();
                            $(item).append(option_1);
                            let option_html;
                            let key;
                            for(key in res.data){
                                if(selected_val == res.data[key]['id']){
                                    option_html = '<option value="'+res.data[key]['id']+'" selected="selected">'+res.data[key]['name']+'</option>';
                                }else{
                                    option_html = '<option value="'+res.data[key]['id']+'">'+res.data[key]['name']+'</option>';
                                }

                                $(item).append(option_html);
                            }

                            layui.form.render('select');
                        }
                    });
                }
                //监听所有下拉框onchange事件
                if(right_field){
                    layui.form.on('select('+id+')',function(data){
                        $.ajax({
                            type: 'GET',
                            url: layTp.facade.url(ajax_url),
                            data: {table_name:table_name,search_field:search_field,search_field_val:data.value,show_field:show_field},
                            dataType: 'json',
                            success: function (res) {
                                let option_1 = $('#'+right_field).children().first().prop("outerHTML");
                                $('#'+right_field).empty();
                                $('#'+right_field).append(option_1);
                                let option_html;
                                let key;
                                for(key in res.data){
                                    option_html = '<option value="'+res.data[key]['id']+'">'+res.data[key]['name']+'</option>';
                                    $('#'+right_field).append(option_html);
                                }

                                let next_right_field = $('#'+right_field).attr('right_field');
                                let next_option_1 = "";
                                while(next_right_field != "" && next_right_field != undefined){
                                    next_option_1 = $('#'+next_right_field).children().first().prop("outerHTML");
                                    $('#'+next_right_field).empty();
                                    $('#'+next_right_field).append(next_option_1);
                                    next_right_field = $('#'+next_right_field).attr('right_field');
                                }
                                layui.form.render('select');
                            }
                        });
                    });
                }
                //如果有选中值，就请求渲染右侧下拉框
                if(selected_val != "" && selected_val != undefined){
                    if(right_field != "" && right_field != undefined){
                        $.ajax({
                            type: 'GET',
                            url: layTp.facade.url(ajax_url),
                            data: {table_name:table_name,search_field:search_field,search_field_val:selected_val,show_field:show_field},
                            dataType: 'json',
                            success: function (res) {
                                let option_1 = $('#'+right_field).children().first().prop("outerHTML");
                                $('#'+right_field).empty();
                                $('#'+right_field).append(option_1);
                                let option_html;
                                let key;
                                let right_selected_val = $('#'+right_field).attr('selected_val');
                                for(key in res.data){
                                    if(right_selected_val == res.data[key]['id']){
                                        option_html = '<option value="'+res.data[key]['id']+'" selected="selected">'+res.data[key]['name']+'</option>';
                                    }else{
                                        option_html = '<option value="'+res.data[key]['id']+'">'+res.data[key]['name']+'</option>';
                                    }

                                    $('#'+right_field).append(option_html);
                                }
                                layui.form.render('select');
                            }
                        });
                    }
                }
            });
        },

        /**
         * 下载文件
         */
        download_file:function(){
            $(document).on('click','[download]',function(){
                let file_url = $(this).attr('download');
                window.open(layTp.facade.url('/admin/ajax/download',{'file_url':window.btoa(file_url)}));
            });
        },

        /**
         * selectPage插件渲染
         */
        select_page:function(){
            layui.each($(".selectPage"),function(key,item) {
                let showField = $(item).attr('show-field');
                let searchField = $(item).attr('search-field');
                let search_url = $(item).attr('search-url');
                let multiple = ( $(item).attr('show-multiple') == 'true' ) ? true : false;
                let maxSelectLimit = $(item).attr('maxSelectLimit') ? parseInt($(item).attr('maxSelectLimit')) : 0;
                layTp.facade.select_page($(item),{
                    showField : showField,
                    keyField: 'id',
                    searchField : searchField,
                    multiple : multiple,
                    data : search_url,
                    maxSelectLimit : maxSelectLimit,
                    eAjaxSuccess : function(d){
                        var result;
                        if(d) result = d;
                        else result = undefined;
                        return result;
                    }
                });
            });
        },

        //表格拖动排序渲染
        tablednd:function(){
            layui.each($(".tableDnd"),function(key,item) {
                $(item).tableDnD({});
            });
        },

        //tips渲染
        tips:function(){
            $(document).on('mouseover','[layer-tips]',function(){
                let obj = $(this);
                let colour = (typeof obj.attr('colour') != 'undefined') ? obj.attr('colour') : '#3595CC';
                let time = (typeof obj.attr('time') != 'undefined') ? obj.attr('time') : 800;
                layui.layer.tips($(this).attr('layer-tips'), this, {
                    tips: [1, colour],
                    time: time
                });
            });
        },

        //点击顶部Tab切换，触发搜索表单
        click_search:function(){
            $(document).on('click','[click_search]',function(){
                let obj = $(this);
                let field = obj.attr('field');
                let field_val = obj.attr('field_val');
                //搜索框的值设置成对应值
                $('#'+field).val(field_val);
                layui.form.render('select');
                //顶部如果有item选项，就选中
                let tag_name = $(this).prop("tagName");
                if( tag_name == 'SPAN' ){
                    let li_field = '';
                    let li_field_val = '';
                    layui.each($("li[click_search='true']"),function(key,item) {
                        li_field = $(item).attr('field');
                        li_field_val = $(item).attr('field_val');
                        if(li_field == field){
                            if( li_field_val != field_val ){
                                $(item).removeClass('layui-this');
                            }else{
                                $(item).addClass('layui-this');
                            }
                        }
                    });
                }
                $('[lay-submit]').click();
            });
        },

        //laytp列表单选按钮粗发js
        laytp_switch:function(){
            layui.form.on('switch(laytp_switch)', function(obj){
                let open_value = obj.elem.attributes['open_value'].nodeValue;
                let close_value = obj.elem.attributes['close_value'].nodeValue;
                let field = obj.elem.attributes['name'].nodeValue;
                let id_val = obj.elem.attributes['id_val'].nodeValue;
                let post_data = {};
                if(obj.elem.checked){
                    post_data = {field:this.name,field_val:open_value, ids: id_val};
                }else{
                    post_data = {field:this.name,field_val:close_value, ids: id_val};
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
        },

        //清除缓存列表操作
        clear_cache: function(){
            //批量操作渲染
            layui.dropdown.render({
                elem: '#laytp_clear_cache',
                options: [
                    {
                        action: "edit"
                        ,title: "一键清除缓存"
                        ,icon: ""
                        ,uri: layTp.facade.url("admin/ajax/clear_cache")
                        ,switch_type: "confirm_action"
                        ,need_data:false
                    }
                ]
            });
        },

        //锁屏
        lock_screen: function(){
            let lock_screen_status = localStorage.getItem("laytp_lock_screen");
            if( lock_screen_status == "locked" ){
                layui.layer.open({
                    type : 2,
                    title : '锁屏',
                    content : layTp.facade.url("admin/ajax/lock_screen"),
                    area: ['50%','25%'],
                    btn: [],//不加这个，全屏高度不会变化
                    shade: 1,
                    maxmin:true,
                    skin:'hide-layer-box-shadow',
                    closeBtn:0
                });
            }

            $(document).on('click','#lock_screen',function(){
                layui.layer.confirm('锁屏后，需要使用登录密码解锁，确定锁屏么?', function(index){
                    localStorage.setItem("laytp_lock_screen", "locked");
                    layui.layer.open({
                        type : 2,
                        title : '锁屏',
                        content : layTp.facade.url("admin/ajax/lock_screen"),
                        area: ['50%','25%'],
                        btn: [],//不加这个，全屏高度不会变化
                        shade: 1,
                        maxmin:true,
                        skin:'hide-layer-box-shadow',
                        closeBtn:0
                    });
                });
            });
        },

        //颜色选择器
        colorpicker:function(){
            layui.each($("[colorpicker='true']"),function(key,item) {
                let id = $(item).attr('id');//id属性，必须
                let color = $(item).val() ? $(item).val() : '#1c97f5';//id属性，必须
                layui.colorpicker.render({
                    elem: '#' + id + '-div'
                    ,color: color
                    ,done: function(color){
                        $('#'+ id).val(color);
                    }
                });
            });
        },

        //监听表格排序
        table_sort:function(){
            layui.table.on('sort(default)', function(obj){
                var where = layui.form.val("laytp_search_form");
                where['order_param[field]'] = obj.field;
                where['order_param[type]'] = obj.type;
                layui.table.reload(table_id, {
                    initSort: obj
                    ,where: where
                });
            });
        },

        //菜单跳转
        menu:function(){
            $(document).on('click','[menuto]',function(){
                let obj = $(this);
                let rule = obj.attr('rule');
                let menu_id = obj.attr('menu_id');
                let selected_menu_ids = obj.attr('select_menu_ids');
                let index = obj.attr('index');
                $('#layTpIframe').attr('src',__URL__ + rule + '/laytp_menu_id/' + menu_id);
                editHistory(default_menu.name,__URL__ + rule + '?ref=' + menu_id);
                $.post(__URL__ + 'admin/ajax/get_crumbs',{menu_id:menu_id},function(res){
                    if(res.code==1){
                        let html = '';
                        for(let key in res.data){
                            html += '<li>' + res.data[key] + '</li>';
                        }
                        $('.bread-crumbs').html(html);
                    }
                });
                let data = menu_json[index].children;
                select_menu_ids = selected_menu_ids.split(',');
                createMenu(data,0,true);
                layui.laytp_element.init();
            });
        }
    }

    layTp.init_render = function(){
        layui.each(layTp.init, function(key,item){
            if(typeof item == "function"){
                item();
            }
        });
    }

    layTp.init_render();

    //输出模块
    exports(MOD_NAME, layTp);
});
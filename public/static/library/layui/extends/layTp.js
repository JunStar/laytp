/*
* @version: 1.0
* @Author:  JunStar
* @Date:    2018年09月20日11:47:00
* @Last Modified by:   JunStar
* @Last Modified time: 2018年09月20日11:47:00
*/
let add_search_condition_click_num = 0;
layui.define([
    'jquery', 'layer', 'form', 'table', 'laytpl', 'element','laydate','upload','selectPage'
    ,'select_multi'
    ,'formSelects'
], function(exports){
    const MOD_NAME = 'layTp';
    let layTp = {};
    const $ = layTp.$ = layui.jquery;
    const selectPagePlugin = layui.selectPage;
    //toastr加入layui的jquery中
    $.extend({
        toastr: {
            // 惯例配置
            options: {
                time: 3000, // 关闭时间(毫秒)
                position: 'top-right', // 位置 ['top-left', 'top-center', 'top-right', 'right-bottom', 'bottom-center', 'left-bottom']
                size: '', // 大小 ['lg', 'sm', 'xs']
                callback: function () { // 默认关闭后的回调
                }
            },
            /**
             * 设置配置
             * @param options
             */
            config: function (options) {
                Object.keys(options).forEach(function (key) {
                    this.options[key] = options[key];
                }.bind(this));
            },
            /**
             * Toastr容器
             * @param position  位置
             * @returns {jQuery|HTMLElement}
             */
            container: function (position) {
                position = position ? position : this.options.position;
                var container = $('body .toastr-container');
                if (!container.hasClass(position)) {
                    $('body').append('<div class="toastr-container ' + position + '"><ul></ul></div>');
                }
                return $('body .toastr-container.' + position);
            },
            /**
             * 创建Toastr
             * @param type    类型
             * @param msg     信息
             * @param options 参数
             */
            create: function (type, msg, options) {
                var self = this;
                msg = msg || 'null';
                options = options || {};
                var time = options.time ? options.time : this.options.time,
                    position = options.position ? options.position : this.options.position,
                    size = options.size ? options.size : this.options.size,
                    callback = options.callback ? options.callback : this.options.callback;

                // 开始和结束动画
                var fades = {
                    'top-left': {fadeIn: 'left', fadeOut: 'left'},
                    'top-center': {fadeIn: 'top', fadeOut: 'bottom'},
                    'top-right': {fadeIn: 'right', fadeOut: 'right'},
                    'right-bottom': {fadeIn: 'right', fadeOut: 'right'},
                    'bottom-center': {fadeIn: 'top', fadeOut: 'bottom'},
                    'left-bottom': {fadeIn: 'left', fadeOut: 'left'}
                }, id = 'toastr-' + new Date().getTime();

                this.container(position).find('> ul').prepend('<li class="' + size + ' fade-in-' + fades[position].fadeIn + ' ' + id + ' toastr-' + type + '">' + msg + '</li>');

                // 定时关闭
                var li = this.container(position).find('.' + id), fadeOut = 'fade-out-' + fades[position].fadeOut,
                    timer = setTimeout(function () {
                        clearTimeout(timer);
                        li.unbind('click');
                        self.close(li, fadeOut, callback);
                    }, time);

                // 绑定单击事件关闭
                li.click(function () {
                    clearTimeout(timer);
                    self.close(li, fadeOut, callback);
                });
            },
            /**
             * 关闭Toastr
             * @param li        li元素
             * @param fadeOut   关闭动画
             * @param callback  关闭后的回调
             */
            close: function (li, fadeOut, callback) {
                li.addClass(fadeOut);
                setTimeout(function () {
                    li.remove();
                }, 300);
                // 执行关闭回调
                callback();
                setTimeout(function () {
                    // 清除空容器
                    $('body .toastr-container').each(function (i, v) {
                        if ($(v).find('ul li').length <= 0) {
                            $(v).remove();
                        }
                    });
                }, 500);
            },
            /**
             * 清除所有Toastr
             */
            clear: function () {
                var container = $('body .toastr-container');
                container.length >= 0 && container.fadeOut(1000);
                setTimeout(function () {
                    container.remove();
                }, 2000);
            },
            success: function (msg, options) {
                this.create('success', msg, options);
            },
            info: function (msg, options) {
                this.create('info', msg, options);
            },
            warning: function (msg, options) {
                this.create('warning', msg, options)
            },
            error: function (msg, options) {
                this.create('error', msg, options);
            }
        }
    });

    const default_popup_frame_width = '60%';
    const default_popup_frame_height = '70%';

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
                if(!layui.form.config.verify.number[0].test(value)){
                    return layui.form.config.verify.number[1];
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

        //跳转页面
        redirect: function(url){
            location.href = url;
        },

        //组装成url
        url: function(path, params){
            if( typeof params !== 'undefined' ){
                params = $.param(params);
                params = params.replace('=','/');
                params = params.replace('&','/');
                params = '/' + params;
            }else{
                params = '';
            }

            path = '/' + path.replace(/(^\/)|(\/$)/,'');

            var url = path + params + '.html';
            return url;
        },

        //layer弹窗iFrame
        popup_frame: function(title, url, width, height){
            if(width == undefined) {
                width = default_popup_frame_width;
            }
            if(height == undefined) {
                height = default_popup_frame_height;
            }
            layui.layer.open({
                type : 2,
                title : title,
                content : url,
                area: [width,height],
                shade: 0.01,
                success: function(layero, index){
                    layTp.facade.after_popup_frame(layero,index);
                }
            });
        },

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
            // $.toastr.success(text);
            layui.layer.msg(text,{icon:1});
        },

        //layer提示操作失败
        error: function(text){
            // $.toastr.error(text);
            layui.layer.msg(text,{icon:2});
        },

        //表格点击编辑删除按钮
        table_tool: function(obj){
            let data = obj.data;
            if(obj.event === 'del'){
                layui.layer.confirm('真的删除么?', function(index){
                    $.ajax({
                        type: 'POST',
                        url: layTp.facade.url(module + '/' + controller +'/del'),
                        data: {id:data.id},
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
            //点击编辑按钮
            }else if(obj.event === 'edit'){
                let url = layTp.facade.url(module + '/' + controller + '/edit',{id:data.id});
                layTp.facade.popup_frame('编辑', url);
            }
        },

        //select_page
        select_page:function(obj, option){
            selectPagePlugin.selectPage(obj, option);
        },

        //多选下拉框
        //layero,index有值时，说明是layer弹窗中进行渲染，添加和编辑都是layer弹窗
        //layero,index无值时，说明是非layer弹窗中进行渲染，列表页搜索表单部分使用到了
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
        }
    }

    layTp.init = {
        //jquery ajax set
        ajaxSet: function(){
            $.ajaxSetup({
                "async": false
            });
        },

        /**
         * 监听批量操作下拉框
         */
        batch_select_action: function(){
            layui.form.on('select(batch)',function(data){
                let uri = $(data.elem).find("option:selected").attr("uri");
                if(uri == undefined){
                    return false;
                }
                let field = $(data.elem).find("option:selected").attr("field");
                let field_val = $(data.elem).find("option:selected").attr("field_val");
                let text = $(data.elem).find("option:selected").text();
                let switch_type = $(data.elem).find("option:selected").attr("switch_type");
                let checkStatus = layui.table.checkStatus(table_id);
                let checkData = checkStatus.data;
                if(checkData.length == 0){
                    layTp.facade.error('请选择数据');
                    return false;
                }
                let key;
                switch(switch_type){
                    case 'popup_frame':
                        for(key in checkData){
                            url = layTp.facade.url(uri,{id:checkData[key].id});
                            layTp.facade.popup_frame(name, url);
                        }
                        break;
                    case 'confirm_action':
                        layui.layer.confirm('确定'+text+'么?', function(index){
                            let ids_arr = [];
                            for(key in checkData){
                                ids_arr.push(checkData[key].id);
                            }
                            let ids = ids_arr.join(',');
                            let data = {'id':ids,'field':field,'field_val':field_val};
                            $.ajax({
                                type: 'POST',
                                url: layTp.facade.url(uri),
                                data: data,
                                dataType: 'json',
                                success: function (res) {
                                    if( res.code == 1 ){
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
                        break;
                }
                // let field_name = $('#'+data.elem.id).data('field_name');
                // let value = data.elem.value;
                // func_controller.form_type_select_after(field_name, value);
                // layui.form.render('select');
                // return true;
            });
        },

        /**
         * 批量弹窗 - 使用例子：数据表格顶部的编辑按钮
         * 所有拥有popup-frame的类名的节点，点击后都会弹窗
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
         *  width属性为弹窗的宽度百分比
         *  height属性为弹窗的高度百分比
         */
        popup_frame: function(){
            $(document).on('click','.popup-frame',function(){
                let name = $(this).data("name") ? $(this).data("name") : '添加';
                let url = $(this).data("open");
                let width = $(this).data("width");
                let height = $(this).data("height");
                if( !width ){
                    width = default_popup_frame_width;
                }
                if( !height ){
                    height = default_popup_frame_height;
                }
                layTp.facade.popup_frame(name, url, width, height);
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
         *  <button class="layui-btn layui-btn-sm" lay-submit lay-filter="*">立即提交</button>
         * </form>
         * 重要的是
         *  1.form的class值为layui-form;
         *  2.button的属性值lay-submit lay-filter="*";
         */
        form: function(){
            layui.form.on('submit(*)', function(data){
                /**
                 * 所有的表单提交都是执行ajax，ajax请求的地址都是当前的url
                 * 列表页的搜索表单要使用到layui的table控件进行ajax提交，其他表单使用jQuery的ajax提交方式
                 */
                //当前url的action值为index，搜索表单进行了提交
                if( action == 'index' ){
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
                    func_controller.table_render(data.field);
                }else{
                    do_update(data);
                }
            }

            //添加和编辑的表单进行提交时触发的方法
            function do_update(data){
                $.ajax({
                    type: 'POST',
                    url: window.location.href,
                    data: data.field,
                    dataType: 'json',
                    success: function (res) {
                        if( res.code == 1 ){
                            layTp.facade.success(res.msg);
                            if(res.data.reload){
                                setTimeout(function () {
                                    location.reload();
                                }, 1000);
                            }
                            if(typeof parent.func_controller != "undefined"){
                                parent.func_controller.table_render();
                                parent.layui.layer.closeAll();
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
         * 搜索表单，添加搜索条件绑定事件
         */
        add_search_condition: function(){
            $(document).on('click','.add_search_condition',function(){
                let search_condition_tpl = $('#search_condition_tpl').html();
                add_search_condition_click_num = add_search_condition_click_num + 1;
                layui.laytpl(search_condition_tpl).render(add_search_condition_click_num, function(html){
                    $('form > div').append(html);
                    layui.form.render();
                });
            });
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
                layui.upload.render({
                    elem: elem,
                    url: layTp.facade.url('/admin/ajax/upload'),
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
                            return layer.msg(res.msg + ":" + res.data.data);
                        }
                        if(is_multiple){
                            //预览
                            if(accept == 'images') {
                                $('#preview_' + id).append(
                                    '<li class="item_img">' +
                                    '<div class="operate">' +
                                    '<i class="upload_img_close layui-icon" file_url_data="' + res.data.data + '" node="'+id+'"></i>' +
                                    '</div>' +
                                    '<img src="' + res.data.data + '" class="img" >' +
                                    '</li>'
                                );
                            }else if(accept == 'video'){
                                $('#preview_' + id).append(
                                    '<li class="item_video">' +
                                    '<video src="' + res.data.data + '" controls="controls" width="200px" height="200px"></video>' +
                                    '<button class="layui-btn layui-btn-sm layui-btn-danger upload_delete" style="display: block; width: 100%px;" file_url_data="' + res.data.data + '" node="'+id+'"><i class="layui-icon">&#xe640;</i></button>' +
                                    '</li>'
                                );
                            }else if(accept == 'audio'){
                                $('#preview_' + id).append(
                                    '<li class="item_audio">' +
                                    '<audio src="' + res.data.data + '" controls="controls" style="height:54px;"></audio>' +
                                    '<button class="layui-btn layui-btn-sm layui-btn-danger upload_delete" style="display: block; width: 100%;" file_url_data="' + res.data.data + '" node="'+id+'"><i class="layui-icon">&#xe640;</i></button>' +
                                    '</li>'
                                );
                            }
                            //隐藏input框增加文件值
                            let input_value = $('#input_'+id).val();
                            if(input_value){
                                $('#input_'+id).val( input_value + ',' + res.data.data );
                            }else{
                                $('#input_'+id).val( res.data.data );
                            }
                        }else{
                            //预览
                            if(accept == 'images') {
                                $('#preview_' + id).html(
                                    '<li class="item_img">' +
                                    '<div class="operate">' +
                                    '<i class="upload_img_close layui-icon" file_url_data="' + res.data.data + '"></i>' +
                                    '</div>' +
                                    '<img src="' + res.data.data + '" class="img" >' +
                                    '</li>'
                                );
                            }else if(accept == 'video'){
                                $('#preview_' + id).html(
                                    '<li class="item_video">' +
                                    '<video src="' + res.data.data + '" controls="controls"></video>' +
                                    '<button class="layui-btn layui-btn-sm layui-btn-danger upload_delete" style="display: block; width: 100%;" file_url_data="' + res.data.data + '" node="'+id+'"><i class="layui-icon">&#xe640;</i></button>' +
                                    '</li>'
                                );
                            }else if(accept == 'audio'){
                                $('#preview_' + id).html(
                                    '<li class="item_audio">' +
                                    '<audio src="' + res.data.data + '" controls="controls" style="height:54px;"></audio>' +
                                    '<button class="layui-btn layui-btn-sm layui-btn-danger upload_delete" style="display: block; width: 100%;" file_url_data="' + res.data.data + '" node="'+id+'"><i class="layui-icon">&#xe640;</i></button>' +
                                    '</li>'
                                );
                            }
                            //隐藏input框增加文件值
                            $('#input_'+id).val( res.data.data );
                        }
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
         * 百度编辑器
         */
        ueditor_render: function(){
            layui.each($("script[editor='true']"),function(key,item) {
                let id = $(item).attr('id');
                UE.getEditor(id);
            });
        },

        /**
         * 省市区联动下拉框渲染
         */
        area_render: function(){
            layui.each($("select[linkage_area='true']"),function(key,item) {
                let id = $(item).attr('id');
                let is_province = $(item).attr('is-province');
                let ajax_url = $(item).attr('ajax-url');
                let selected_id = $(item).attr('selected-id');
                let change_linkage_id = $(item).attr('change-linkage-id');
                let parent_id = $(item).attr('parent-id');
                if(is_province=='true'){
                    $.ajax({
                        type: 'GET',
                        url: layTp.facade.url(ajax_url),
                        data: {parent_id:0},
                        dataType: 'json',
                        success: function (res) {
                            let option_1 = $(item).children().first().prop("outerHTML");
                            $(item).empty();
                            $(item).append(option_1);
                            let option_html;
                            let key;
                            for(key in res.data){
                                if(selected_id == res.data[key]['id']){
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

                if(change_linkage_id){
                    layui.form.on('select('+id+')',function(data){
                        $.ajax({
                            type: 'GET',
                            url: layTp.facade.url(ajax_url),
                            data: {parent_id:data.value},
                            dataType: 'json',
                            success: function (res) {
                                let option_1 = $('#'+change_linkage_id).children().first().prop("outerHTML");
                                $('#'+change_linkage_id).empty();
                                $('#'+change_linkage_id).append(option_1);
                                let option_html;
                                let key;
                                for(key in res.data){
                                    option_html = '<option value="'+res.data[key]['id']+'">'+res.data[key]['name']+'</option>';
                                    $('#'+change_linkage_id).append(option_html);
                                }
                                let city_change_linkage_id = $('#'+change_linkage_id).attr('change-linkage-id');
                                if( city_change_linkage_id ){
                                    let county_option_1 = $('#'+city_change_linkage_id).children().first().prop("outerHTML");
                                    $('#'+city_change_linkage_id).empty();
                                    $('#'+city_change_linkage_id).append(county_option_1);
                                }
                                layui.form.render('select');
                            }
                        });
                    });
                }

                if(parent_id > 0){
                    $.ajax({
                        type: 'GET',
                        url: layTp.facade.url(ajax_url),
                        data: {parent_id:parent_id},
                        dataType: 'json',
                        success: function (res) {
                            let option_1 = $(item).children().first().prop("outerHTML");
                            $(item).empty();
                            $(item).append(option_1);
                            let option_html;
                            let key;
                            for(key in res.data){
                                if(selected_id == res.data[key]['id']){
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
        }
    }

    layui.each(layTp.init, function(key,item){
        if(typeof item == "function"){
            item();
        }
    });

    //输出模块
    exports(MOD_NAME, layTp);
});
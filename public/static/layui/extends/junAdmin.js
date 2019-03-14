/*
* @version: 1.0
* @Author:  JunStar
* @Date:    2018年09月20日11:47:00
* @Last Modified by:   JunStar
* @Last Modified time: 2018年09月20日11:47:00
*/
let add_search_condition_click_num = 0;
layui.define([
    'jquery', 'layer', 'form', 'table', 'laytpl', 'element','laydate'
    ,'select_multi'
    ,'formSelects'
], function(exports){
    const MOD_NAME = 'junAdmin';
    let junAdmin = {};
    const $ = junAdmin.$ = layui.jquery;

    const default_popup_frame_width = '60%';
    const default_popup_frame_height = '70%';

    //自定义表单验证器
    layui.form.verify({
        junAdmin_email:function(value){
            if(value.length > 0){
                if(!layui.form.config.verify.email[0].test(value)){
                    return layui.form.config.verify.email[1];
                }
            }
        },
        junAdmin_phone:function(value){
            if(value.length > 0){
                if(!layui.form.config.verify.phone[0].test(value)){
                    return layui.form.config.verify.phone[1];
                }
            }
        },
        junAdmin_number:function(value){
            if(value.length > 0){
                if(!layui.form.config.verify.number[0].test(value)){
                    return layui.form.config.verify.number[1];
                }
            }
        },
        junAdmin_url:function(value){
            if(value.length > 0){
                if(!layui.form.config.verify.url[0].test(value)){
                    return layui.form.config.verify.url[1];
                }
            }
        },
        junAdmin_identity:function(value){
            if(value.length > 0){
                if(!layui.form.config.verify.identity[0].test(value)){
                    return layui.form.config.verify.identity[1];
                }
            }
        },
    });

    junAdmin.facade = {
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
                success: function(layero, index){
                    junAdmin.facade.after_popup_frame(layero,index);
                }
            });
        },

        after_popup_frame: function(layero,index){
            junAdmin.facade.select_multi(layero,index);
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

        //表格点击编辑删除按钮
        table_tool: function(obj){
            let data = obj.data;
            if(obj.event === 'del'){
                layui.layer.confirm('真的删除么?', function(index){
                    $.ajax({
                        type: 'POST',
                        url: junAdmin.facade.url(module + '/' + controller +'/del'),
                        data: {id:data.id},
                        dataType: 'json',
                        success: function (res) {
                            if( res.code == 1 ){
                                obj.del();
                            }else{
                                junAdmin.facade.error(res.msg);
                            }
                            layui.layer.close(index);
                        },
                        error: function (xhr) {
                            if( xhr.status == '500' ){
                                junAdmin.facade.error('本地网络问题或者服务器错误');
                            }else if( xhr.status == '404' ){
                                junAdmin.facade.error('请求地址不存在');
                            }
                        }
                    });
                });
            //点击编辑按钮
            }else if(obj.event === 'edit'){
                let url = junAdmin.facade.url(module + '/' + controller + '/edit',{id:data.id});
                junAdmin.facade.popup_frame('编辑', url);
            }
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
        },

        //时间插件
        laydate_render:function(){
            layui.each($('.laydate'),function(key,item){
                let elem = '#'+$(item).attr('id');
                let laydate_type = $(item).attr('laydate_type');
                layui.laydate.render({
                    elem: elem //指定元素
                    ,type: laydate_type
                });
            });
        }

        //表格插件

        //其他插件,等等...


    }

    junAdmin.init = {
        popup_frame: function(){
            $(document).on('click','.popup-frame',function(){
                var name = $(this).data("name") ? $(this).data("name") : '添加';
                var url = $(this).data("open");
                var width = $(this).data("width");
                var height = $(this).data("height");
                if( !width ){
                    width = default_popup_frame_width;
                }
                if( !height ){
                    height = default_popup_frame_height;
                }
                junAdmin.facade.popup_frame(name, url, width, height);
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
                        junAdmin.facade.popup_div(title, html, '99%', '98%');
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
                func_controller.table_render(data.field);
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
                            junAdmin.facade.success(res.msg);
                            parent.func_controller.table_render();
                            parent.layui.layer.closeAll();
                        }else{
                            junAdmin.facade.error(res.msg);
                        }
                    },
                    error: function (xhr) {
                        if( xhr.status == '500' ){
                            junAdmin.facade.error('本地网络问题或者服务器错误');
                        }else if( xhr.status == '404' ){
                            junAdmin.facade.error('请求地址不存在');
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
        laydate_render:function(){
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
        }
    }

    layui.each(junAdmin.init, function(key,item){
        if(typeof item == "function"){
            item();
        }
    });

    //输出模块
    exports(MOD_NAME, junAdmin);
});
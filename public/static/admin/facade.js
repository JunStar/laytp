/* 助手函数，借ThinkPHP助手函数概念，这里定义一些能够使用到的函数 */
if (typeof layui !== 'undefined') {
    var form = layui.form,
        layer = layui.layer,
        laydate = layui.laydate,
        laytpl = layui.laytpl,
        table = layui.table
    ;
    if (typeof jQuery === 'undefined') {
        var $ = jQuery = layui.$;
    }
}

var pop_select_input = "";

(function func_facade() {
    window.facade = {};  //全局对象

    var main = {};

    //跳转页面
    main.redirect = function(url){
        location.href = url;
    }

    //组装成url
    main.url = function(path, params){
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
    }

    //layer弹窗iFrame
    main.popup_frame = function(title, url, width, height){
        if(width == undefined) {
            width = '500px';
        }
        if(height == undefined) {
            height = '220px';
        }
        layer.open({
            type : 2,
            title : title,
            content : url,
            area: [width,height],
        });
    }

    //layer弹出层
    main.popup_div = function(title, content, width, height){
        if(width == undefined) {
            width = '500px';
        }
        if(height == undefined) {
            height = '220px';
        }
        layer.open({
            type : 1,
            title : title,
            content : content,
            area: [width,height],
        });
    }

    //layer提示操作成功
    main.success = function(text){
        layer.msg(text,{icon:1});
    }

    //layer提示操作失败
    main.error = function(text){
        layer.msg(text,{icon:2});
    }

    //时间插件

    //表格插件

    //其他插件,等等...

    window.facade = main;
})()
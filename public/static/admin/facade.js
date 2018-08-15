/* 助手函数，借ThinkPHP助手函数概念，这里定义一些能够使用到的函数 */
if (typeof layui !== 'undefined') {
    var form = layui.form,
        layer = layui.layer,
        laydate = layui.laydate;
    if (typeof jQuery === 'undefined') {
        var $ = jQuery = layui.$;
    }
}

(function func_facade() {
    window.facade = {};  //全局对象

    var main = {};

    //跳转页面
    main.redirect = function(url){
        location.href = url;
    }

    //组装成url
    main.url = function(path, params){
        params = $.param(params);
        params = params.replace('=','/');
        params = params.replace('&','/');
        return path + '/' + params + '.html';
    }

    //layer弹窗iFrame
    main.popup = function(title, url, width, height){
        if(width == undefined) {
            width = 500;
        }
        if(height == undefined) {
            height = 220;
        }
        layer.open({
            type : 2,
            title : title,
            content : url,
            area: [width+'px',height+'px'],
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
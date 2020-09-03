/**
 * 上传组件，基于layui.upload组件再次封装，html代码<div class="layTpUpload" data-id="id" data-name="name"></div>渲染成一个上传组件
 *  - 允许上传图片、音频、视频和任意文件
 *  - input输入框的展示访问上传文件的url地址
 *  - 图片、音频、视频的预览和删除
 * @version: 1.0
 * @Author:  JunStar
 * @Date:    2020-09-03 11:33:43
 * @Last Modified by:   JunStar
 * @Last Modified time: 2020-09-03 11:33:48
 */
layui.define(["jquery"], function (exports) {
    const MOD_NAME = "layTpUpload";
    let $ = layui.$;

    let layTpUpload = {
        //展示input输入框和上传按钮的html内容
        uploadHtml: function (options) {
            let html =
                "<div class=\"layui-upload\">\n" +
                "   <div class=\"layui-inline\" style=\"width: 70%;\"><input type=\"text\" class=\"layui-input\" name=\"{{d.name}}\" id=\"input_{{d.id}}\" /></div>\n" +
                "   <div class=\"layui-inline\">\n" +
                "       <button type=\"button\" class=\"layui-btn layui-btn-sm layui-btn-primary pull-left\"\n" +
                "           <i class=\"layui-icon\">&#xe62f;</i> 点击上传\n" +
                "       </button>\n" +
                "   </div>\n" +
                "   {{d.previewHtml}}" +
                "</div>";
            return layui.laytpl(html).render(options);
        },

        //预览图片的html内容
        imagePreviewHtml: function (options) {
            let html =
                "<div class=\"pic-more\">\n" +
                "   <ul class=\"pic-more-upload-list\" id=\"preview_\"></ul>\n" +
                "</div>";
        },

        render: function (options) {
            // layui.each($("div[class='layTpUpload']"), function (key, item) {
                // options.id = $(item).data('id');
                // options.name = $(item).data('name');
                // options.accept = $(item).data('accept') ? $(item).data('accept') : "image";
                // options.is_multiple = $(item).data('is_multiple') === "true";
                // options.upload_dir = $(item).data('upload_dir') ? $(item).attr('upload_dir') : "";
                // options.upload_url = $(item).data('upload_url') ? $(item).data('upload_url') : facade.url("admin/common/upload", {
                //     'accept': options.accept,
                //     'upload_dir': options.upload_dir
                // });
            // });
            console.log(options);
        }
    };

    //输出模块
    exports(MOD_NAME, layTpUpload);

    //注入layui组件中，供全局调用
    layui.layTpUpload = layTpUpload;

    //注入window全局对象中，供全局调用
    window.layTpUpload = layTpUpload;
});
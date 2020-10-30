/**
 * 上传组件，基于layui.upload组件进行封装，html代码<div class="layTpUpload" data-id="id" data-name="name"></div>渲染成一个上传组件
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

    let layTpUpload = {
        splitStr: ", ",
        //展示input输入框和上传按钮的html内容
        uploadHtml: function (options) {
            let html =
                "<div class=\"layui-upload\">" +
                "   <div class=\"layui-inline\" style=\"width: " + options.inputWidth + ";\"><input type=\"text\" class=\"layui-input\" name=\"{{d.name}}\" id=\"input_{{d.name}}\" value='{{d.uploaded}}' lay-verify=\"{{d.layVerify}}\" lay-verType=\"{{d.layVerType}}\" /></div>" +
                "   <div class=\"layui-inline\">" +
                "       <a id='layTpUploadBtn_{{d.name}}' class=\"layui-btn layui-btn-default layui-btn-sm layui-icon layui-icon-upload-drag pull-left\">" +
                "            点击上传" +
                "       </a>" +
                "   </div>" +
                "   {{d.previewHtml}}" +
                "</div>";
            return layui.laytpl(html).render(options);
        },

        //预览图片的html内容
        imagePreviewHtml: function (options) {
            let html =
                "<div class=\"pic-more\">" +
                "   <ul class=\"pic-more-upload-list\" id=\"preview_{{d.name}}\">" +
                "   {{# let uploaded=d.uploaded; }}" +
                "   {{# if(typeof uploaded !== 'undefined' && uploaded){ let key,uploadedArr=uploaded.split(layTpUpload.splitStr); }}" +
                "   {{# for(key in uploadedArr){ }}" +
                "       <li class=\"item_img\">" +
                "           <div class=\"operate\">" +
                "               <i class=\"upload_img_close layui-icon\" data-file_url=\"{{uploadedArr[key]}}\" data-id=\"{{d.name}}\" data-parentElem=\"{{d.parentElem}}\"></i>" +
                "           </div>" +
                "           <img src=\"{{sysConf.upload.domain + uploadedArr[key]}}\" class=\"img\">" +
                "       </li>" +
                "   {{# } }}" +
                "   {{# } }}" +
                "   </ul>" +
                "</div>";
            return layui.laytpl(html).render(options);
        },

        //单个图片模板
        singleImageHtml: function (options) {
            let html =
                '<li class="item_img">' +
                '   <div class="operate">' +
                '       <i class="upload_img_close layui-icon" data-file_url="{{d.data}}" data-id="{{d.name}}" data-parentElem=\"{{d.parentElem}}\"></i>' +
                '   </div>' +
                '   <img src="{{sysConf.upload.domain + d.data}}" class="img" />' +
                '</li>';
            return layui.laytpl(html).render(options);
        },

        //单个视频模板
        singleVideoHtml: function (options) {
            let html =
                '<li class="item_video">' +
                '   <video src="{{sysConf.upload.domain + d.data}}" controls="controls" width="200px" height="200px"></video>' +
                '   <button class="layui-btn layui-btn-sm layui-btn-danger upload_delete" style="display: block; width: 100%;" data-file_url="{{d.data}}" data-id="{{d.name}}" data-parentElem=\"{{d.parentElem}}\"><i class="layui-icon">&#xe640;</i></button>' +
                '</li>';
            return layui.laytpl(html).render(options);
        },

        //单个音频模板
        singleAudioHtml: function (options) {
            let html =
                '<li class="item_audio">' +
                '   <audio src="{{sysConf.upload.domain + d.data}}" controls="controls" style="height:54px;"></audio>' +
                '   <button class="layui-btn layui-btn-sm layui-btn-danger upload_delete" style="display: block; width: 100%;" data-file_url="{{d.data}}" data-id="{{d.name}}" data-parentElem=\"{{d.parentElem}}\"><i class="layui-icon">&#xe640;</i></button>' +
                '</li>';
            return layui.laytpl(html).render(options);
        },

        render: function (options) {
            let splitStr = layTpUpload.splitStr;
            if (options.accept === "image") {
                options.previewHtml = layTpUpload.imagePreviewHtml(options);
            } else if (options.accept === "file") {
                options.previewHtml = "";
            }
            $(options.el).after(layTpUpload.uploadHtml(options));
            layui.upload.render({
                elem: $("#layTpUploadBtn_" + options.name, options.parentElem),
                url: options.url,
                accept: options.accept,
                multiple: options.multi,
                data: options.params,
                field: "layTpUploadFile",
                done: function (res) {
                    if (res.code === 10401) {
                        facade.error(res["msg"], "重新登录提示", function () {
                            facade.delCookie("laytp_admin_token");
                            facade.redirect("/admin/login.html");
                        });
                        return;
                    }
                    if (res.code === 1) {
                        facade.error(res["msg"], "失败提示");
                        return;
                    }
                    options.data = res.data;
                    if (options.multi) {
                        //多个预览
                        if (options.accept === "image") {
                            $("#preview_" + options.name, options.parentElem).append(layTpUpload.singleImageHtml(options));
                        } else if (options.accept === "video") {
                            $("#preview_" + options.name, options.parentElem).append(layTpUpload.singleVideoHtml(options));
                        } else if (options.accept === "audio") {
                            $("#preview_" + options.name, options.parentElem).append(layTpUpload.singleAudioHtml(options));
                        }
                        //input框增加文件值
                        let input_value = $("#input_" + options.name).val();
                        if (input_value) {
                            $("#input_" + options.name, options.parentElem).val(input_value + splitStr + res.data).focus();
                        } else {
                            $("#input_" + options.name, options.parentElem).val(res.data).focus();
                        }
                    } else {
                        //单个预览
                        if (options.accept === "image") {
                            $("#preview_" + options.name, options.parentElem).html(layTpUpload.singleImageHtml(options));
                        } else if (options.accept === "video") {
                            $("#preview_" + options.name, options.parentElem).html(layTpUpload.singleVideoHtml(options));
                        } else if (options.accept === "audio") {
                            $("#preview_" + options.name, options.parentElem).html(layTpUpload.singleAudioHtml(options));
                        }
                        //input框增加文件值
                        $("#input_" + options.name, options.parentElem).val(res.data).focus();
                    }
                    return facade.success(res.msg);
                }
            });

            //删除已经上传的东西
            $("body").on("click", ".upload_img_close, .upload_delete", function () {
                let id = $(this).data("id");
                let parentElem = $(this).data('parentelem');
                if (options.multi) {
                    let fileUrl = $(this).data("file_url");
                    let inputValue = $("#input_" + id).val();
                    let newInputValue = "";
                    if (inputValue.indexOf(fileUrl + splitStr) !== -1) {
                        let reg = new RegExp(fileUrl + splitStr);
                        newInputValue = inputValue.replace(reg, "");
                    } else {
                        if (inputValue.indexOf(splitStr + fileUrl) !== -1) {
                            let reg = new RegExp(splitStr + fileUrl);
                            newInputValue = inputValue.replace(reg, "");
                        } else {
                            let reg = new RegExp(fileUrl);
                            newInputValue = inputValue.replace(reg, "");
                        }
                    }
                    $("#input_" + id, parentElem).val(newInputValue);
                } else {
                    $("#input_" + id, parentElem).val("");
                }
                $(this).closest("li").remove();
            });
        }
    };

    //输出模块
    exports(MOD_NAME, layTpUpload);

    //注入layui组件中，供全局调用
    layui.layTpUpload = layTpUpload;

    //注入window全局对象中，供全局调用
    window.layTpUpload = layTpUpload;
});
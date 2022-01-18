/**
 * 上传组件，基于layui.upload组件进行封装，html代码<div class="laytpUpload" data-id="id" data-name="name"></div>渲染成一个上传组件
 *  - 允许上传图片、音频、视频和任意文件
 *  - input输入框的展示访问上传文件的url地址
 *  - 图片、音频、视频的预览和删除
 * @version: 1.0
 * @Author:  JunStar
 * @Date:    2020-09-03 11:33:43
 * @Last Modified by:   JunStar
 * @Last Modified time: 2020-09-03 11:33:48
 */
layui.define(["jquery", "element"], function (exports) {
    const MOD_NAME = "laytpUpload";
    var $ = layui.$;
    var element = layui.element;

    let storeAsOptions = [];

    var optionsObj = {};

    var laytpUpload = {
        pathSplitStr: ", ",
        idSplitStr: ",",
        uploadType: {
            "image": "图片",
            "video": "视频",
            "audio": "音频",
            "file": "文件"
        },
        // 展示上传按钮和选择附件的html内容
        uploadHtml: function (options) {
            var html =
                "<div class=\"layui-upload\">" +
                "   <input type=\"hidden\" class=\"layui-input\" id=\"id_{{d.name}}\" value='{{d.uploadedId}}' lay-verify=\"{{d.layVerify}}\" lay-verType=\"{{d.layVerType}}\" readonly=\"readonly\" name=\"{{d.name}}\" />" +
                "   <input type=\"hidden\" class=\"layui-input\" id=\"input_{{d.name}}\" value='{{d.uploaded}}' lay-verify=\"{{d.layVerify}}\" lay-verType=\"{{d.layVerType}}\" readonly=\"readonly\" />" +
                "{{# if(d.showUploadBtn){ }}" +
                "   <div class=\"layui-inline\">" +
                "       <a id='laytpUploadBtn_{{d.name}}' class=\"laytp-btn laytp-btn-primary laytp-btn-md pull-left\">" +
                "            <i class='layui-icon layui-icon-upload-drag'></i> 点击上传{{laytpUpload.uploadType[d.accept]}}" +
                "       </a>" +
                "   </div>" +
                "{{# } }}" +
                "{{# if(d.showChoiceBtn){ }}" +
                "   <div class=\"layui-inline\">" +
                "       <a id='laytpCheckFileBtn_{{d.name}}' data-name='{{d.name}}' class=\"laytp-btn laytp-btn-warming laytp-btn-md pull-left\">" +
                "            <i class='layui-icon layui-icon-file-b'></i> 选择附件" +
                "       </a>" +
                "   </div>" +
                "{{# } }}" +
                "   {{# if(d.accept === 'image'){ }}" +
                "   {{#     var widthText = ''; var heightText = ''; var sizeText = ''; }}" +
                "   {{#     if(d.width === '0' || d.width === 'undefined'){ }}" +
                "   {{#         widthText = '不限制'; }}" +
                "   {{#     }else{ }}" +
                "   {{#         widthText = d.width + 'px'; }}" +
                "   {{#     } }}" +
                "   {{#     if(d.height === '0' || d.height === 'undefined'){ }}" +
                "   {{#         heightText = '不限制'; }}" +
                "   {{#     }else{ }}" +
                "   {{#         heightText = d.height + 'px'; }}" +
                "   {{#     } }}" +
                "   {{#     if(d.size === 'undefined' || d.size === ''){ }}" +
                "   {{#         sizeText = '不限制'; }}" +
                "   {{#     }else{ }}" +
                "   {{#         sizeText = d.size; }}" +
                "   {{#     } }}" +
                "   <div class=\"layui-inline\">" +
                "       <div class=\"layui-form-mid layui-word-aux\">" +
                "            建议尺寸，宽：{{widthText}}，高：{{heightText}}；文件大小上限：{{sizeText}}" +
                "       </div>" +
                "   </div>" +
                "   {{# }else if(d.accept === 'video' || d.accept === 'audio' || d.accept === 'file'){ }}" +
                "   {{#     var sizeText = ''; }}" +
                "   {{#     if(d.size === 'undefined' || d.size === ''){ }}" +
                "   {{#         sizeText = '不限制'; }}" +
                "   {{#     }else{ }}" +
                "   {{#         sizeText = d.size; }}" +
                "   {{#     } }}" +
                "   <div class=\"layui-inline\">" +
                "       <div class=\"layui-form-mid layui-word-aux\">" +
                "            文件大小上限：{{sizeText}}" +
                "       </div>" +
                "   </div>" +
                "   {{# } }}" +
                "   {{d.previewHtml}}" +
                "</div>";
            return layui.laytpl(html).render(options);
        },

        // 经过服务器方式上传文件，选中文件后，展示上传的中间过程html
        viaChooseProgress: function (options) {
            var html =
                "<div class=\"laytp-more\">" +
                "   <ul class=\"laytp-more-upload-list\" id=\"preview_{{d.name}}\">" +
                "       <li class=\"item_img\" data-index=\"{{d.index}}\">" +
                "           <div class=\"layui-progress layui-progress-big\" lay-filter=\"{{d.index}}\">" +
                "               <div class=\"layui-progress-bar\" lay-percent=\"0%\">" +
                "                   <span class=\"layui-progress-text\">0%</span>" +
                "               </div>" +
                "           </div>" +
                "       </li>" +
                "   </ul>" +
                "</div>";
            return layui.laytpl(html).render(options);
        },

        // 不经过服务器方式上传文件，选中文件后，展示上传的中间过程html
        unViaChooseProgress: function (options) {
            var html =
                "<div class=\"laytp-more\">" +
                "   <ul class=\"laytp-more-upload-list\" id=\"preview_{{d.name}}\">" +
                "       <li class=\"item_img\" data-path=\"{{d.path}}\">" +
                "           <div class=\"layui-progress layui-progress-big\" lay-filter=\"{{d.storeAsKey}}\">" +
                "               <div class=\"layui-progress-bar\" lay-percent=\"0%\">" +
                "                   <span class=\"layui-progress-text\">0%</span>" +
                "               </div>" +
                "           </div>" +
                "       </li>" +
                "   </ul>" +
                "</div>";
            return layui.laytpl(html).render(options);
        },

        // 预览图片的html
        imagePreviewHtml: function (options) {
            var html =
                "<div class=\"laytp-more\">" +
                "   <ul class=\"laytp-more-upload-list\" id=\"preview_{{d.name}}\">" +
                "   {{# var uploaded=d.uploaded; var uploadedId=d.uploadedId.toString(); }}" +
                "   {{# if(typeof uploaded !== 'undefined' && uploaded){ }}" +
                "   {{# var key,uploadedArr=uploaded.split(laytpUpload.pathSplitStr); }}" +
                "   {{# var uploadedIdArr=uploadedId.split(laytpUpload.idSplitStr); }}" +
                "   {{# for(key in uploadedArr){ }}" +
                "       <li class=\"item_img\">" +
                "           <div class=\"operate\">" +
                "               <i class=\"upload_img_close layui-icon\" data-fileUrl=\"{{uploadedArr[key]}}\" data-fileId=\"{{uploadedIdArr[key]}}\" data-id=\"{{d.name}}\" data-parentElem=\"{{d.parentElem}}\"></i>" +
                "           </div>" +
                "           <img src=\"{{uploadedArr[key]}}\" class=\"img\">" +
                "       </li>" +
                "   {{# } }}" +
                "   {{# } }}" +
                "   </ul>" +
                "</div>";
            return layui.laytpl(html).render(options);
        },

        // 预览视频的html
        videoPreviewHtml: function (options) {
            var html =
                "<div class=\"laytp-more\">" +
                "   <ul class=\"laytp-more-upload-list\" id=\"preview_{{d.name}}\">" +
                "   {{# var uploaded=d.uploaded; var uploadedId=d.uploadedId.toString(); }}" +
                "   {{# if(typeof uploaded !== 'undefined' && uploaded){ var key,uploadedArr=uploaded.split(laytpUpload.pathSplitStr); }}" +
                "   {{# var uploadedIdArr=uploadedId.split(laytpUpload.idSplitStr); }}" +
                "   {{# for(key in uploadedArr){ }}" +
                "       <li class=\"item_video\">" +
                "           <video src=\"{{uploadedArr[key]}}\" controls=\"controls\" width=\"200px\" height=\"200px\"></video>" +
                "           <button type=\"button\" class=\"layui-btn layui-btn-sm layui-btn-danger upload_delete\" style=\"display: block; width: 100%;\" data-fileUrl=\"{{uploadedArr[key]}}\" data-fileId=\"{{uploadedIdArr[key]}}\" data-id=\"{{d.name}}\"><i class=\"layui-icon\">&#xe640;</i></button>" +
                "       </li>" +
                "   {{# } }}" +
                "   {{# } }}" +
                "   </ul>" +
                "</div>";
            return layui.laytpl(html).render(options);
        },

        // 预览音频的html
        audioPreviewHtml: function (options) {
            var html =
                "<div class=\"laytp-more\">" +
                "   <ul class=\"laytp-more-upload-list\" id=\"preview_{{d.name}}\">" +
                "   {{# var uploaded=d.uploaded; var uploadedId=d.uploadedId.toString(); }}" +
                "   {{# if(typeof uploaded !== 'undefined' && uploaded){ var key,uploadedArr=uploaded.split(laytpUpload.pathSplitStr); }}" +
                "   {{# var uploadedIdArr=uploadedId.split(laytpUpload.idSplitStr); }}" +
                "   {{# for(key in uploadedArr){ }}" +
                "       <li class=\"item_audio\">" +
                "           <audio src=\"{{uploadedArr[key]}}\" controls=\"controls\" style=\"height:54px;\"></audio>" +
                "           <button type=\"button\" class=\"layui-btn layui-btn-sm layui-btn-danger upload_delete\" style=\"display: block; width: 100%;\" data-fileUrl=\"{{uploadedArr[key]}}\" data-fileId=\"{{uploadedIdArr[key]}}\" data-id=\"{{d.name}}\"><i class=\"layui-icon\">&#xe640;</i></button>" +
                "       </li>" +
                "   {{# } }}" +
                "   {{# } }}" +
                "   </ul>" +
                "</div>";
            return layui.laytpl(html).render(options);
        },

        // 预览文件的html
        filePreviewHtml: function (options) {
            var html =
                "<div class=\"laytp-more\">" +
                "   <ul class=\"laytp-more-upload-list\" id=\"preview_{{d.name}}\">" +
                "   {{# var uploaded=d.uploaded; var uploadedFilename=d.uploadedFilename;  var uploadedId=d.uploadedId.toString(); }}" +
                "   {{# if(typeof uploaded !== 'undefined' && uploaded){ }}" +
                "   {{# var key,uploadedArr=uploaded.split(laytpUpload.pathSplitStr); }}" +
                "   {{# var uploadedIdArr=uploadedId.split(laytpUpload.idSplitStr); var uploadedFilenameArr=uploadedFilename.split(laytpUpload.pathSplitStr); }}" +
                "   {{# for(key in uploadedArr){ }}" +
                "       <li class=\"item_file\">" +
                "           <div class=\"operate\">" +
                "               <i class=\"upload_file_close layui-icon\" data-fileUrl=\"{{uploadedArr[key]}}\" data-fileName=\"{{uploadedFilenameArr[key]}}\" data-fileId=\"{{uploadedIdArr[key]}}\" data-id=\"{{d.name}}\" data-parentElem=\"{{d.parentElem}}\"></i>" +
                "           </div>" +
                "       {{# var fa=laytpUpload.getFaByFilename(uploadedArr[key]); }}" +
                "       {{# if(fa == 'image'){ }}" +
                "           <img src=\"{{uploadedArr[key]}}\" class=\"img\">" +
                "       {{# }else{ }}" +
                "           <div class=\"file {{fa}} layerTips\" data-text=\"{{uploadedFilenameArr[key]}}\"></div>" +
                "       {{# } }}" +
                "       </li>" +
                "   {{# } }}" +
                "   {{# } }}" +
                "   </ul>" +
                "</div>";
            return layui.laytpl(html).render(options);
        },

        // 单个图片模板
        singleImageHtml: function (options) {
            var html =
                '<li class="item_img">' +
                '   <div class="operate">' +
                '       <i class="upload_img_close layui-icon" data-fileUrl="{{d.data.path}}" data-fileId="{{d.data.id}}" data-id="{{d.name}}" data-parentElem=\"{{d.parentElem}}\"></i>' +
                '   </div>' +
                '   <img src="{{d.data.path}}" class="img" />' +
                '</li>';
            return layui.laytpl(html).render(options);
        },

        // 单个视频模板
        singleVideoHtml: function (options) {
            var html =
                '<li class="item_video">' +
                '   <video src="{{d.data.path}}" controls="controls" width="200px" height="200px"></video>' +
                '   <button type=\"button\" class="layui-btn layui-btn-sm layui-btn-danger upload_delete" style="display: block; width: 100%;" data-fileUrl="{{d.data.path}}" data-fileId="{{d.data.id}}" data-id="{{d.name}}" data-parentElem=\"{{d.parentElem}}\"><i class="layui-icon">&#xe640;</i></button>' +
                '</li>';
            return layui.laytpl(html).render(options);
        },

        // 单个音频模板
        singleAudioHtml: function (options) {
            var html =
                '<li class="item_audio">' +
                '   <audio src="{{d.data.path}}" controls="controls" style="height:54px;"></audio>' +
                '   <button type=\"button\" class="layui-btn layui-btn-sm layui-btn-danger upload_delete" style="display: block; width: 100%;" data-fileUrl="{{d.data.path}}" data-fileId="{{d.data.id}}" data-id="{{d.name}}" data-parentElem=\"{{d.parentElem}}\"><i class="layui-icon">&#xe640;</i></button>' +
                '</li>';
            return layui.laytpl(html).render(options);
        },

        // 单个文件模板
        singleFileHtml: function (options) {
            var html =
                '<li class="item_file">' +
                '   <div class="operate">' +
                '       <i class="upload_file_close layui-icon" data-fileUrl="{{d.data.path}}" data-fileName="{{=d.data.name}}" data-fileId="{{d.data.id}}" data-id="{{d.name}}" data-parentElem=\"{{d.parentElem}}\"></i>' +
                '   </div>' +
                '   {{# var fa=laytpUpload.getFaByFilename(d.data.path); }}' +
                '   {{# if(fa == "image"){ }}' +
                '   <img src="{{d.data.path}}" class="img" />' +
                '   {{# }else{ }}' +
                '   <div class=\"file {{fa}} layerTips\" data-text="{{=d.data.name}}"></div>' +
                '   {{# } }}' +
                '</li>';
            return layui.laytpl(html).render(options);
        },

        // 通过文件名获取字体样式
        getFaByFilename: function (filename) {
            var fileExtention = facade.getFileExt(filename);
            var imageExtArr = ['jpg', 'jpeg', 'gif', 'png'];
            var pptExtArr = ['ppt', 'pptx'];
            var excelExtArr = ['xls', 'xlsx'];
            var wordExtArr = ['doc', 'docx'];
            var videoExtArr = ['mp4'];
            var audioExtArr = ['mp3'];
            if (facade.inArray(fileExtention, imageExtArr)) {
                return 'image';
            } else if (facade.inArray(fileExtention, pptExtArr)) {
                return 'fa fa-file-powerpoint-o';
            } else if (facade.inArray(fileExtention, excelExtArr)) {
                return 'fa fa-file-excel-o';
            } else if (facade.inArray(fileExtention, wordExtArr)) {
                return 'fa fa-file-word-o';
            } else if (facade.inArray(fileExtention, videoExtArr)) {
                return 'fa fa-film';
            } else if (facade.inArray(fileExtention, audioExtArr)) {
                return 'fa fa-file-audio-o';
            } else {
                return 'fa fa-file-o';
            }
        },

        // 经过服务器方式上传
        render: function (options) {
            optionsObj[options.name] = options;
            if (options.accept === "image") {
                options.previewHtml = laytpUpload.imagePreviewHtml(options);
            } else if (options.accept === "file") {
                options.previewHtml = laytpUpload.filePreviewHtml(options);
            } else if (options.accept === "video") {
                options.previewHtml = laytpUpload.videoPreviewHtml(options);
            } else if (options.accept === "audio") {
                options.previewHtml = laytpUpload.audioPreviewHtml(options);
            }
            $(options.el).after(laytpUpload.uploadHtml(options));

            // 渲染点击上传按钮
            var ajaxHeaders = {};
            ajaxHeaders[layui.context.get("tokenKey")] = facade.getCookie(layui.context.get("tokenCookieKey"));
            layui.upload.render({
                headers: ajaxHeaders,
                elem: $("#laytpUploadBtn_" + options.name, options.parentElem),
                url: facade.url("/admin.common/upload"),
                accept: options.accept,
                multiple: options.multi,
                data: options.params,
                field: "laytpUploadFile",
                auto: false, // 为了调试选中文件后，展示上传的中间过程的html，暂时先不自动上传
                choose: function (obj) {
                    // 这个位置开始预览，展示上传的中间过程
                    obj.preview(function (index, file, result) {
                        options.index = index;
                        $("#preview_" + options.name, options.parentElem).append(laytpUpload.viaChooseProgress(options));
                        element.progress(index, '20%');
                        obj.upload(index, file);
                    });
                },
                done: function (res, index, upload) {
                    if (res.code === 10401) {
                        layui.popup.failure(res["msg"], function () {
                            facade.redirect("admin/login.html");
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
                        $("li[data-index=" + index + "]", $("#preview_" + options.name, options.parentElem)).remove();
                        if (options.accept === "image") {
                            $("#preview_" + options.name, options.parentElem).append(laytpUpload.singleImageHtml(options));
                        } else if (options.accept === "video") {
                            $("#preview_" + options.name, options.parentElem).append(laytpUpload.singleVideoHtml(options));
                        } else if (options.accept === "audio") {
                            $("#preview_" + options.name, options.parentElem).append(laytpUpload.singleAudioHtml(options));
                        } else if (options.accept === "file") {
                            $("#preview_" + options.name, options.parentElem).append(laytpUpload.singleFileHtml(options));
                        }
                        //input框增加文件值
                        var inputValue = $("#input_" + options.name).val();
                        var idValue = $("#id_" + options.name).val();
                        if (inputValue) {
                            $("#id_" + options.name, options.parentElem).val(idValue + laytpUpload.idSplitStr + res.data.id);
                            $("#input_" + options.name, options.parentElem).val(inputValue + laytpUpload.pathSplitStr + res.data.path);
                        } else {
                            $("#id_" + options.name, options.parentElem).val(res.data.id);
                            $("#input_" + options.name, options.parentElem).val(res.data.path);
                        }
                    } else {
                        //单个预览
                        if (options.accept === "image") {
                            $("#preview_" + options.name, options.parentElem).html(laytpUpload.singleImageHtml(options));
                        } else if (options.accept === "video") {
                            $("#preview_" + options.name, options.parentElem).html(laytpUpload.singleVideoHtml(options));
                        } else if (options.accept === "audio") {
                            $("#preview_" + options.name, options.parentElem).html(laytpUpload.singleAudioHtml(options));
                        } else if (options.accept === "file") {
                            $("#preview_" + options.name, options.parentElem).html(laytpUpload.singleFileHtml(options));
                        }
                        //input框增加文件值
                        $("#id_" + options.name, options.parentElem).val(res.data.id);
                        $("#input_" + options.name, options.parentElem).val(res.data.path);
                    }
                    return facade.success(res.msg);
                }
            });

            // 渲染选择附件按钮
            laytpUpload.renderChoiceFile(options);

            // 删除已经上传的东西
            $("body").off("click", ".upload_img_close, .upload_delete, .upload_file_close").on("click", ".upload_img_close, .upload_delete, .upload_file_close", function () {
                var id = $(this).data("id");
                var parentElem = $(this).data('parentelem');
                var idObj = (parentElem !== "undefined") ? $("#id_" + id, parentElem) : $("#id_" + id);
                var inputObj = (parentElem !== "undefined") ? $("#input_" + id, parentElem) : $("#input_" + id);
                if (optionsObj[id].multi) {
                    var fileUrl = $(this).data("fileurl");
                    var fileId = $(this).data("fileid");
                    var idValue = idObj.val();
                    var inputValue = inputObj.val();
                    var newIdValue = "";
                    var newInputValue = "";
                    if (inputValue.indexOf(fileUrl + laytpUpload.pathSplitStr) !== -1) {
                        var reg = new RegExp(fileUrl + laytpUpload.pathSplitStr);
                        newInputValue = inputValue.replace(reg, "");
                        var idReg = new RegExp(fileId + laytpUpload.idSplitStr);
                        newIdValue = idValue.replace(idReg, "");
                    } else {
                        if (inputValue.indexOf(laytpUpload.pathSplitStr + fileUrl) !== -1) {
                            var reg = new RegExp(laytpUpload.pathSplitStr + fileUrl);
                            newInputValue = inputValue.replace(reg, "");
                            var idReg = new RegExp(laytpUpload.idSplitStr + fileId);
                            newIdValue = idValue.replace(idReg, "");
                        } else {
                            var reg = new RegExp(fileUrl);
                            newInputValue = inputValue.replace(reg, "");
                            var idReg = new RegExp(fileId);
                            newIdValue = idValue.replace(idReg, "");
                        }
                    }
                    inputObj.val(newInputValue);
                    idObj.val(newIdValue);
                } else {
                    inputObj.val("");
                    idObj.val("");
                }
                $(this).closest("li").remove();
            });
        },

        // 获取sts临时凭证，客户端上传到oss
        renderStsOss: function (options) {
            optionsObj[options.name] = options;
            if (options.accept === "image") {
                options.previewHtml = laytpUpload.imagePreviewHtml(options);
            } else if (options.accept === "file") {
                options.previewHtml = laytpUpload.filePreviewHtml(options);
            } else if (options.accept === "video") {
                options.previewHtml = laytpUpload.videoPreviewHtml(options);
            } else if (options.accept === "audio") {
                options.previewHtml = laytpUpload.audioPreviewHtml(options);
            }
            $(options.el).after(laytpUpload.uploadHtml(options));
            var uploadInst = layui.upload.render({
                elem: $("#laytpUploadBtn_" + options.name, options.parentElem),
                url: options.url,
                accept: options.accept,
                multiple: options.multi,
                data: options.params,
                auto: false,
                field: "laytpUploadFile",
                choose: function (obj) {
                    //choose的回调，在第二次选择相同文件时，只会触发第一次
                    //加上如下语句，就会每次都触发，不管选择是不是相同的文件
                    uploadInst.config.elem.next()[0].value = '';
                    obj.preview(function (index, file, base64) {
                        facade.ajax({
                            route: "/admin.common/aliSts",
                            data: {name: file.name, size: file.size, ext: facade.getFileExt(file.name), index: index},
                            successAlert: false
                        }).done(function (res) {
                            if (res.code === 0) {
                                var storeAs = res.data.path;//设置上传的文件名
                                var storeAsKey = storeAs.replace('/', '-').substr(0, storeAs.lastIndexOf('.'));
                                if (!(storeAsKey in storeAsOptions)) {
                                    storeAsOptions[storeAsKey] = {};
                                    storeAsOptions[storeAsKey].index = res.data.index;
                                    storeAsOptions[storeAsKey].storeAsKey = storeAsKey;
                                    storeAsOptions[storeAsKey].status = 'uploading';
                                    storeAsOptions[storeAsKey].path = res.data.path;
                                    storeAsOptions[storeAsKey].name = options.name;
                                    storeAsOptions[storeAsKey].parentElem = options.parentElem;
                                    storeAsOptions[storeAsKey].accept = options.accept;
                                    storeAsOptions[storeAsKey].type = options.type;
                                    storeAsOptions[storeAsKey].via_server = options.viaServer;
                                    storeAsOptions[storeAsKey].multi = options.multi;
                                    storeAsOptions[storeAsKey].filename = file.name;
                                    storeAsOptions[storeAsKey].size = file.size;
                                    storeAsOptions[storeAsKey].category_id = options.category_id;
                                    storeAsOptions[storeAsKey].ext = facade.getFileExt(file.name);
                                    storeAsOptions[storeAsKey].data = {};
                                }
                                // options.path = storeAsKey;
                                $("#preview_" + storeAsOptions[storeAsKey].name, storeAsOptions[storeAsKey].parentElem).append(laytpUpload.unViaChooseProgress(storeAsOptions[storeAsKey]));
                                element.progress(index, '0%');
                                $.ajax({
                                    url: "https://gosspublic.alicdn.com/aliyun-oss-sdk-6.3.1.min.js",
                                    dataType: "script",
                                    cache: true
                                }).done(function () {
                                    var client = new OSS({
                                        accessKeyId: res.data.sts.AccessKeyId,
                                        accessKeySecret: res.data.sts.AccessKeySecret,
                                        stsToken: res.data.sts.SecurityToken,
                                        region: res.data.sts.endpoint,
                                        bucket: res.data.sts.bucket
                                    });
                                    client.multipartUpload(storeAs, file, {
                                        progress: function (p) {
                                            var storeAsKey = storeAs.replace('/', '-').substr(0, storeAs.lastIndexOf('.'));
                                            element.progress(storeAsKey, (p * 100).toFixed(2) + '%');
                                            if (p === 1) {
                                                facade.ajax({
                                                    route: "/admin.files/unViaSave",
                                                    data: {
                                                        name: storeAsOptions[storeAsKey].filename
                                                        , category_id: storeAsOptions[storeAsKey].category_id
                                                        , file_type: storeAsOptions[storeAsKey].accept
                                                        , upload_type: storeAsOptions[storeAsKey].type
                                                        , ext: storeAsOptions[storeAsKey].ext
                                                        , path: storeAs
                                                        , size: storeAsOptions[storeAsKey].size
                                                        , via_server: storeAsOptions[storeAsKey].via_server
                                                    },
                                                    successAlert: false
                                                }).done(function (resFile) {
                                                    storeAsOptions[storeAsKey].data.path = res.data.sts.domain + '/' + storeAs;
                                                    storeAsOptions[storeAsKey].data.id = resFile.data.id;
                                                    storeAsOptions[storeAsKey].data.name = file.name;
                                                    storeAsOptions[storeAsKey].status = "uploaded";
                                                    if (options.uploadedCallback) {
                                                        options.uploadedCallback(storeAsOptions[storeAsKey]);
                                                    }
                                                    if (laytpUpload.hasUploadingStoreAsOptions()) {
                                                        return facade.success('上传成功，页面上所有文件上传成功后，才可预览');
                                                    } else {
                                                        facade.success('所有文件上传成功，正在渲染预览');
                                                        for (let k in storeAsOptions) {
                                                            var item = storeAsOptions[k];
                                                            if (item.multi) {
                                                                $("li[data-path=" + k + "]", $("#preview_" + item.name, item.parentElem)).remove();
                                                                //多个预览
                                                                if (item.accept === "image") {
                                                                    $("#preview_" + item.name, item.parentElem).append(laytpUpload.singleImageHtml(item));
                                                                } else if (item.accept === "video") {
                                                                    $("#preview_" + item.name, item.parentElem).append(laytpUpload.singleVideoHtml(item));
                                                                } else if (item.accept === "audio") {
                                                                    $("#preview_" + item.name, item.parentElem).append(laytpUpload.singleAudioHtml(item));
                                                                } else if (item.accept === "file") {
                                                                    $("#preview_" + item.name, item.parentElem).append(laytpUpload.singleFileHtml(item));
                                                                }
                                                                //input框增加文件值
                                                                var inputValue = $("#input_" + item.name).val();
                                                                var idValue = $("#id_" + item.name).val();
                                                                if (inputValue) {
                                                                    $("#id_" + item.name, item.parentElem).val(idValue + laytpUpload.idSplitStr + item.data.id);
                                                                    $("#input_" + item.name, item.parentElem).val(inputValue + laytpUpload.pathSplitStr + item.data.path);
                                                                } else {
                                                                    $("#id_" + item.name, item.parentElem).val(item.data.id);
                                                                    $("#input_" + item.name, item.parentElem).val(item.data.path);
                                                                }
                                                            } else {
                                                                //单个预览
                                                                if (item.accept === "image") {
                                                                    $("#preview_" + item.name, item.parentElem).html(laytpUpload.singleImageHtml(item));
                                                                } else if (item.accept === "video") {
                                                                    $("#preview_" + item.name, item.parentElem).html(laytpUpload.singleVideoHtml(item));
                                                                } else if (item.accept === "audio") {
                                                                    $("#preview_" + item.name, item.parentElem).html(laytpUpload.singleAudioHtml(item));
                                                                } else if (item.accept === "file") {
                                                                    $("#preview_" + item.name, item.parentElem).html(laytpUpload.singleFileHtml(item));
                                                                }
                                                                //input框增加文件值
                                                                $("#id_" + item.name, item.parentElem).val(item.data.id);
                                                                $("#input_" + item.name, item.parentElem).val(item.data.path);
                                                            }
                                                        }
                                                        // 预览都执行完毕之后，就把storeAsOptions数组清空
                                                        storeAsOptions = [];
                                                    }
                                                });
                                            }
                                        }
                                    });
                                });
                            }
                        });
                    });
                }
            });

            // 渲染选择附件按钮
            laytpUpload.renderChoiceFile(options);

            // 删除已经上传的东西
            $("body").off("click", ".upload_img_close, .upload_delete, .upload_file_close").on("click", ".upload_img_close, .upload_delete, .upload_file_close", function () {
                var id = $(this).data("id");
                var parentElem = $(this).data('parentelem');
                var idObj = (parentElem !== "undefined") ? $("#id_" + id, parentElem) : $("#id_" + id);
                var inputObj = (parentElem !== "undefined") ? $("#input_" + id, parentElem) : $("#input_" + id);
                if (optionsObj[id].multi) {
                    var fileUrl = $(this).data("fileurl");
                    var fileId = $(this).data("fileid");
                    var idValue = idObj.val();
                    var inputValue = inputObj.val();
                    var newIdValue = "";
                    var newInputValue = "";
                    if (inputValue.indexOf(fileUrl + laytpUpload.pathSplitStr) !== -1) {
                        var reg = new RegExp(fileUrl + laytpUpload.pathSplitStr);
                        newInputValue = inputValue.replace(reg, "");
                        var idReg = new RegExp(fileId + laytpUpload.idSplitStr);
                        newIdValue = idValue.replace(idReg, "");
                    } else {
                        if (inputValue.indexOf(laytpUpload.pathSplitStr + fileUrl) !== -1) {
                            var reg = new RegExp(laytpUpload.pathSplitStr + fileUrl);
                            newInputValue = inputValue.replace(reg, "");
                            var idReg = new RegExp(laytpUpload.idSplitStr + fileId);
                            newIdValue = idValue.replace(idReg, "");
                        } else {
                            var reg = new RegExp(fileUrl);
                            newInputValue = inputValue.replace(reg, "");
                            var idReg = new RegExp(fileId);
                            newIdValue = idValue.replace(idReg, "");
                        }
                    }
                    inputObj.val(newInputValue);
                    idObj.val(newIdValue);
                } else {
                    inputObj.val("");
                    idObj.val("");
                }
                $(this).closest("li").remove();
            });
        },

        // 获取token，客户端上传到kodo
        renderTokenKodo: function (options) {
            optionsObj[options.name] = options;
            if (options.accept === "image") {
                options.previewHtml = laytpUpload.imagePreviewHtml(options);
            } else if (options.accept === "file") {
                options.previewHtml = laytpUpload.filePreviewHtml(options);
            } else if (options.accept === "video") {
                options.previewHtml = laytpUpload.videoPreviewHtml(options);
            } else if (options.accept === "audio") {
                options.previewHtml = laytpUpload.audioPreviewHtml(options);
            }
            $(options.el).after(laytpUpload.uploadHtml(options));
            var uploadInst = layui.upload.render({
                elem: $("#laytpUploadBtn_" + options.name, options.parentElem),
                url: options.url,
                accept: options.accept,
                multiple: options.multi,
                data: options.params,
                auto: false,
                field: "laytpUploadFile",
                choose: function (obj) {
                    //choose的回调，在第二次选择相同文件时，只会触发第一次
                    //加上如下语句，就会每次都触发，不管选择是不是相同的文件
                    uploadInst.config.elem.next()[0].value = '';
                    obj.preview(function (index, file, base64) {
                        facade.ajax({
                            route: facade.url("/admin.common/kodoToken"),
                            data: {name: file.name, size: file.size, ext: facade.getFileExt(file.name), index: index},
                            successAlert: false
                        }).done(function (res) {
                            if (res.code === 0) {
                                var storeAs = res.data.path;//设置上传的文件名
                                var storeAsKey = storeAs.replace('/', '-').substr(0, storeAs.lastIndexOf('.'));
                                if (!(storeAsKey in storeAsOptions)) {
                                    storeAsOptions[storeAsKey] = {};
                                    storeAsOptions[storeAsKey].index = res.data.index;
                                    storeAsOptions[storeAsKey].status = 'uploading';
                                    storeAsOptions[storeAsKey].path = storeAsKey;
                                    storeAsOptions[storeAsKey].name = options.name;
                                    storeAsOptions[storeAsKey].parentElem = options.parentElem;
                                    storeAsOptions[storeAsKey].accept = options.accept;
                                    storeAsOptions[storeAsKey].type = options.type;
                                    storeAsOptions[storeAsKey].via_server = options.viaServer;
                                    storeAsOptions[storeAsKey].multi = options.multi;
                                    storeAsOptions[storeAsKey].filename = file.name;
                                    storeAsOptions[storeAsKey].size = file.size;
                                    storeAsOptions[storeAsKey].category_id = options.category_id;
                                    storeAsOptions[storeAsKey].ext = facade.getFileExt(file.name);
                                    storeAsOptions[storeAsKey].data = {};
                                }
                                options.path = storeAsKey;
                                $("#preview_" + storeAsOptions[storeAsKey].name, storeAsOptions[storeAsKey].parentElem).append(laytpUpload.unViaChooseProgress(storeAsOptions[storeAsKey]));
                                element.progress(index, '0%');
                                $.ajax({
                                    url: "https://cdnjs.cloudflare.com/ajax/libs/qiniu-js/3.1.4/qiniu.min.js",
                                    dataType: "script",
                                    cache: true
                                }).done(function () {
                                    const observable = qiniu.upload(file, storeAs, res.data.token, {fname: file.name,}, {});
                                    const subscription = observable.subscribe({
                                        next: function (res) {
                                            var p = res.total.percent.toFixed(2);
                                            var storeAsKey = storeAs.replace('/', '-').substr(0, storeAs.lastIndexOf('.'));
                                            element.progress(storeAsKey, p + '%');
                                        },
                                        error(err){
                                            // ...
                                        },
                                        complete(completeRes){
                                            var storeAsKey = completeRes.key.replace('/', '-').substr(0, storeAs.lastIndexOf('.'));
                                            facade.ajax({
                                                route: "/admin.files/unViaSave",
                                                data: {
                                                    name: storeAsOptions[storeAsKey].filename
                                                    , category_id: storeAsOptions[storeAsKey].category_id
                                                    , file_type: storeAsOptions[storeAsKey].accept
                                                    , upload_type: storeAsOptions[storeAsKey].type
                                                    , ext: storeAsOptions[storeAsKey].ext
                                                    , path: completeRes.key
                                                    , size: completeRes.size
                                                    , via_server: storeAsOptions[storeAsKey].via_server
                                                },
                                                successAlert: false
                                            }).done(function (resFile) {
                                                storeAsOptions[storeAsKey].data.path = res.data.domain + '/' + completeRes.key;
                                                storeAsOptions[storeAsKey].data.id = resFile.data.id;
                                                storeAsOptions[storeAsKey].data.name = storeAsOptions[storeAsKey].filename;
                                                storeAsOptions[storeAsKey].status = "uploaded";
                                                if (options.uploadedCallback) {
                                                    options.uploadedCallback(storeAsOptions[storeAsKey]);
                                                }
                                                if (laytpUpload.hasUploadingStoreAsOptions()) {
                                                    return facade.success('上传成功，页面上所有文件上传成功后，才可预览');
                                                } else {
                                                    facade.success('所有文件上传成功，正在渲染预览');
                                                    for (let k in storeAsOptions) {
                                                        var item = storeAsOptions[k];
                                                        if (item.multi) {
                                                            $("li[data-path=" + k + "]", $("#preview_" + item.name, item.parentElem)).remove();
                                                            //多个预览
                                                            if (item.accept === "image") {
                                                                $("#preview_" + item.name, item.parentElem).append(laytpUpload.singleImageHtml(item));
                                                            } else if (item.accept === "video") {
                                                                $("#preview_" + item.name, item.parentElem).append(laytpUpload.singleVideoHtml(item));
                                                            } else if (item.accept === "audio") {
                                                                $("#preview_" + item.name, item.parentElem).append(laytpUpload.singleAudioHtml(item));
                                                            } else if (item.accept === "file") {
                                                                $("#preview_" + item.name, item.parentElem).append(laytpUpload.singleFileHtml(item));
                                                            }
                                                            //input框增加文件值
                                                            var inputValue = $("#input_" + item.name).val();
                                                            var idValue = $("#id_" + item.name).val();
                                                            if (inputValue) {
                                                                $("#id_" + item.name, item.parentElem).val(idValue + laytpUpload.idSplitStr + item.data.id);
                                                                $("#input_" + item.name, item.parentElem).val(inputValue + laytpUpload.pathSplitStr + item.data.path);
                                                            } else {
                                                                $("#id_" + item.name, item.parentElem).val(item.data.id);
                                                                $("#input_" + item.name, item.parentElem).val(item.data.path);
                                                            }
                                                        } else {
                                                            //单个预览
                                                            if (item.accept === "image") {
                                                                $("#preview_" + item.name, item.parentElem).html(laytpUpload.singleImageHtml(item));
                                                            } else if (item.accept === "video") {
                                                                $("#preview_" + item.name, item.parentElem).html(laytpUpload.singleVideoHtml(item));
                                                            } else if (item.accept === "audio") {
                                                                $("#preview_" + item.name, item.parentElem).html(laytpUpload.singleAudioHtml(item));
                                                            } else if (item.accept === "file") {
                                                                $("#preview_" + item.name, item.parentElem).html(laytpUpload.singleFileHtml(item));
                                                            }
                                                            //input框增加文件值
                                                            $("#id_" + item.name, item.parentElem).val(item.data.id);
                                                            $("#input_" + item.name, item.parentElem).val(item.data.path);
                                                        }
                                                    }
                                                    // 预览都执行完毕之后，就把storeAsOptions数组清空
                                                    storeAsOptions = [];
                                                }
                                            });
                                        }
                                    });
                                });
                            }
                        });
                    });
                }
            });

            // 渲染选择附件按钮
            laytpUpload.renderChoiceFile(options);

            // 删除已经上传的东西
            $("body").off("click", ".upload_img_close, .upload_delete, .upload_file_close").on("click", ".upload_img_close, .upload_delete, .upload_file_close", function () {
                var id = $(this).data("id");
                var parentElem = $(this).data('parentelem');
                var idObj = (parentElem !== "undefined") ? $("#id_" + id, parentElem) : $("#id_" + id);
                var inputObj = (parentElem !== "undefined") ? $("#input_" + id, parentElem) : $("#input_" + id);
                if (optionsObj[id].multi) {
                    var fileUrl = $(this).data("fileurl");
                    var fileId = $(this).data("fileid");
                    var idValue = idObj.val();
                    var inputValue = inputObj.val();
                    var newIdValue = "";
                    var newInputValue = "";
                    if (inputValue.indexOf(fileUrl + laytpUpload.pathSplitStr) !== -1) {
                        var reg = new RegExp(fileUrl + laytpUpload.pathSplitStr);
                        newInputValue = inputValue.replace(reg, "");
                        var idReg = new RegExp(fileId + laytpUpload.idSplitStr);
                        newIdValue = idValue.replace(idReg, "");
                    } else {
                        if (inputValue.indexOf(laytpUpload.pathSplitStr + fileUrl) !== -1) {
                            var reg = new RegExp(laytpUpload.pathSplitStr + fileUrl);
                            newInputValue = inputValue.replace(reg, "");
                            var idReg = new RegExp(laytpUpload.idSplitStr + fileId);
                            newIdValue = idValue.replace(idReg, "");
                        } else {
                            var reg = new RegExp(fileUrl);
                            newInputValue = inputValue.replace(reg, "");
                            var idReg = new RegExp(fileId);
                            newIdValue = idValue.replace(idReg, "");
                        }
                    }
                    inputObj.val(newInputValue);
                    idObj.val(newIdValue);
                } else {
                    inputObj.val("");
                    idObj.val("");
                }
                $(this).closest("li").remove();
            });
        },

        hasUploadingStoreAsOptions: function () {
            for (let k in storeAsOptions) {
                if (storeAsOptions[k].status === "uploading") {
                    return true;
                }
            }
            return false;
        },

        // 选择附件按钮渲染
        renderChoiceFile: function(options){
            $("body").off("click", "#laytpCheckFileBtn_" + options.name).on("click", "#laytpCheckFileBtn_" + options.name, function () {
                var checkedIds = $("#id_"+options.name).val();
                facade.popupDiv({
                    title : "选择附件",
                    path : facade.compatibleHtmlPath("/admin/files/choice.html?checked_ids="+checkedIds+"&name=" + options.name + "&multi=" + options.multi + "&file_type=" + options.accept + "&category_id=" + options.fileCategoryId),
                    width : '95%',
                    height : '95%'
                });
            });
        },

        // 弹窗选择文件，选择完后，关闭弹窗执行此函数进行界面预览
        choiceAfter: function(name, multi, accept, checkedItems){
            $("#id_" + name, window.parent.document).val('');
            $("#input_" + name, window.parent.document).val('');
            $("li", $("#preview_" + name, window.parent.document)).remove();
            for(let k in checkedItems){
                var checkedItem = {};
                checkedItem.path = checkedItems[k].path;
                checkedItem.id = checkedItems[k].id;
                checkedItem.name = name;
                checkedItem.data = {
                    id : checkedItems[k].id,
                    path : checkedItem.path,
                    name : checkedItems[k].name,
                };
                if (multi) {
                    //多个预览
                    if (accept === "image") {
                        $("#preview_" + name, window.parent.document).append(laytpUpload.singleImageHtml(checkedItem));
                    } else if (accept === "video") {
                        $("#preview_" + name, window.parent.document).append(laytpUpload.singleVideoHtml(checkedItem));
                    } else if (accept === "audio") {
                        $("#preview_" + name, window.parent.document).append(laytpUpload.singleAudioHtml(checkedItem));
                    } else if (accept === "file") {
                        $("#preview_" + name, window.parent.document).append(laytpUpload.singleFileHtml(checkedItem));
                    }
                    //input框增加文件值
                    var inputValue = $("#input_" + name, window.parent.document).val();
                    var idValue = $("#id_" + name, window.parent.document).val();
                    if (inputValue) {
                        $("#id_" + name, window.parent.document).val(idValue + laytpUpload.idSplitStr + checkedItem.id);
                        $("#input_" + name, window.parent.document).val(inputValue + laytpUpload.pathSplitStr + checkedItem.path);
                    } else {
                        $("#id_" + name, window.parent.document).val(checkedItem.id);
                        $("#input_" + name, window.parent.document).val(checkedItem.path);
                    }
                } else {
                    //单个预览
                    if (accept === "image") {
                        $("#preview_" + name, window.parent.document).html(laytpUpload.singleImageHtml(checkedItem));
                    } else if (accept === "video") {
                        $("#preview_" + name, window.parent.document).html(laytpUpload.singleVideoHtml(checkedItem));
                    } else if (accept === "audio") {
                        $("#preview_" + name, window.parent.document).html(laytpUpload.singleAudioHtml(checkedItem));
                    } else if (accept === "file") {
                        $("#preview_" + name, window.parent.document).html(laytpUpload.singleFileHtml(checkedItem));
                    }
                    //input框增加文件值
                    $("#id_" + name, window.parent.document).val(checkedItem.id);
                    $("#input_" + name, window.parent.document).val(checkedItem.path);
                }
            }

            // 删除已经上传的东西
            parent.$("body").off("click", ".upload_img_close, .upload_delete, .upload_file_close").on("click", ".upload_img_close, .upload_delete, .upload_file_close", function () {
                var idObj = $("input",$(this).parent().parent().parent().parent().parent()).first();
                var inputObj = $("input",$(this).parent().parent().parent().parent().parent()).eq(1);
                if (multi) {
                    var fileUrl = $(this).data("fileurl");
                    var fileId = $(this).data("fileid");
                    var idValue = idObj.val();
                    var inputValue = inputObj.val();
                    var newIdValue = "";
                    var newInputValue = "";
                    if (inputValue.indexOf(fileUrl + laytpUpload.pathSplitStr) !== -1) {
                        var reg = new RegExp(fileUrl + laytpUpload.pathSplitStr);
                        newInputValue = inputValue.replace(reg, "");
                        var idReg = new RegExp(fileId + laytpUpload.idSplitStr);
                        newIdValue = idValue.replace(idReg, "");
                    } else {
                        if (inputValue.indexOf(laytpUpload.pathSplitStr + fileUrl) !== -1) {
                            var reg = new RegExp(laytpUpload.pathSplitStr + fileUrl);
                            newInputValue = inputValue.replace(reg, "");
                            var idReg = new RegExp(laytpUpload.idSplitStr + fileId);
                            newIdValue = idValue.replace(idReg, "");
                        } else {
                            var reg = new RegExp(fileUrl);
                            newInputValue = inputValue.replace(reg, "");
                            var idReg = new RegExp(fileId);
                            newIdValue = idValue.replace(idReg, "");
                        }
                    }
                    inputObj.val(newInputValue);
                    idObj.val(newIdValue);
                } else {
                    inputObj.val("");
                    idObj.val("");
                }
                $(this).closest("li").remove();
            });
        },
    };

    // 输出模块
    exports(MOD_NAME, laytpUpload);

    // 注入layui组件中，供全局调用
    layui.laytpUpload = laytpUpload;

    // 注入window全局对象中，供全局调用
    window.laytpUpload = laytpUpload;
});
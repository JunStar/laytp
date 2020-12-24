layui.define(['jquery'], function (exports) {
    layui.link("/static/library/meditor/css/editormd.css");

    layui.$.getScript("/static/library/meditor/editormd.js", function () {
        const MOD_NAME = 'meditor';
        let meditor = {};

        meditor.render = function (id) {
            var value = layui.$('#' + id).html();
            var name = layui.$('#' + id).data('name');
            editormd(id, {
                name: name,
                value: value,
                width: "100%",
                zIndex: 0,
                height: 720,
                syncScrolling: "single",
                path: '/static/library/meditor/lib/',
                emoji: {
                    path: '/static/library/meditor/plugins/emoji-dialog/emoji/',
                    ext: '.png'
                },
                taskList: true,
                tex: true, // 默认不解析
                flowChart: true, // 默认不解析
                sequenceDiagram: true, // 默认不解析
                imageUpload: true,
                imageFormats: ["jpg", "jpeg", "gif", "png", "bmp", "webp", "JPG", "JPEG", "GIF", "PNG", "BMP", "WEBP"],
                imageUploadURL: layui.facade.pluginUrl('meditor', '/common/upload', {
                    'accept': 'file',
                    'dir': 'meditor'
                })
            });
        };

        exports(MOD_NAME, meditor);
    });
});
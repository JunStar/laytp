layui.define(['jquery', 'layTp'], function (exports) {
    const MOD_NAME = 'meditor';
    let meditor = {};

    meditor.render = function (id) {
        var value = $('#' + id).html();
        var name = $('#' + id).attr('name');
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
            imageUploadURL: layui.layTp.facade.pluginUrl('core', '/common/upload', {'accept': 'file', 'dir': 'meditor'})
        });
    };

    layui.link("/static/library/meditor/css/editormd.css");

    exports(MOD_NAME, meditor);
});
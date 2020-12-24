layui.define(['jquery'], function (exports) {
    const MOD_NAME = 'ueditor';
    let ueditor = {};

    ueditor.render = function (id) {
        UE.getEditor(id, {
            zIndex: 0
            , serverUrl: layui.facade.pluginUrl('ueditor', '/common/upload', {'accept': 'file', 'dir': 'ueditor'})
            , imageFieldName: "file"
            , imageActionName: "uploadimage"
            , imageUrlPrefix: ""
        });
    };

    exports(MOD_NAME, ueditor);
});
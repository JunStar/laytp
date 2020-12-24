layui.define(['jquery'], function (exports) {
    const MOD_NAME = 'ueditor';
    let ueditor = {};

    ueditor.render = function (id) {
        UE.getEditor(id, {
            zIndex: 0
            ,
            serverUrl: layui.layTp.facade.addon_url('ueditor', '/api/common/upload', {
                'accept': 'file',
                'upload_dir': 'ueditor'
            })
            ,
            imageFieldName: "file"
            ,
            imageActionName: "uploadimage"
            ,
            imageUrlPrefix: ""
        });
    };

    exports(MOD_NAME, ueditor);
});
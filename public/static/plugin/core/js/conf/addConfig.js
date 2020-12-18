layui.use(['layTp'], function () {
    //监听选中类型下拉框的onchange事件
    window.selectType = function (params) {
        let nowValue = params.arr[0].value;
        let array_content_types = ['select_single', 'select_multi', 'checkbox', 'switch'];
        if (array_content_types.indexOf(nowValue) !== -1) {
            if (nowValue === "switch") {
                $('#form-item-content').find("label").val("数据列表");
                $('#form-item-content').find("textarea").val(
                    "关闭状态值=关闭状态文本\n" +
                    "打开状态值=打开状态文本");
            } else {
                $('#form-item-content').find("label").val("数据列表");
                $('#form-item-content').find("textarea").val(
                    "value1=title1\n" +
                    "value2=title2");
            }
            $('#form-item-content').show();
        } else if (nowValue === "image_single") {
            $('#form-item-content').find("label").html("上传配置");
            $('#form-item-content').find("textarea").css("height", "100px").val(
                "width=640\n" +
                "height=400\n" +
                "mime=jpg,jpeg,png,gif\n" +
                "size=100mb"
            );
            $('#form-item-content').show();
        } else if (nowValue === "image_multi") {
            $('#form-item-content').find("label").html("上传配置");
            $('#form-item-content').find("textarea").css("height", "115px").val(
                "width=640\n" +
                "height=400\n" +
                "mime=jpg,jpeg,png,gif\n" +
                "max=5\n" +
                "size=100mb"
            );
            $('#form-item-content').show();
        } else if (nowValue === "file_single") {
            $('#form-item-content').find("label").html("上传配置");
            $('#form-item-content').find("textarea").css("height", "100px").val(
                "mime=*\n" +
                "size=100mb"
            );
            $('#form-item-content').show();
        } else if (nowValue === "file_multi") {
            $('#form-item-content').find("label").html("上传配置");
            $('#form-item-content').find("textarea").css("height", "100px").val(
                "max=5\n" +
                "mime=*\n" +
                "size=100mb"
            );
            $('#form-item-content').show();
        } else {
            $('#form-item-content').hide();
        }
    };
});
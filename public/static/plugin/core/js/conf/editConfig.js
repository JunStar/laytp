layui.use(['layTp'], function () {

    let type = $("#hiddenType").val();
    let content = $("#hiddenContent").val();
    let contentJson = content ? JSON.parse(content) : "";

    window.selectType = function (params) {
        let nowValue = params.arr[0].value;
        window.showMoreConf(nowValue);
    };

    let contentArr = {
        switch: "关闭状态值=关闭状态文本\n" +
            "打开状态值=打开状态文本",
        select_single: "value1=title1\n" +
            "value2=title2",
        select_multi: "value1=title1\n" +
            "value2=title2",
        checkbox: "value1=title1\n" +
            "value2=title2",
        image_single: "width=640\n" +
            "height=400\n" +
            "mime=jpg,jpeg,png,gif\n" +
            "size=100mb",
        image_multi: "width=640\n" +
            "height=400\n" +
            "mime=jpg,jpeg,png,gif\n" +
            "max=5\n" +
            "size=100mb",
        file_single: "mime=*\n" +
            "size=100mb",
        file_multi: "max=5\n" +
            "mime=*\n" +
            "size=100mb"
    };

    contentArr[type] = formatContent(contentJson);

    function formatContent(obj) {
        let key, content = "";
        if (facade.isArray(obj)) {
            for (key in obj) {
                content += obj[key].value + "=" + obj[key].text + "\n";
            }
        } else {
            for (key in obj) {
                content += key + "=" + obj[key] + "\n";
            }
        }
        return facade.rtrim(content, "\n");
    }

    window.showMoreConf = function (nowValue) {
        let array_content_types = ['select_single', 'select_multi', 'checkbox', 'switch'];
        if (array_content_types.indexOf(nowValue) !== -1) {
            if (nowValue === "switch") {
                $('#form-item-content').find("label").val("数据列表");
                $('#form-item-content').find("textarea").val(contentArr[nowValue]);
            } else {
                $('#form-item-content').find("label").val("数据列表");
                $('#form-item-content').find("textarea").val(contentArr[nowValue]);
            }
            $('#form-item-content').show();
        } else if (nowValue === "image_single") {
            $('#form-item-content').find("label").html("上传配置");
            $('#form-item-content').find("textarea").css("height", "100px").val(contentArr[nowValue]);
            $('#form-item-content').show();
        } else if (nowValue === "image_multi") {
            $('#form-item-content').find("label").html("上传配置");
            $('#form-item-content').find("textarea").css("height", "115px").val(contentArr[nowValue]);
            $('#form-item-content').show();
        } else if (nowValue === "file_single") {
            $('#form-item-content').find("label").html("上传配置");
            $('#form-item-content').find("textarea").css("height", "100px").val(contentArr[nowValue]);
            $('#form-item-content').show();
        } else if (nowValue === "file_multi") {
            $('#form-item-content').find("label").html("上传配置");
            $('#form-item-content').find("textarea").css("height", "100px").val(contentArr[nowValue]);
            $('#form-item-content').show();
        } else {
            $('#form-item-content').hide();
        }
    };

    $(document).ready(function () {
        window.showMoreConf(type);
    });
});
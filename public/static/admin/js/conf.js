layui.use(["laytp"], function () {
    const funController = {};

    let nowGroup = 'basic';

    //获取分组列表进行渲染
    funController.getGroup = function () {
        facade.ajax({method: "GET", route: "/admin.conf/getGroup", successAlert: false}).done(function (res) {
            let addGroupBtnHtml = "";
            if (facade.hasAuth("/admin.conf/addGroup")) {
                addGroupBtnHtml =
                    "<div class=\"layui-col-lg6 layui-col-md6 layui-col-xs6\">\n" +
                    "    <div style=\"text-align: center\">\n" +
                    "        <button class=\"laytp-btn laytp-btn-default laytp-btn-xs add-group\"><i class='layui-icon layui-icon-add-1'></i>分组</button>\n" +
                    "    </div>\n" +
                    "</div>\n";
            }
            let addConfBtnHtml = "";
            if (facade.hasAuth("/admin.conf/addConfig")) {
                addConfBtnHtml =
                    "<div class=\"layui-col-lg6 layui-col-md6 layui-col-xs6\">\n" +
                    "    <div style=\"text-align: center\">\n" +
                    "        <button class=\"laytp-btn laytp-btn-primary laytp-btn-xs add-config\"><i class='layui-icon layui-icon-add-1'></i>配置</button>\n" +
                    "    </div>\n" +
                    "</div>\n";
            }
            let groupHtml =
                "<span class=\"layui-tab-vertical-btn\">\n" +
                "    <div class=\"layui-row\">\n" +
                addGroupBtnHtml +
                addConfBtnHtml +
                "    </div>\n" +
                "</span>";
            let key;
            for (key in res.data) {
                if (res.data[key]["value"] === "basic") {
                    groupHtml += "<li class=\"groupLi layui-this\" data-group=\"" + res.data[key]["value"] + "\"><i class='" + res.data[key]["icon"] + " margin-right5'></i>" + res.data[key]["name"] + "</li>";
                } else {
                    groupHtml += "<li class=\"groupLi\" data-group=\"" + res.data[key]["value"] + "\"><i class='" + res.data[key]["icon"] + " margin-right5'></i>" + res.data[key]["name"] + "</li>";
                }
            }
            $("#groupList").html(groupHtml);
        });
    };

    //渲染某个分组下所有的配置项
    funController.showGroup = function (group) {
        facade.ajax({
            method: "GET",
            route: "/admin.conf/getGroupItem",
            data: {"group": group},
            async: false,
            successAlert: false
        }).done(function (res) {
            let configTemplate = layui.laytpl($("#configTemplate").html()).render({"group": group, "list": res.data});
            $("#configContent").html(configTemplate);
            layui.laytpForm.render();
            layui.form.render();
            if ($(".layui-tab-title").height() < $(".layui-tab-content").height()) {
                $(".layui-tab-title").height($(".layui-tab-content").height() + 20);
            }
        });
    };

    //添加分组按钮绑定点击事件
    $(document).off("click", ".add-group").on("click", ".add-group", function () {
        facade.popupDiv({
            title: "添加分组"
            , path: "/admin/conf/addGroup.html"
        });
    });

    //删除分组
    $(document).off("click", ".delete-group").on("click", ".delete-group", function () {
        let group = $("#group").val();
        facade.popupConfirm({
            text: "确定删除此分组吗？",
            route: "/admin.conf/delGroup",
            data: {group: group}
        }, function () {
            funController.getGroup();
            funController.showGroup("basic");
            // facade.ajax({
            //     method: "GET",
            //     route: "plugin/core/common/getCache",
            //     async: false,
            //     successAlert: false
            // }).done(function (res) {
            //     if (res.code === 0) {
            //         sysConf = res.data["sysConf"] ? res.data["sysConf"] : "";
            //         user = res.data["user"] ? res.data["user"] : "";
            //         menu = res.data["menu"] ? res.data["menu"] : "";
            //         pluginConf = res.data["pluginConf"] ? res.data["pluginConf"] : "";
            //         authList = menu.authList;
            //     } else {
            //         facade.getCookie(tokenCookieKey);
            //         facade.redirect("/admin/login.html");
            //         return false;
            //     }
            // });
        });
    });

    //编辑分组
    $(document).off("click", ".edit-group").on("click", ".edit-group", function () {
        facade.popupDiv({
            title: "编辑分组",
            path: "/admin/conf/editGroup.html?nowGroup="+nowGroup
        });
    });

    //添加配置按钮绑定点击事件
    $(document).off("click", ".add-config").on("click", ".add-config", function () {
        facade.popupDiv({
            title: "添加配置",
            path: "/admin/conf/addConfig.html?nowGroup="+nowGroup,
            width: "80%",
            height: "90%"
        });
    });

    //编辑配置按钮绑定点击事件
    $(document).off("click", ".edit-config").on("click", ".edit-config", function () {
        let id = $(this).data("id");
        facade.popupDiv({
            title: "添加配置",
            path: "/admin/conf/editConfig.html?id="+id+"&nowGroup="+nowGroup,
            width: "80%",
            height: "90%"
        });
    });

    //删除配置项按钮绑定点击事件
    $(document).off("click", ".delete-config").on("click", ".delete-config", function () {
        let id = $(this).data('id');
        facade.popupConfirm({text: "确定删除此配置项吗？", route: "/admin.conf/delConfig", data: {"id": id}}, function (res) {
            if(res.code === 0){
                $("#item-" + id).remove();
            }
        });
    });

    //左侧分组列表绑定点击事件
    $(document).off("click", ".groupLi").on("click", ".groupLi", function () {
        nowGroup = $(this).data("group");
        funController.showGroup(nowGroup);
    });

    //数组类型配置项，点击追加按钮
    $(document).on('click', '.add_array_item', function () {
        let click_obj = $(this);
        let config_key = click_obj.attr('config_key');
        layui.laytpl($('#array_item_html').html()).render({
            config_key: config_key
        }, function (string) {
            click_obj.parent().parent().before(string);
        });
    });

    //数组类型配置项，删除数组某个元素
    $(document).on('click','.del_array_item',function(){
        let click_obj = $(this);
        click_obj.parent().parent().remove();
    });

    //表单提交绑定事件
    layui.form.on('submit(laytp-form)', function (data) {
        data = facade.setEditorField(data);
        facade.ajax({route: "/admin.conf/set", data: data.field});
        return false;
    });

    //页面加载完成就执行的操作
    $(document).ready(function () {
        funController.getGroup();
        funController.showGroup(nowGroup);
    });

    window.funController = funController;
});
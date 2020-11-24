layui.use(["layTp"], function () {
    const funController = {};

    let nowGroup = 'basic';

    //获取分组列表进行渲染
    funController.getGroup = function () {
        facade.ajax({path: "plugin/core/conf/getGroup", successAlert: false}).done(function (res) {
            let addGroupBtnHtml = "";
            if (facade.hasAuth("plugin/core/conf/addGroup")) {
                addGroupBtnHtml =
                    "<div class=\"layui-col-lg6 layui-col-md6 layui-col-xs6\">\n" +
                    "    <div style=\"text-align: center\">\n" +
                    "        <button class=\"layui-btn layui-btn-default layui-btn-xs add-group layui-icon layui-icon-add-1\">分组</button>\n" +
                    "    </div>\n" +
                    "</div>\n";
            }
            let addConfBtnHtml = "";
            if (facade.hasAuth("plugin/core/conf/addConfig")) {
                addConfBtnHtml =
                    "<div class=\"layui-col-lg6 layui-col-md6 layui-col-xs6\">\n" +
                    "    <div style=\"text-align: center\">\n" +
                    "        <button class=\"layui-btn layui-btn-default layui-btn-xs add-config  layui-icon layui-icon-add-1\">配置</button>\n" +
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
            path: "plugin/core/conf/getGroupItem",
            params: {"group": group},
            successAlert: false
        }).done(function (res) {
            let configTemplate = layui.laytpl($("#configTemplate").html()).render({"group": group, "list": res.data});
            $("#configContent").html(configTemplate);
            layui.layTpForm.render();
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
            , path: "plugin/core/conf/addGroup"
        }, function (data) {
            let groupHtml = "<li class=\"groupLi\" data-group=\"" + data.group + "\"><i class='" + data.icon + " margin-right5'></i>" + data.group_name + "</li>";
            $("#groupList").append(groupHtml);
            layTp.init.config();
        });
    });

    //删除分组
    $(document).off("click", ".delete-group").on("click", ".delete-group", function () {
        let group = $("#group").val();
        facade.popupConfirm({
            text: "确定删除此分组吗？",
            path: "plugin/core/conf/delGroup",
            params: {group: group}
        }, function () {
            funController.getGroup();
            funController.showGroup("basic");
            layTp.init.config();
        });
    });

    //编辑分组
    $(document).off("click", ".edit-group").on("click", ".edit-group", function () {
        let key, groupName, icon;
        for (key in sysConf.layTpSys.configGroup) {
            if (sysConf.layTpSys.configGroup[key].value.toString() === nowGroup.toString()) {
                groupName = sysConf.layTpSys.configGroup[key].name;
                icon = sysConf.layTpSys.configGroup[key].icon;
                break;
            }
        }
        facade.popupDiv({
            title: "编辑分组",
            path: "plugin/core/conf/editGroup",
            data: {group: nowGroup, group_name: groupName, icon: icon}
        }, function (data) {
            $("li[class='groupLi layui-this']", "#groupList").html("<i class='" + data.icon + " margin-right5'></i>" + data.group_name);
            layTp.init.config();
        });
    });

    //添加配置按钮绑定点击事件
    $(document).off("click", ".add-config").on("click", ".add-config", function () {
        facade.popupDiv({
            title: "添加配置",
            path: "plugin/core/conf/addConfig"
        }, function (data) {
            if (data.group === nowGroup.toString()) {
                funController.showGroup(nowGroup);
            }
            layTp.init.config();
        });
    });

    //编辑配置按钮绑定点击事件
    $(document).off("click", ".edit-config").on("click", ".edit-config", function () {
        let id = $(this).data('id');
        facade.ajax({
            path: "plugin/core/conf/editConfig",
            params: {id: id, get_data: true},
            async: false,
            successAlert: false
        }).done(function (res) {
            facade.popupDiv({title: "编辑配置", path: "plugin/core/conf/editConfig", data: res.data}, function (data) {
                if (data.group === nowGroup.toString()) {
                    funController.showGroup(nowGroup);
                }
                layTp.init.config();
            });
        });
    });

    //删除配置项按钮绑定点击事件
    $(document).off("click", ".delete-config").on("click", ".delete-config", function () {
        let id = $(this).data('id');
        facade.popupConfirm({text: "确定删除此配置项吗？", path: "plugin/core/conf/delConfig", params: {"ids": id}}, function () {
            $("#item-" + id).remove();
            layTp.init.config();
        });
    });

    //左侧分组列表绑定点击事件
    $(document).off("click", ".groupLi").on("click", ".groupLi", function () {
        nowGroup = $(this).data("group");
        funController.showGroup(nowGroup);
    });

    //表单提交绑定事件
    layui.form.on('submit(laytp-form)', function (obj) {
        facade.ajax({path: "plugin/core/conf/set", params: obj.field}).done(function () {
            layTp.init.config();
        });
        return false;
    });

    //页面加载完成就执行的操作
    $(document).ready(function () {
        funController.getGroup();
        funController.showGroup(nowGroup);
    });

    window.funController = funController;
});
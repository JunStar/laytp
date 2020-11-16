layui.use(["layTp"], function () {
    //退出登录
    $(document).off("click", "[laytp-event=logout]").on("click", "[laytp-event=logout]", function () {
        facade.delCookie("laytp_admin_token");
        facade.redirect("/admin/login.html");
    });

    //弹窗展示主题列表
    $(document).off("click", "#laytp_theme").on("click", "#laytp_theme", function () {
        layui.layer.open({
            type: 1,
            title: "主题样式",
            content: $("#theme").html(),
            area: ["300px", "100%"],
            offset: ["50px", parseInt(document.body.offsetWidth - 300) + "px"],
            maxmin: false,
            anim: 2,
            shadeClose: true,
            end: function (index, layero) {
                $("#laytp_theme").parent("li").removeClass("layui-this");
            }
        });
    });

    //绑定样式选择点击事件
    $(document).off("click", "[laytp-event='setTheme']").on("click", "[laytp-event='setTheme']", function () {
        let index = $(this).data("index");
        $("body").attr("class", "layui-layout-body laytp-theme-" + index);
        facade.setCookie("theme", index, 365);
    });

    //页面加载完毕
    $(document).ready(function () {
        //获取token，根据token判断用户是否已经登录，未登录跳转至登录页面
        let layTpAdminToken = facade.getCookie("laytp_admin_token");
        if (!layTpAdminToken) facade.redirect("/admin/login.html");
        layTpMenu.render();

        //渲染用户信息
        $(".layui-nav-img").attr("src", user.avatar);
        $(".laytp-nickname").html(user.nickname);
        $(".img-circle").attr("src", user.avatar);
        $(".laytp-nickname").html(user.nickname);

        let theme = facade.getCookie("theme");
        if (!theme) {
            theme = defaultTheme;
        }
        //默认body的style=display:none;把主题class设置完成后再显示，否则会颜色闪烁
        $("body").attr("class", "layui-layout-body laytp-theme-" + theme).show();
    });
});
layui.use(["layTp"], function () {
    const
        $ = layui.$
        , tree = layui.layTpTree
        , facade = layui.facade
        , funController = {}
    ;

    //添加表，绑定点击事件
    $("a[lay-event='create']").on("click", function () {
        facade.ajax({
            path: "plugin/core/autocreate.api/create"
        });
    });

    //添加表，绑定点击事件
    $("a[lay-event='open']").on("click", function () {
        window.open("/api.html");
    });
});
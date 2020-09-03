layui.use(["layTp"], function () {
    const funcController = {};

    let nowGroup = 'basic';

    //获取分组列表进行渲染
    funcController.getGroup = function(){
        facade.ajax({path:"admin/sysconf/getGroup",successAlert:false}).done(function(res){
            let groupHtml = "<span class=\"layui-tab-vertical-btn\">\n" +
                "                <div class=\"layui-row\">\n" +
                "                    <div class=\"layui-col-lg6 layui-col-md6 layui-col-xs6\">\n" +
                "                        <div style=\"text-align: center\">\n" +
                "                            <button class=\"layui-btn layui-btn-normal layui-btn-xs add-group\">+分组</button>\n" +
                "                        </div>\n" +
                "                    </div>\n" +
                "                    <div class=\"layui-col-lg6 layui-col-md6 layui-col-xs6\">\n" +
                "                        <div style=\"text-align: center\">\n" +
                "                            <button class=\"layui-btn layui-btn-normal layui-btn-xs add-config\">+配置</button>\n" +
                "                        </div>\n" +
                "                    </div>\n" +
                "                </div>\n" +
                "            </span>";
            let key;
            for(key in res.data){
                if(key === "basic"){
                    groupHtml += "<li class=\"layui-this groupLi\" data-group=\""+key+"\"><span>"+res.data[key]+"</span></li>";
                }else{
                    groupHtml += "<li class=\"groupLi\" data-group=\""+key+"\"><span>"+res.data[key]+"</span></li>";
                }
            }
            $("#groupList").html(groupHtml);
        });
    };

    //渲染某个分组下所有的配置项
    funcController.showGroup = function(group){
        facade.ajax({path:"admin/sysconf/getGroupItem",params:{"group":group},successAlert:false}).done(function(res){
            let configTemplate = layui.laytpl($("#configTemplate").html()).render({"group":group,"list":res.data});
            $("#configContent").html(configTemplate);
            layui.layTpForm.render();
            layui.form.render();
            if($(".layui-tab-title").height() < $(".layui-tab-content").height()){
                $(".layui-tab-title").height($(".layui-tab-content").height() + 20);
            }
        });
    };

    //添加分组按钮绑定点击事件
    $(document).off("click",".add-group").on("click",".add-group",function(){
        facade.popupDiv({title:"添加分组", url:"a/sysconf/addGroup"}, function(data){
            let groupHtml = "<li class=\"groupLi\" data-group=\""+data.group+"\"><span>"+data.group_name+"</span></li>";
            $("#groupList").append(groupHtml);
        });
    });

    //删除分组
    $(document).off("click",".delete-group").on("click",".delete-group",function(){
        let group = $("#group").val();
        facade.popupConfirm({text:"确定删除吗？",url:"admin/sysconf/delGroup",params:{group:group}},function(){
            funcController.getGroup();
            funcController.showGroup('basic');
        });
    });

    //添加配置按钮绑定点击事件
    $(document).off("click",".add-config").on("click",".add-config",function(){
        facade.popupDiv({title:"添加配置", url:"a/sysconf/addConfig"}, function(){
            funcController.showGroup(nowGroup);
        });
    });

    //删除配置项按钮绑定点击事件
    $(document).off("click",".delete-config").on("click",".delete-config",function(){
        let id = $(this).data('id');
        facade.popupConfirm({text:"确定删除吗？",url:"admin/sysconf/del",params:{"ids":id}},function(){
            $("#item-"+id).remove();
        });
    });

    //左侧分组列表绑定点击事件
    $(document).off("click",".groupLi").on("click",".groupLi",function(){
        nowGroup = $(this).data("group");
        funcController.showGroup(nowGroup);
    });

    //表单提交绑定事件
    layui.form.on('submit(laytp-form)',function(obj){
        facade.ajax({path:"admin/sysconf/set",params:obj.field}).done();
        return false;
    });

    //页面加载完成就执行的操作
    $(document).ready(function(){
        funcController.getGroup();
        funcController.showGroup(nowGroup);
    });

    window.funcController = funcController;
});
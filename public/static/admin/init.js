/* 对页面上有指定class或者属性的节点绑定相应事件 */
(function func_init() {
    var init = {};

    //添加按钮
    init.btn_add = function(){
        $('.btn-add').click(function(){
            var url = $(this).data("open");
            facade.popup_frame('添加', url, '800px', '500px');
        });
    }

    //弹框二次选择 - 举例：菜单管理-》添加菜单-》图标-》搜索图标
    init.popup_select = function(){
        $('.popup-select').click(function(){
            var title = $(this).text();
            var template_id = $(this).data('template_id');
            var source = $(this).data('source');
            $.get(source,{},function(res){
                var data = res.data;
                laytpl($('#'+template_id).html()).render(data, function(html){
                    facade.popup_div(title, html, '99%', '98%');
                });
            });
        });
    }

    for(key in init){
        eval("init."+key+"();");
    }
})()
/* 对页面上有指定class或者属性的节点绑定相应事件 */
(function func_init() {
    var init = {};

    //添加按钮
    init.btn_add = function(){
        // $('.btn-add').click(function(){
        $(document).on('click','.btn-add',function(){
            var url = $(this).data("open");
            facade.popup_frame('添加', url, '800px', '500px');
        });
    }

    //弹框二次选择 - 举例：菜单管理-》添加菜单-》图标-》搜索图标
    /**
     * class="popup-select" 表示弹窗选择的节点，绑定点击事件
     *  data-template_id="$template_id" 表示弹窗内容的模板ID，这里的模板使用laytpl的js模板
     *  data-source="$source" 表示模板内容要使用的数据源,一般是一个url地址
     *  data-input_id="表示二次选择弹出层得到的值要输入到哪个input节点的ID属性值"
     *
     * .pop-select-to-input 弹出层中的节点绑定点击事件
     *  data-input_val="value" 表示将value放到data-input_id节点中
     */
    init.popup_select = function(){
        $(document).on('click','.popup-select',function(){
            var title = $(this).text();
            var template_id = $(this).data('template_id');
            var source = $(this).data('source');
            pop_select_input = $(this).data('input_id');
            $.get(source,{},function(res){
                var data = res.data;
                laytpl($('#'+template_id).html()).render(data, function(html){
                    facade.popup_div(title, html, '99%', '98%');
                });
            });
        });

        $(document).on('click','.pop-select-to-input',function(){
            var value = $(this).data('input_value');
            $("#"+pop_select_input).val(value);
            layer.closeAll();
        });
    }

    //表单绑定事件
    init.form = function(){
        form.on('submit(*)', function(data){
            $.ajax({
                type: 'POST',
                url: window.location.href,
                data: data.field,
                dataType: 'json',
                success: function (res) {
                    facade.success('操作成功');
                },
                error: function (xhr) {
                    if( xhr.status == '500' ){
                        facade.error('本地网络问题或者服务器错误');
                    }else if( xhr.status == '404' ){
                        facade.error('请求地址不存在');
                    }
                }
            });
            return false; //阻止表单跳转。如果需要表单跳转，去掉这段即可。
        });
    }

    for(key in init){
        eval("init."+key+"();");
    }
})()
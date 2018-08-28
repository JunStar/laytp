/* 对页面上有指定class或者属性的节点绑定相应事件 */
(function func_init() {
    var init = {};

    /**
     * 弹窗展示页面
     * 使用实例：1.添加按钮
     */
    init.popup_frame = function(){
        $(document).on('click','.popup-frame',function(){
            var url = $(this).data("open");
            var width = $(this).data("width");
            var height = $(this).data("height");
            if( !width ){
                width = '800px';
            }
            if( !height ){
                height = '500px';
            }
            facade.popup_frame('添加', url, width, height);
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

    /**
     * 表单提交按钮绑定事件，包括添加表单、编辑表单和搜索表单
     * <form class="layui-form">
     *  <button class="layui-btn layui-btn-sm" lay-submit lay-filter="*">立即提交</button>
     * </form>
     * 重要的是
     *  1.form的class值为layui-form;
     *  2.button的属性值lay-submit lay-filter="*";
     */
    init.form = function(){
        form.on('submit(default_submit)', function(data){
            //当前url的action值为index，搜索表单进行了提交
            if( action == 'index' ){
                index(data);
            //当前url的action值为add，添加表单进行了提交
            }else if( action == 'add' ){
                do_update(data);
            //当前url的action值为edit，编辑表单进行了提交
            }else if( action == 'edit' ){
                do_update(data);
            }
            return false; //阻止表单跳转。如果需要表单跳转，去掉这段即可。
        });

        //搜索表单进行提交时，触发的方法
        function index(data){
            func_controller.table_render(data.field);
        }

        //添加和编辑的表单进行提交时触发的方法
        function do_update(data){
            $.ajax({
                type: 'POST',
                url: window.location.href,
                data: data.field,
                dataType: 'json',
                success: function (res) {
                    if( res.code == 1 ){
                        facade.success(res.msg);
                        parent.func_controller.table_render();
                        parent.layer.closeAll();
                    }else{
                        facade.error(res.msg);
                    }
                },
                error: function (xhr) {
                    if( xhr.status == '500' ){
                        facade.error('本地网络问题或者服务器错误');
                    }else if( xhr.status == '404' ){
                        facade.error('请求地址不存在');
                    }
                }
            });
        }
    }

    /**
     * 搜索表单，添加搜索条件绑定事件
     */
    var add_search_condition_click_num = 0;
    init.add_search_condition = function(){
        $(document).on('click','.add_search_condition',function(){
            var search_condition_tpl = $('#search_condition_tpl').html();
            add_search_condition_click_num = add_search_condition_click_num + 1;
            laytpl(search_condition_tpl).render(add_search_condition_click_num, function(html){
                $('form > div').append(html);
                form.render();
            });
        });
    }

    for(key in init){
        eval("init."+key+"();");
    }
})()
layui.use(['layTp'],function() {
    const
        layTp = layui.layTp
        , $ = layui.jquery
    ;

    //监听选择表下拉框onchange事件
    layui.form.on('select(select_type)',function(data){
        let array_content_types = ['select_single','select_multi','checkbox','radio'];
        if( array_content_types.indexOf(data.value) != -1 ){
            $('#form-item-content').show();
        }else{
            $('#form-item-content').hide();
        }
    });

    $(document).on('click','.add_array_item',function(){
        let click_obj = $(this);
        let config_key = click_obj.attr('config_key');
        layui.laytpl($('#array_item_html').html()).render({
            config_key: config_key
        }, function(string){
            click_obj.parent().parent().before(string);
        });
    });

    $(document).on('click','.del_array_item',function(){
        let click_obj = $(this);
        click_obj.parent().parent().remove();
    });
});
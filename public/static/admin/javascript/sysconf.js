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
});
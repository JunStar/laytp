/* 对页面上有指定class或者属性的节点绑定相应事件 */
(function func_init() {
    var init = {};

    //ajax设置
    init.ajaxSet = function(){
        // 设置AJAX请求时某些参数的的默认值
        $.ajaxSetup({
            dataType: "json",
            type: "POST",
            contentType: "application/x-www-form-urlencoded; charset=UTF-8",
            //设置AJAX请求时，带上cookie
            xhrFields : {
                withCredentials : true
            },
        });
    }

    //添加按钮
    init.btnAdd = function(){
        $('.btn-add').click(function(){
            var url = $(this).data("open");
            facade.popup('添加',url, 800, 500);
        });
    }

    //ajax表单
    init.ajaxForm = function(){
        $('[ajax-form]').submit(function(){
            $.ajax({
                type: $("#ajax-form").attr("method"),
                url: $("#ajax-form").attr("action"),
                data: $("#ajax-form").serialize(),
                success : function(data) {
                    if(data.code == 1)
                    {
                        layer.msg(data.msg,{icon:1});
                        if( data.url != "" )
                        {
                            //获取iframe层索引
                            var  frameindex= parent.layer.getFrameIndex(window.name);
                            if( frameindex > 0 )
                            {
                                //下面两句，并不能让location.reload()生效
                                // parent.layer.closeAll();
                                // location.reload();
                                //于是乎，直接刷新父级的url
                                parent.location.reload();
                            }
                            else
                            {
                                setTimeout( "phpby.main.redirect('"+data.url+"')", 1000 );
                            }
                        }
                    }
                    else
                    {
                        layer.msg(data.msg,{icon:2});
                    }
                },
                error: function (data){
                    layer.msg('数据返回错误',{icon:2});
                }
            }).fail(function(){
                layer.msg('网络错误',{icon:2});
            });
            return false;
        });
    }

    init.ajaxSet();
    init.btnAdd();
})()
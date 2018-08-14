(function func_app() {
    window.junadmin = {};  //全局对象

    var main = {};

    main.redirect = function(url)
    {
        location.href = url;
    }

    //AJAX全局配置
    main.ajaxSet = function(){
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

    main.ajaxForm = function()
    {
        $('#ajax-form').submit(function(){
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

    main.ajaxDel = function()
    {
        $("[ajax-del]").click(function(){
            var url = $(this).attr('ajax-del');
            layer.open({
                title: '提示',
                content: '此操作不可恢复，确定继续吗？',
                //20秒后自动关闭，0表示不自动关闭
                time: 20000,
                btn: ['确定', '关闭'],
                yes: function(index){
                    layer.close(index);
                    $.ajax({
                        url : url,
                        async : false,//设置为同步请求
                        success : function(data) {
                            if(data.code == 1)
                            {
                                layer.msg(data.msg,{icon:1});
                                if( data.url != "" )
                                {
                                    setTimeout( "phpby.main.redirect('"+data.url+"')", 1000 );
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
                },
                btn2: function(){
                    layer.closeAll();
                }
            });
        });
    }

    /**
     * 弹框打开一个iFrame
     * @title 为Frame的标题
     * @url 为Frame的src值
     */
    main.openFrame = function(title, url, width, height)
    {
        if(width == undefined) {
            width = 500;
        }
        if(height == undefined) {
            height = 220;
        }
        layer.open({
            type : 2,
            title : title,
            content : url,
            area: [width+'px',height+'px'],
        });
    }

    main.editor = function(id)
    {
        editormd(id, {
            width: "100%",
            height: 720,
            syncScrolling: "single",
            path: '/static/common/editor.md/lib/',
            emoji:{
                path: '/static/common/editor.md/plugins/emoji-dialog/emoji/',
                ext: '.png'
            },
            taskList: true,
            tex: true, // 默认不解析
            flowChart: true, // 默认不解析
            sequenceDiagram: true, // 默认不解析
            imageUpload: true,
            imageFormats: ["jpg", "jpeg", "gif", "png", "bmp", "webp", "JPG", "JPEG", "GIF", "PNG", "BMP", "WEBP"],
            imageUploadURL: upload_url
        });
    }

    main.init = function()
    {
        main.ajaxSet();
        main.ajaxForm();
        main.ajaxDel();
    }

    main.init();

    window.junadmin.main = main;
})()

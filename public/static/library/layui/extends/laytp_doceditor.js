/**
 * laytp的文档编辑器
 */
layui.define(['jquery'], function(exports){
    const MOD_NAME = 'laytp_doceditor';
    let laytp_doceditor = {};
    const $ = layui.jquery;
    const layer = layui.layer;
    const device = layui.device();

    laytp_doceditor.facade = {
        //组装成url
        url: function(path, params){
            let count = 0;
            for(k in params){
                if(params.hasOwnProperty(k)){
                    count++;
                }
            }
            path = path.replace('\.html','');
            if( typeof params !== 'undefined' && count > 0 ){
                params = $.param(params);
                let reg = new RegExp('=','g');
                params = params.replace(reg,'/');
                params = params.replace('&','/');
                params = '/' + params;
            }else{
                params = '';
            }
            path = '/' + path.replace(/(^\/)|(\/$)/,'');

            let url = path + params;
            return url;
        },

        //焦点处插入文本
        focusInsert: function(obj, str){
            var result, val = obj.value;
            obj.focus();
            if(document.selection){ //ie
                result = document.selection.createRange();
                document.selection.empty();
                result.text = str;
            } else {
                result = [val.substring(0, obj.selectionStart), str, val.substr(obj.selectionEnd)];
                obj.focus();
                obj.value = result.join('');
            }
        },

        //转义基础函数
        escape: function(html){
            return String(html||'').replace(/&(?!#?[a-zA-Z0-9]+;)/g, '&amp;')
                .replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/'/g, '&#39;').replace(/"/g, '&quot;');
        },

        //预览内容转义
        content: function(content){
            var util = laytp_doceditor.facade
                ,item = util.faces;

            //支持的html标签
            var html = function(end){
                return new RegExp('\\n*\\|\\-'+ (end||'') +'(div|span|p|button|table|thead|th|tbody|tr|td|ul|li|ol|li|dl|dt|dd|h2|h3|h4|h5)([\\s\\S]*?)\\-\\|\\n*', 'g');
            };

            //XSS
            content = util.escape(content||'')

            //转义图片
                .replace(/img\[([^\s]+?)\]/g, function(img){
                    return '<img src="' + img.replace(/(^img\[)|(\]$)/g, '') + '">';
                })

                //转义@
                .replace(/@(\S+)(\s+?|$)/g, '@<a href="javascript:;" class="fly-aite">$1</a>$2')

                //转义表情
                .replace(/face\[([^\s\[\]]+?)\]/g, function(face){
                    var alt = face.replace(/^face/g, '');
                    return '<img alt="'+ alt +'" title="'+ alt +'" src="' + item[alt] + '">';
                })

                //转义脚本
                .replace(/a(\(javascript:)(.+)(;*\))/g, 'a(javascript:layer.msg(\'非法脚本\');)')

                //转义链接
                .replace(/a\([\s\S]+?\)\[[\s\S]*?\]/g, function(str){
                    var href = (str.match(/a\(([\s\S]+?)\)\[/)||[])[1];
                    var text = (str.match(/\)\[([\s\S]*?)\]/)||[])[1];
                    if(!href) return str;
                    var rel =  /^(http(s)*:\/\/)\b(?!(\w+\.)*(sentsin.com|layui.com))\b/.test(href.replace(/\s/g, ''));
                    return '<a href="'+ href +'" target="_blank"'+ (rel ? ' rel="nofollow"' : '') +'>'+ (text||href) +'</a>';
                })

                //转义横线
                .replace(/\[hr\]\n*/g, '<hr>')

                //转义表格
                .replace(/\[table\]([\s\S]*)\[\/table\]\n*/g, function(str){
                    return str.replace(/\[(thead|th|tbody|tr|td)\]\n*/g, '<$1>')
                        .replace(/\n*\[\/(thead|th|tbody|tr|td)\]\n*/g, '</$1>')

                        .replace(/\[table\]\n*/g, '<table class="layui-table">')
                        .replace(/\n*\[\/table\]\n*/g, '</table>');
                })

                //转义 div/span
                .replace(/\n*\[(div|span)([\s\S]*?)\]([\s\S]*?)\[\/(div|span)\]\n*/g, function(str){
                    return str.replace(/\[(div|span)([\s\S]*?)\]\n*/g, '<$1 $2>')
                        .replace(/\n*\[\/(div|span)\]\n*/g, '</$1>');
                })

                //转义列表
                .replace(/\[ul\]([\s\S]*)\[\/ul\]\n*/g, function(str){
                    return str.replace(/\[li\]\n*/g, '<li>')
                        .replace(/\n*\[\/li\]\n*/g, '</li>')

                        .replace(/\[ul\]\n*/g, '<ul>')
                        .replace(/\n*\[\/ul\]\n*/g, '</ul>');
                })

                //转义代码
                .replace(/\[pre\]([\s\S]*)\[\/pre\]\n*/g, function(str){
                    return str.replace(/\[pre\]\n*/g, '<pre>')
                        .replace(/\n*\[\/pre\]\n*/g, '</pre>');
                })

                //转义引用
                .replace(/\[quote\]([\s\S]*)\[\/quote\]\n*/g, function(str){
                    return str.replace(/\[quote\]\n*/g, '<div class="layui-elem-quote">')
                        .replace(/\n*\[\/quote\]\n*/g, '</div>');
                })

                //转义换行
                .replace(/\n/g, '<br>')

            return content;
        },

        //表情列表
        faces: {
            "[微笑]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/5c/huanglianwx_thumb.gif",
            "[嘻嘻]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/0b/tootha_thumb.gif",
            "[哈哈]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/6a/laugh.gif",
            "[可爱]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/14/tza_thumb.gif",
            "[可怜]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/af/kl_thumb.gif",
            "[挖鼻]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/0b/wabi_thumb.gif",
            "[吃惊]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/f4/cj_thumb.gif",
            "[害羞]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/6e/shamea_thumb.gif",
            "[挤眼]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/c3/zy_thumb.gif",
            "[闭嘴]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/29/bz_thumb.gif",
            "[鄙视]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/71/bs2_thumb.gif",
            "[爱你]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/6d/lovea_thumb.gif",
            "[泪]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/9d/sada_thumb.gif",
            "[偷笑]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/19/heia_thumb.gif",
            "[亲亲]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/8f/qq_thumb.gif",
            "[生病]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/b6/sb_thumb.gif",
            "[太开心]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/58/mb_thumb.gif",
            "[白眼]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/d9/landeln_thumb.gif",
            "[右哼哼]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/98/yhh_thumb.gif",
            "[左哼哼]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/6d/zhh_thumb.gif",
            "[嘘]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/a6/x_thumb.gif",
            "[衰]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/af/cry.gif",
            "[委屈]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/73/wq_thumb.gif",
            "[吐]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/9e/t_thumb.gif",
            "[哈欠]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/cc/haqianv2_thumb.gif",
            "[抱抱]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/27/bba_thumb.gif",
            "[怒]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/7c/angrya_thumb.gif",
            "[疑问]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/5c/yw_thumb.gif",
            "[馋嘴]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/a5/cza_thumb.gif",
            "[拜拜]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/70/88_thumb.gif",
            "[思考]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/e9/sk_thumb.gif",
            "[汗]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/24/sweata_thumb.gif",
            "[困]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/40/kunv2_thumb.gif",
            "[睡]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/96/huangliansj_thumb.gif",
            "[钱]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/90/money_thumb.gif",
            "[失望]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/0c/sw_thumb.gif",
            "[酷]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/40/cool_thumb.gif",
            "[色]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/20/huanglianse_thumb.gif",
            "[哼]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/49/hatea_thumb.gif",
            "[鼓掌]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/36/gza_thumb.gif",
            "[晕]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/d9/dizzya_thumb.gif",
            "[悲伤]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/1a/bs_thumb.gif",
            "[抓狂]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/62/crazya_thumb.gif",
            "[黑线]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/91/h_thumb.gif",
            "[阴险]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/6d/yx_thumb.gif",
            "[怒骂]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/60/numav2_thumb.gif",
            "[互粉]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/89/hufen_thumb.gif",
            "[心]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/40/hearta_thumb.gif",
            "[伤心]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/ea/unheart.gif",
            "[猪头]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/58/pig.gif",
            "[熊猫]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/6e/panda_thumb.gif",
            "[兔子]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/81/rabbit_thumb.gif",
            "[ok]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/d6/ok_thumb.gif",
            "[耶]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/d9/ye_thumb.gif",
            "[good]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/d8/good_thumb.gif",
            "[NO]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/ae/buyao_org.gif",
            "[赞]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/d0/z2_thumb.gif",
            "[来]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/40/come_thumb.gif",
            "[弱]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/d8/sad_thumb.gif",
            "[草泥马]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/7a/shenshou_thumb.gif",
            "[神马]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/60/horse2_thumb.gif",
            "[囧]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/15/j_thumb.gif",
            "[浮云]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/bc/fuyun_thumb.gif",
            "[给力]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/1e/geiliv2_thumb.gif",
            "[围观]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/f2/wg_thumb.gif",
            "[威武]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/70/vw_thumb.gif",
            "[奥特曼]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/bc/otm_thumb.gif",
            "[礼物]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/c4/liwu_thumb.gif",
            "[钟]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/d3/clock_thumb.gif",
            "[话筒]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/9f/huatongv2_thumb.gif",
            "[蜡烛]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/d9/lazhuv2_thumb.gif",
            "[蛋糕]": "http://img.t.sinajs.cn/t4/appstyle/expression/ext/normal/3a/cakev2_thumb.gif"
        }
    };

    //创建编辑器
    laytp_doceditor.createEditor= function(options){
        var html = ['<div class="layui-unselect fly-edit">'
            ,'<span type="face" title="表情"><i class="iconfont icon-yxj-expression" style="top: 1px;"></i></span>'
            ,'<span type="picture" title="图片：img[src]"><i class="iconfont icon-tupian"></i></span>'
            ,'<span type="href" title="超链接格式：a(href)[text]"><i class="iconfont icon-lianjie"></i></span>'
            ,'<span type="quote" title="引用"><i class="iconfont icon-yinyong" style="top: 1px;"></i></span>'
            ,'<span type="code" title="插入代码" class="layui-hide-xs"><i class="iconfont icon-emwdaima" style="top: 1px;"></i></span>'
            ,'<span type="hr" title="水平线">hr</span>'
            ,'<span type="title" title="插入标题">T</span>'
            ,'<span type="content" title="插入内容">P</span>'
            ,'<span type="preview" title="预览"><i class="iconfont icon-yulan1"></i></span>'
            ,'</div>'].join('');

        var closeTips = function(){
            layer.close(mod.face.index);
        };

        var log = {}, mod = {
            face: function(editor, self){ //插入表情
                var str = '', ul, face = laytp_doceditor.facade.faces;
                for(var key in face){
                    str += '<li title="'+ key +'"><img src="'+ face[key] +'"></li>';
                }
                str = '<ul id="LAY-editface" class="layui-clear" style="margin: -10px 0 0 -1px;">'+ str +'</ul>';

                layer.close(mod.face.index);
                mod.face.index = layer.tips(str, self, {
                    tips: 3
                    ,time: 0
                    ,skin: 'layui-edit-face'
                    ,tipsMore: true
                });

                $(document).off('click', closeTips).on('click', closeTips);

                $('#LAY-editface li').on('click', function(){
                    var title = $(this).attr('title') + ' ';
                    laytp_doceditor.facade.focusInsert(editor[0], 'face' + title);
                    editor.trigger('keyup');
                });
            }
            ,picture: function(editor){ //插入图片
                layer.open({
                    type: 1
                    ,id: 'fly-jie-upload'
                    ,title: '插入图片'
                    ,area: 'auto'
                    ,shade: false
                    ,area: '465px'
                    ,fixed: false
                    ,offset: [
                        editor.offset().top - $(window).scrollTop() + 'px'
                        ,editor.offset().left + 'px'
                    ]
                    ,skin: 'layui-layer-border'
                    ,content: ['<ul class="layui-form layui-form-pane" style="margin: 20px;">'
                        ,'<li class="layui-form-item">'
                        ,'<label class="layui-form-label">URL</label>'
                        ,'<div class="layui-input-inline">'
                        ,'<input required name="image" placeholder="支持直接粘贴远程图片地址" value="" class="layui-input">'
                        ,'</div>'
                        ,'<button type="button" class="layui-btn layui-btn-primary" id="uploadImg"><i class="layui-icon">&#xe67c;</i>上传图片</button>'
                        ,'</li>'
                        ,'<li class="layui-form-item" style="text-align: center;">'
                        ,'<button type="button" lay-submit lay-filter="uploadImages" class="layui-btn">确认</button>'
                        ,'</li>'
                        ,'</ul>'].join('')
                    ,success: function(layero, index){
                        var image =  layero.find('input[name="image"]');

                        //执行上传实例
                        layui.upload.render({
                            elem: '#uploadImg'
                            ,url: laytp_doceditor.facade.url('/admin/ajax/upload',{'accept':'images'})
                            ,size: 300000000
                            ,done: function(res){
                                if(res.code == 1){
                                    image.val(res.data.data);
                                } else {
                                    layer.msg(res.msg, {icon: 5});
                                }
                            }
                        });

                        layui.form.on('submit(uploadImages)', function(data){
                            var field = data.field;
                            if(!field.image) return image.focus();
                            laytp_doceditor.facade.focusInsert(editor[0], 'img['+ field.image + '] ');
                            layer.close(index);
                            editor.trigger('keyup');
                        });
                    }
                });
            }
            ,href: function(editor){ //超链接
                layer.prompt({
                    title: '请输入合法链接'
                    ,shade: false
                    ,fixed: false
                    ,id: 'LAY_flyedit_href'
                    ,offset: [
                        editor.offset().top - $(window).scrollTop() + 1 + 'px'
                        ,editor.offset().left + 1 + 'px'
                    ]
                }, function(val, index, elem){
                    if(!/^http(s*):\/\/[\S]/.test(val)){
                        layer.tips('请务必 http 或 https 开头', elem, {tips:1})
                        return;
                    }
                    laytp_doceditor.facade.focusInsert(editor[0], ' a('+ val +')['+ val + '] ');
                    layer.close(index);
                    editor.trigger('keyup');
                });
            }
            ,quote: function(editor){ //引用
                layer.prompt({
                    title: '请输入引用内容'
                    ,formType: 2
                    ,maxlength: 10000
                    ,shade: false
                    ,id: 'LAY_flyedit_quote'
                    ,offset: [
                        editor.offset().top - $(window).scrollTop() + 1 + 'px'
                        ,editor.offset().left + 1 + 'px'
                    ]
                    ,area: ['300px', '100px']
                }, function(val, index, elem){
                    laytp_doceditor.facade.focusInsert(editor[0], '[quote]\n  '+ val + '\n[/quote]\n');
                    layer.close(index);
                    editor.trigger('keyup');
                });
            }
            ,code: function(editor){ //插入代码
                layer.prompt({
                    title: '请贴入代码'
                    ,formType: 2
                    ,maxlength: 10000
                    ,shade: false
                    ,id: 'LAY_flyedit_code'
                    ,area: ['800px', '360px']
                }, function(val, index, elem){
                    laytp_doceditor.facade.focusInsert(editor[0], '[pre]\n'+ val + '\n[/pre]\n');
                    layer.close(index);
                    editor.trigger('keyup');
                });
            }
            ,hr: function(editor){ //插入水平分割线
                laytp_doceditor.facade.focusInsert(editor[0], '[hr]\n');
                editor.trigger('keyup');
            }
            ,title: function(editor){
                layer.prompt({
                    title: '请输入标题'
                    ,shade: false
                    ,fixed: false
                    ,id: 'LAY_flyedit_title'
                    ,offset: [
                        editor.offset().top - $(window).scrollTop() + 1 + 'px'
                        ,editor.offset().left + 1 + 'px'
                    ]
                }, function(val, index, elem){
                    laytp_doceditor.facade.focusInsert(editor[0], '[title]\n'+ val + '\n[/title]\n');
                    layer.close(index);
                    editor.trigger('keyup');
                });
            }
            ,content: function(editor){
                laytp_doceditor.facade.focusInsert(editor[0], '[content][/content]');
                editor.trigger('keyup');
            }
            ,preview: function(editor, span){ //预览
                var othis = $(span), getContent = function(){
                    var content = editor.val();
                    return /^\{html\}/.test(content)
                        ? content.replace(/^\{html\}/, '')
                        : laytp_doceditor.facade.content(content)
                }, isMobile = device.ios || device.android;

                if(mod.preview.isOpen) return layer.close(mod.preview.index);

                mod.preview.index = layer.open({
                    type: 1
                    ,title: '预览'
                    ,shade: false
                    ,offset: 'r'
                    ,id: 'LAY_flyedit_preview'
                    ,area: [
                        isMobile ? '100%' : '775px'
                        ,'100%'
                    ]
                    ,scrollbar: isMobile ? false : true
                    ,anim: -1
                    ,isOutAnim: false
                    ,content: '<div class="detail-body layui-text" style="margin:20px;">'+ getContent() +'</div>'
                    ,success: function(layero){
                        editor.on('keyup', function(val){
                            layero.find('.detail-body').html(getContent());
                        });
                        mod.preview.isOpen = true;
                        othis.addClass('layui-this');
                    }
                    ,end: function(){
                        delete mod.preview.isOpen;
                        othis.removeClass('layui-this');
                    }
                });
            }
        };


        $(options.elem).each(function(index){
            var that = this, othis = $(that), parent = othis.parent();
            parent.prepend(html);
            parent.find('.fly-edit span').on('click', function(event){
                var type = $(this).attr('type');
                mod[type].call(that, othis, this);
                if(type === 'face'){
                    event.stopPropagation()
                }
            });
        });
    },

    //输出模块
    exports(MOD_NAME, laytp_doceditor);
});
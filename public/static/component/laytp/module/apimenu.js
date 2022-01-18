layui.define(['table', 'jquery', 'element'], function(exports) {
    "use strict";

    var MOD_NAME = 'apimenu',
        $ = layui.jquery,
        element = layui.element;

    var laytpApiMenu = function(opt) {
        this.option = opt;
    };

    // 供外部调用的，渲染菜单方法
    laytpApiMenu.prototype.render = function(opt) {
        var option = {
            elem: opt.elem,
            async: opt.async,
            parseData: opt.parseData,
            url: opt.url,
            method: opt.method ? opt.method : "GET",
            defaultOpen: opt.defaultOpen,
            defaultSelect: opt.defaultSelect,
            control: opt.control,
            defaultMenu: opt.defaultMenu,
            accordion: opt.accordion,
            height: opt.height,
            theme: opt.theme,
            data: opt.data ? opt.data : [],
            change: opt.change ? opt.change : function() {},
            done: opt.done ? opt.done : function() {}
        };
        if (option.async) {
            if (option.method === "GET") {
                getData(option.url).then(function(data) {
                    option.data = data;
                    renderMenu(option);
                });
            } else {
                postData(option.url).then(function(data) {
                    option.data = data;
                    renderMenu(option);
                });
            }
        } else {
            //renderMenu中需要调用done事件，done事件中需要menu对象，但是此时还未返回menu对象，做个延时提前返回对象
            window.setTimeout(function() { renderMenu(option);}, 500);
        }

        // 处理高度
        layui.$("#"+opt.elem).height(option.height);
        return new laytpApiMenu(opt);
    };

    // 任意左侧菜单绑定点击事件
    laytpApiMenu.prototype.click = function(clickEvent) {
        var _this = this;
        layui.$(document).off("click", "#" + _this.option.elem + " .site-demo-active").on("click", "#" + _this.option.elem + " .site-demo-active", function() {
            // layui.$("body").on("click", "#" + _this.option.elem + " .site-demo-active", function() {
            var dom = layui.$(this);
            var data = {
                menuId: dom.attr("menu-id"),
                docType: dom.attr("doc-type"),
                menuTitle: dom.attr("menu-title"),
            };
            var doms = hash(dom);
            if (doms != null) {
                if (doms.text() != '') {
                    data['menuPath'] = doms.find("span").text() + " / " + data['menuPath'];
                }
            }
            if (doms != null) {
                var domss = hash(doms);
                if(domss!=null){
                    if (domss.text() != '') {
                        data['menuPath'] = domss.find("span").text() + " / " + data['menuPath'];
                    }}
            }
            if (domss != null) {

                var domsss = hash(domss);
                if(domsss!=null){
                    if (domsss.text() != '') {
                        data['menuPath'] = domsss.find("span").text() + " / " + data['menuPath'];
                    }}
            }
            if (layui.$("#" + _this.option.elem).is(".laytp-nav-mini")) {
                if (_this.option.accordion) {
                    activeMenus = layui.$(this).parent().parent().parent().children("a");
                } else {
                    activeMenus.push(layui.$(this).parent().parent().parent().children("a"));
                }
            }
            clickEvent(dom, data);
        })
    };

    function hash(dom) {
        var d = dom.parent().parent().prev();
        if (d.prop("tagName") === "UL") {
            return null;
        }
        return d;
    }

    // 样式选择
    laytpApiMenu.prototype.skin = function(skin) {
        var menu = layui.$(".laytp-nav-tree[lay-filter='" + this.option.elem + "']").parent();
        menu.removeClass("dark-theme");
        menu.removeClass("light-theme");
        menu.addClass(skin);
    };

    // 选择没有子级的菜单节点
    laytpApiMenu.prototype.selectItem = function(laytpId) {
        if (this.option.control != false) {
            layui.$("#" + this.option.elem + " a[menu-id='" + laytpId + "']").parents(".layui-side-scroll ").find("ul").css({
                display: "none"
            });
            layui.$("#" + this.option.elem + " a[menu-id='" + laytpId + "']").parents(".layui-side-scroll ").find(".layui-this").removeClass(
                "layui-this");
            layui.$("#" + this.option.elem + " a[menu-id='" + laytpId + "']").parents("ul").css({
                display: "block"
            });
            var controlId = layui.$("#" + this.option.elem + " a[menu-id='" + laytpId + "']").parents("ul").attr("menu-id");
            if (controlId != undefined) {
                layui.$("#" + this.option.control).find(".layui-this").removeClass("layui-this");
                layui.$("#" + this.option.control).find("[menu-id='" + controlId + "']").addClass("layui-this");
            }
        }
        if (this.option.accordion === true) {
            layui.$("#" + this.option.elem + " a[menu-id='" + laytpId + "']").parents(".laytp-nav-tree").find(".layui-nav-itemed").removeClass(
                "layui-nav-itemed");
        }
        layui.$("#" + this.option.elem + " a[menu-id='" + laytpId + "']").parents(".laytp-nav-tree").find(".layui-this").removeClass(
            "layui-this");
        if (!layui.$("#" + this.option.elem).is(".laytp-nav-mini")) {
            layui.$("#" + this.option.elem + " a[menu-id='" + laytpId + "']").parents(".layui-nav-item").addClass("layui-nav-itemed");
            layui.$("#" + this.option.elem + " a[menu-id='" + laytpId + "']").parents("dd").addClass("layui-nav-itemed");
        }
        layui.$("#" + this.option.elem + " a[menu-id='" + laytpId + "']").parent().addClass("layui-this");
    }

    var activeMenus;
    // 手机模式下，右下角按钮点击展开收缩左侧菜单事件
    laytpApiMenu.prototype.collaspe = function(time) {
        var elem = this.option.elem;
        var config = this.option;
        if (layui.$("#" + this.option.elem).is(".laytp-nav-mini")) {
            $.each(activeMenus, function(i, item) {
                layui.$("#" + elem + " a[menu-id='" + layui.$(this).attr("menu-id") + "']").parent().addClass("layui-nav-itemed");
            });
            layui.$("#" + this.option.elem).removeClass("laytp-nav-mini");
            layui.$("#" + this.option.elem).animate({
                width: "220px"
            }, 150);
            isHoverMenu(false, config);
        } else {
            activeMenus = layui.$("#" + this.option.elem).find(".layui-nav-itemed>a");
            layui.$("#" + this.option.elem).find(".layui-nav-itemed").removeClass("layui-nav-itemed");
            layui.$("#" + this.option.elem).addClass("laytp-nav-mini");
            layui.$("#" + this.option.elem).animate({
                width: "60px"
            }, 400);
            isHoverMenu(true, config);
        }
    };

    function getData(url) {
        var defer = $.Deferred();
        var contact = (url.indexOf('?') > -1) ? "&" : "?";
        $.get(url + contact + "fresh=" + Math.random(), function(result) {
            defer.resolve(result)
        });
        return defer.promise();
    }

    function postData(url) {
        var defer = $.Deferred();
        $.post(url + "?fresh=" + Math.random(), function(result) {
            defer.resolve(result)
        });
        return defer.promise();
    }

    // 内部使用，渲染菜单函数
    function renderMenu(option) {
        if (option.parseData != false) {
            option.data = option.parseData(option.data);
        }
        if (option.data.length > 0) {
            if (option.control != false) {
                createMenuAndControl(option);
            } else {
                createMenu(option);
            }
        }
        element.init();
        downShow(option);
        option.done();
    }

    // 创建菜单
    function createMenu(option) {
        var menuHtml = '<div style="height:100%!important;" class="laytp-side-scroll layui-side-scroll ' + option.theme + '"><ul lay-filter="' + option.elem +
            '" class="layui-nav arrow laytp-menu layui-nav-tree laytp-nav-tree">';
        $.each(option.data, function(i, item) {
            var content = '<li class="layui-nav-item">';
            if (i === option.defaultOpen) {
                content = '<li class="layui-nav-item layui-nav-itemed">';
            }
            var href = "javascript:void(0);";
            var className = "site-demo-active";
            if (item.openType === "_blank" && item.type === 1) {
                className = "";
            }
            if (item.type === 0) {
                // 创 建 目 录 结 构
                content += '<a  href="javascript:;" menu-type="' + item.type + '" doc-type="' + item.docType + '" menu-id="' + item.id + '" href="' + href + '">' +
                    '<span>' + item.title + '</span></a>';
            } else if (item.type === 1) {
                content += '<a class="' + className + '" menu-type="' + item.type + '" doc-type="' + item.docType + '" menu-id="' +
                    item.id +
                    '" menu-title="' + item.title + '"  href="' + href + '"><span>' + item.title + '</span></a>';
            }
            // 调 用 递 归 方 法 加 载 无 限 层 级 的 子 菜 单
            content += loadchild(item);
            // 结 束 一 个 根 菜 单 项
            content += '</li>';
            menuHtml += content;
        });
        // 结 束 菜 单 结 构 的 初 始 化
        menuHtml += "</ul></div>";
        // 将 菜 单 拼 接 到 初 始 化 容 器 中
        layui.$("#" + option.elem).html(menuHtml);
    }

    // 创建多系统菜单, 包括渲染静态html和绑定顶部菜单点击事件
    function createMenuAndControl(option) {
        var control = '<ul class="layui-nav  laytp-nav-control pc layui-hide-xs">';
        var controlPe = '<ul class="layui-nav laytp-nav-control layui-hide-sm">';
        // 声 明 头 部
        var menu = '<div class="layui-side-scroll ' + option.theme + '">';
        // 开 启 同 步 操 作
        var index = 0;
        var controlItemPe = '<dl class="layui-nav-child">';
        var config = layui.apidoc.readConfig();
        $.each(option.data, function(i, item) {
            var menuItem = '';
            var controlItem = '';
            if(index < config.menu['maxTopMenuNum']){
                if (i === option.defaultMenu) {
                    controlItem = '<li menu-title="' + item.title + '" doc-type="' + item.docType + '" menu-id="' + item.id +
                        '" class="layui-this layui-nav-item"><a href="javascript:void(0);">' + item.title + '</a></li>';
                    menuItem = '<ul  menu-id="' + item.id + '" lay-filter="' + option.elem +
                        '" class="layui-nav arrow laytp-menu layui-nav-tree laytp-nav-tree">';
                    // 兼容移动端
                    controlPe += '<li class="layui-nav-item"><a class="pe-title" href="javascript:void(0);" >' + item.title + '</a>';
                    controlItemPe += '<dd menu-title="' + item.title + '" menu-id="' + item.id +
                        '"><a href="javascript:void(0);">' + item.title + '</a></dd>';
                } else {
                    controlItem = '<li menu-title="' + item.title + '" doc-type="' + item.docType + '" menu-id="' + item.id +
                        '" class="layui-nav-item"><a href="javascript:void(0);">' + item.title + '</a></li>';
                    menuItem = '<ul style="display:none" menu-id="' + item.id + '" lay-filter="' + option.elem +
                        '" class="layui-nav arrow layui-nav-tree laytp-nav-tree">';
                    controlItemPe += '<dd menu-title="' + item.title + '" doc-type="' + item.docType + '" menu-id="' + item.id +
                        '"><a href="javascript:void(0);">' + item.title + '</a></dd>';
                }
                index++;
            }else{
                menuItem = '<ul style="display:none" menu-id="' + item.id + '" lay-filter="' + option.elem +
                    '" class="layui-nav arrow layui-nav-tree laytp-nav-tree">';
                controlItemPe += '<dd menu-title="' + item.title + '" doc-type="' + item.docType + '" menu-id="' + item.id +
                    '"><a href="javascript:void(0);">' + item.title + '</a></dd>';
                index++;
            }

            $.each(item.children, function(i, note) {
                // 创 建 每 一 个 菜 单 项
                var content = '<li class="layui-nav-item" >';
                var href = "javascript:void(0);";
                var target = "";
                var className = "site-demo-active";
                if (note.openType == "_blank" && note.type == 1) {
                    href = note.href;
                    target = "target='_blank'";
                    className = "";
                }
                // 判 断 菜 单 类 型 0 是 不可跳转的目录 1 是 可 点 击 跳 转 的 菜 单
                if (note.type == 0) {
                    // 创 建 目 录 结 构
                    content += '<a  href="javascript:void(0);" menu-type="' + item.type + '" doc-type="' + note.docType + '" menu-id="' + note.id +
                        '" ><span>' + note.title +
                        '</span></a>';
                } else if (note.type == 1) {
                    // 创 建 菜 单 结 构
                    content += '<a class="' + className + '" menu-type="' + item.type + '" doc-type="' + note.docType + '" menu-id="' + note.id +
                        '" menu-title="' + note.title + '" href="' + href + '"><span>' + note.title + '</span></a>';
                }
                content += loadchild(note);
                content += '</li>';
                menuItem += content;
            })
            menu += menuItem + '</ul>';
            control += controlItem;
        })

        if(option.data.length > config.menu['maxTopMenuNum']){
            control += '<li class="layui-nav-item">\n' +
                '<a href="javascript:void(0);" class="laytp-icon laytp-icon-elipsis"></a>' +
                '<!-- 功 能 菜 单 -->\n' +
                '<dl class="layui-nav-child">';
            var moreTopMenuIndex = 0;
            $.each(option.data, function(i, item) {
                var moreTopMenuItem = '';
                if(moreTopMenuIndex >= config.menu['maxTopMenuNum']){
                    moreTopMenuItem = '<dd><a menu-title="' + item.title + '" menu-id="' + item.id +'">' + item.title + '</a></dd>';
                }
                moreTopMenuIndex++;
                control += 	moreTopMenuItem;
            });
            control += 	'</dl></li></ul>';
        }

        controlItemPe += "</li></dl></ul>";
        controlPe += controlItemPe;
        layui.$("#" + option.control).html(control);
        layui.$("#" + option.control).append(controlPe);
        layui.$("#" + option.elem).html(menu);
        // 绑定顶部菜单点击事件
        layui.$(document).off("click", "#" + option.control + " .laytp-nav-control [menu-id]").on("click", "#" + option.control + " .laytp-nav-control [menu-id]", function() {
            // layui.$("#" + option.control + " .laytp-nav-control").on("click", "[menu-id]", function() {
            layui.$("#" + option.elem).find(".laytp-nav-tree").css({
                display: 'none'
            });
            layui.$("#" + option.elem).find(".laytp-nav-tree[menu-id='" + layui.$(this).attr("menu-id") + "']").css({
                display: 'block'
            });
            layui.$("#" + option.control).find(".pe-title").html(layui.$(this).attr("menu-title"));
            layui.$("#" + option.control).find("");
            option.change(layui.$(this).attr("menu-id"), layui.$(this).attr("menu-title"), layui.$(this).attr("menu-href"));
            // 自动点击左侧的第一个菜单
            var s = layui.$("a:first",layui.$("#" + option.elem).find("ul[style='display: block;']")).parent();
            if(s.hasClass("layui-nav-itemed")){
                recursionFindA(s);
            }else{
                layui.$("a:first",layui.$("#" + option.elem).find("ul[style='display: block;']")).click();
            }
        });
    }

    // 点击顶部菜单，自动点击左侧菜单，当菜单已经是展开状态时，要递归找到最低级别的A标签进行点击
    function recursionFindA(obj){
        var dlObj = layui.$("dl:first", obj);
        if(dlObj.length === 0){
            layui.$("a:first", obj).click();
        }else{
            recursionFindA(layui.$("dd:first",dlObj));
        }
    }

    /** 加载子菜单 (递归)*/
    function loadchild(obj) {
        // 判 单 是 否 是 菜 单， 如 果 是 菜 单 直 接 返 回
        if (obj.type == 1) {
            return "";
        }
        // 创 建 子 菜 单 结 构
        var content = '<dl class="layui-nav-child">';
        // 如 果 嵌 套 不 等 于 空
        if (obj.children != null && obj.children.length > 0) {
            // 遍 历 子 项 目
            $.each(obj.children, function(i, note) {
                // 创 建 子 项 结 构
                content += '<dd>';
                var href = "javascript:;";
                var target = "";
                var className = "site-demo-active";
                if (note.openType == "_blank" && note.type == 1) {
                    href = note.href;
                    target = "target='_blank'";
                    className = "";
                }
                // 判 断 子 项 类 型
                if (note.type == 0) {
                    // 创 建 目 录 结 构
                    content += '<a href="' + href + '" doc-type="' + note.docType + '" menu-id="' + note.id +
                        '"><i class="' + note.icon + '"></i><span>' + note.title + '</span></a>';
                } else if (note.type == 1) {
                    // 创 建 菜 单 结 构
                    content += '<a class="' + className + '" doc-type="' + note.docType + '" menu-id="' + note.id + '" menu-title="' + note.title + '" href="' + href +
                        '" ><i class="' + note.icon + '"></i><span>' + note.title + '</span></a>';
                }
                // 加 载 子 项 目 录
                content += loadchild(note);
                // 结 束 当 前 子 菜 单
                content += '</dd>';
            });
            // 封 装
        } else {
            content += '<div class="toast"> 无 内 容 </div>';
        }
        content += '</dl>';
        return content;
    }

    // 左侧菜单点击事件
    function downShow(option) {
        layui.$(document).off("click", "#" + option.elem + " a[menu-type='0']").on("click", "#" + option.elem + " a[menu-type='0']", function() {
            // layui.$("body #" + option.elem).on("click", "a[menu-type='0']", function() {
            if (!layui.$("#" + option.elem).is(".laytp-nav-mini")) {
                var superEle = layui.$(this).parent();
                var ele = layui.$(this).next('.layui-nav-child');
                var heights = ele.children("dd").length * 48;

                if (superEle.is(".layui-nav-itemed")) {
                    if (option.accordion) {
                        superEle.parent().find(".layui-nav-itemed").removeClass("layui-nav-itemed");
                        superEle.addClass("layui-nav-itemed");
                        //自动点击第一个子菜单
                        layui.$("a:first",ele).click();
                    }
                    ele.height(0);
                    ele.animate({
                        height: heights + "px"
                    }, 200, function() {
                        ele.css({
                            height: "auto"
                        });
                    });
                } else {
                    superEle.addClass("layui-nav-itemed");
                    ele.animate({
                        height: "0px"
                    }, 200, function() {
                        ele.css({
                            height: "auto"
                        });
                        superEle.removeClass("layui-nav-itemed");
                    });
                }
            }
        })
    }

    /** 二 级 悬 浮 菜 单*/
    function isHoverMenu(b, option) {
        if (b) {
            layui.$("#" + option.elem + ".laytp-nav-mini .layui-nav-item,#" + option.elem + ".laytp-nav-mini dd").hover(function(e) {
                e.stopPropagation();
                var _this = layui.$(this);
                _this.siblings().find(".layui-nav-child")
                    .removeClass("layui-nav-hover").css({
                    left: 0,
                    top: 0
                });
                _this.children(".layui-nav-child").addClass("layui-nav-hover");
                _this.closest('.layui-nav-item').data('time') && clearTimeout(_this.closest('.layui-nav-item').data('time'));
                var height = layui.$(window).height();
                var topLength = _this.offset().top;
                var thisHeight = _this.children(".layui-nav-child").height();
                if ((thisHeight + topLength) > height) {
                    topLength = height - thisHeight - 10;
                }
                var left = _this.offset().left + 60;
                if (!_this.hasClass("layui-nav-item")) {
                    left = _this.offset().left + _this.width();
                }
                _this.children(".layui-nav-child").offset({
                    top: topLength,
                    left: left + 3
                });
            }, function(e) {
                e.stopPropagation();
                var _this = layui.$(this);
                _this.closest('.layui-nav-item').data('time', setTimeout(function() {
                    _this.closest('.layui-nav-item')
                        .find(".layui-nav-child")
                        .removeClass("layui-nav-hover")
                        .css({
                            left: 0,
                            top: 0
                        });
                }, 50));
            })
        } else {
            layui.$("#" + option.elem + " .layui-nav-item").off('mouseenter').unbind('mouseleave');
            layui.$("#" + option.elem + " dd").off('mouseenter').unbind('mouseleave');
        }
    }

    exports(MOD_NAME, new laytpApiMenu());
});

layui.define(["jquery","dropdown"], function (t) {
    "use strict";
    var a = layui.$,
        i = (layui.hint(), layui.device()),
        e = "laytp_element",
        l = "layui-this",
        n = "layui-show",
        s = function () {
            this.config = {}
        };
    s.prototype.set = function (t) {
        var i = this;
        return a.extend(!0, i.config, t), i
    }, s.prototype.on = function (t, a) {
        return layui.onevent.call(this, e, t, a)
    }, s.prototype.tabAdd = function (t, i) {
        var e = ".layui-tab-title",
            l = a(".layui-tab[lay-filter=" + t + "]"),
            n = l.children(e),
            s = n.children(".layui-tab-bar"),
            o = l.children(".layui-tab-content"),
            r = '<li lay-id="' + (i.id || "") + '"' + (i.attr ? ' lay-attr="' + i.attr + '"' : "") + ">" + (i.title || "unnaming") + "</li>";
        return s[0] ? s.before(r) : n.append(r), o.append('<div class="layui-tab-item">' + (i.content || "") + "</div>"), f.hideTabMore(!0), f.tabAuto(), this
    }, s.prototype.tabDelete = function (t, i) {
        var e = ".layui-tab-title",
            l = a(".layui-tab[lay-filter=" + t + "]"),
            n = l.children(e),
            s = n.find('>li[lay-id="' + i + '"]');
        return f.tabDelete(null, s), this
    }, s.prototype.tabChange = function (t, i) {
        var e = ".layui-tab-title",
            l = a(".layui-tab[lay-filter=" + t + "]"),
            n = l.children(e),
            s = n.find('>li[lay-id="' + i + '"]');
        return f.tabClick.call(s[0], null, null, s), this
    }, s.prototype.tab = function (t) {
        t = t || {}, b.on("click", t.headerElem, function (i) {
            var e = a(this).index();
            f.tabClick.call(this, i, e, null, t)
        })
    }, s.prototype.progress = function (t, i) {
        var e = "layui-progress",
            l = a("." + e + "[lay-filter=" + t + "]"),
            n = l.find("." + e + "-bar"),
            s = n.find("." + e + "-text");
        return n.css("width", i), s.text(i), this
    };
    var o = ".layui-nav",
        r = "layui-nav-item",
        c = "layui-nav-bar",
        u = "layui-nav-tree",
        d = "layui-nav-child",
        y = "layui-nav-more",
        h = "layui-anim layui-anim-upbit",
        f = {
            tabClick: function (t, i, s, o) {
                o = o || {};
                var r = s || a(this),
                    i = i || r.parent().children("li").index(r),
                    c = o.headerElem ? r.parent() : r.parents(".layui-tab").eq(0),
                    u = o.bodyElem ? a(o.bodyElem) : c.children(".layui-tab-content").children(".layui-tab-item"),
                    d = r.find("a"),
                    y = c.attr("lay-filter");
                "javascript:;" !== d.attr("href") && "_blank" === d.attr("target") || (r.addClass(l).siblings().removeClass(l), u.eq(i).addClass(n).siblings().removeClass(n)), layui.event.call(this, e, "tab(" + y + ")", {
                    elem: c,
                    index: i
                })
            }, tabDelete: function (t, i) {
                var n = i || a(this).parent(),
                    s = n.index(),
                    o = n.parents(".layui-tab").eq(0),
                    r = o.children(".layui-tab-content").children(".layui-tab-item"),
                    c = o.attr("lay-filter");
                n.hasClass(l) && (n.next()[0] ? f.tabClick.call(n.next()[0], null, s + 1) : n.prev()[0] && f.tabClick.call(n.prev()[0], null, s - 1)), n.remove(), r.eq(s).remove(), setTimeout(function () {
                    f.tabAuto()
                }, 50), layui.event.call(this, e, "tabDelete(" + c + ")", {
                    elem: o,
                    index: s
                })
            }, tabAuto: function () {
                var t = "layui-tab-more",
                    e = "layui-tab-bar",
                    l = "layui-tab-close",
                    n = this;
                a(".layui-tab").each(function () {
                    var s = a(this),
                        o = s.children(".layui-tab-title"),
                        r = (s.children(".layui-tab-content").children(".layui-tab-item"), 'lay-stope="tabmore"'),
                        c = a('<span class="layui-unselect layui-tab-bar" ' + r + "><i " + r + ' class="layui-icon">&#xe61a;</i></span>');
                    if (n === window && 8 != i.ie && f.hideTabMore(!0), s.attr("lay-allowClose") && o.find("li").each(function () {
                        var t = a(this);
                        if (!t.find("." + l)[0]) {
                            var i = a('<i class="layui-icon layui-unselect ' + l + '">&#x1006;</i>');
                            i.on("click", f.tabDelete), t.append(i)
                        }
                    }), "string" != typeof s.attr("lay-unauto"))
                        if (o.prop("scrollWidth") > o.outerWidth() + 1) {
                            if (o.find("." + e)[0]) return;
                            o.append(c), s.attr("overflow", ""), c.on("click", function (a) {
                                o[this.title ? "removeClass" : "addClass"](t), this.title = this.title ? "" : "收缩"
                            })
                        } else o.find("." + e).remove(), s.removeAttr("overflow")
                })
            }, hideTabMore: function (t) {
                var i = a(".layui-tab-title");
                t !== !0 && "tabmore" === a(t.target).attr("lay-stope") || (i.removeClass("layui-tab-more"), i.find(".layui-tab-bar").attr("title", ""))
            }, clickThis: function () {
                //点击菜单事件
                var t = a(this),
                    i = t.parents(o),
                    n = i.attr("lay-filter"),
                    s = t.parent(),
                    c = t.siblings("." + d),
                    y = "string" == typeof s.attr("lay-unselect");
                // "javascript:;" !== t.attr("href") && "_blank" === t.attr("target") || y || c[0] || (i.find("." + l).removeClass(l), s.addClass(l)), i.hasClass(u) && (c.removeClass(h), c[0] && (s["none" === c.css("display") ? "addClass" : "removeClass"](r + "ed"), "all" === i.attr("lay-shrink") && s.siblings().removeClass(r + "ed"))), layui.event.call(this, e, "nav(" + n + ")", t);

                //点击顶级菜单
                if( s.parent().hasClass('main-nav') ) {
                    let more_attr = t.attr('more_first_menus');
                    if(more_attr != "true"){
                        click_menu_redirect(t);
                        let data = menu_json[s.index()].children;
                        select_menu_ids = t.attr('select_menu_ids').split(',');
                        createMenu(data,0,true);
                        layui.laytp_element.init();
                    }
                //点击二级菜单
                }else if(s.parent().parent().attr('id') == 'navBarId'){
                    //点击已经选中的菜单
                    if(s.hasClass('layui-nav-itemed') || s.hasClass('layui-this')){
                        //有子菜单的展开子菜单
                        if (s.find('dl').attr('class') == 'layui-nav-child') {
                            s.removeClass('layui-nav-itemed');
                            s.removeClass('layui-this');
                        } else {
                            // $('#layTpIframe').attr('src',__URL__ + t.attr('rule'));
                            // editHistory(default_menu.name,__URL__ + t.attr('rule') + '?ref=' + t.attr('menu_id'));
                            click_menu_redirect(t);
                        }
                    //点击未选中菜单
                    }else{
                        //1.先取消所有选中状态
                        //有子级菜单的选中项
                        s.parent().find('li').removeClass('layui-nav-itemed');
                        //二级菜单且没有子菜单的选中项
                        s.parent().find('li').removeClass('layui-this');
                        //最末子级菜单选中项
                        s.parent().find('dd').removeClass('layui-this');
                        //2.设置当前点击菜单的选中状态
                        //有子菜单的展开子菜单
                        if (s.find('dl').attr('class') == 'layui-nav-child') {
                            s.addClass('layui-nav-itemed');
                        //没有子菜单的，设为选中状态
                        } else {
                            s.addClass('layui-this');
                            click_menu_redirect(t);
                        }
                    }
                //三级及以下的等级菜单
                }else {
                    if(s.parents().hasClass('layui-side')){
                        //点击已选中菜单
                        if (s.hasClass('layui-nav-itemed') || s.hasClass('layui-this')) {
                            if (s.find('dl').attr('class') == 'layui-nav-child') {
                                s.removeClass('layui-nav-itemed');
                                s.removeClass('layui-this');
                            } else {
                                //选中最低级别的菜单则需要设置iframe的src值
                                click_menu_redirect(t);
                            }
                            //点击未选中菜单
                        } else {
                            let parents = [];
                            layui.each(s.parents(), function (key, item) {
                                if ($(item).hasClass('layui-nav-itemed')) {
                                    parents.push(item);
                                }
                            });
                            $('.layui-nav-tree').find('li').removeClass('layui-nav-itemed');
                            $('.layui-nav-tree').find('li').removeClass('layui-this');
                            $('.layui-nav-tree').find('dd').removeClass('layui-this');
                            layui.each(parents, function (key, item) {
                                $(item).addClass('layui-nav-itemed');
                            });

                            if (s.find('dl').attr('class') == 'layui-nav-child') {
                                s.addClass('layui-nav-itemed');
                            } else {
                                //选中最低级别的菜单则需要设置iframe的src值
                                s.addClass('layui-this');
                                click_menu_redirect(t);
                            }
                        }
                    }
                }
            }, collapse: function () {
                // var t = a(this),
                //     i = t.find(".layui-colla-icon"),
                //     l = t.siblings(".layui-colla-content"),
                //     s = t.parents(".layui-collapse").eq(0),
                //     o = s.attr("lay-filter"),
                //     r = "none" === l.css("display");
                // if ("string" == typeof s.attr("lay-accordion")) {
                //     var c = s.children(".layui-colla-item").children("." + n);
                //     c.siblings(".layui-colla-title").children(".layui-colla-icon").html("&#xe602;"), c.removeClass(n)
                // }
                // l[r ? "addClass" : "removeClass"](n), i.html(r ? "&#xe61a;" : "&#xe602;"), layui.event.call(this, e, "collapse(" + o + ")", {
                //     title: t,
                //     content: l,
                //     show: r
                // })
            }
        };
    s.prototype.init = function (t, e) {
        var l = function () {
                return e ? '[lay-filter="' + e + '"]' : ""
            }(),
            s = {
                tab: function () {
                    f.tabAuto.call({})
                }, nav: function () {
                    var t = 200,
                        e = {},
                        s = {},
                        p = {},
                        b = function (l, o, r) {
                            var c = a(this),
                                f = c.find("." + d);
                            o.hasClass(u) ? l.css({
                                top: c.position().top,
                                height: c.children("a").outerHeight(),
                                opacity: 1
                            }) : (f.addClass(h), l.css({
                                left: c.position().left + parseFloat(c.css("marginLeft")),
                                top: c.position().top + c.height() - l.height()
                            }), e[r] = setTimeout(function () {
                                l.css({
                                    width: c.width(),
                                    opacity: 1
                                })
                            }, i.ie && i.ie < 10 ? 0 : t), clearTimeout(p[r]), "block" === f.css("display") && clearTimeout(s[r]), s[r] = setTimeout(function () {
                                f.addClass(n), c.find("." + y).addClass(y + "d")
                            }, 300))
                        };
                    a(o + l).each(function (i) {
                        var l = a(this),
                            o = a('<span class="' + c + '"></span>'),
                            h = l.find("." + r);
                        l.find("." + c)[0] || (l.append(o), h.on("mouseenter", function () {
                            b.call(this, o, l, i)
                        }).on("mouseleave", function () {
                            l.hasClass(u) || (clearTimeout(s[i]), s[i] = setTimeout(function () {
                                l.find("." + d).removeClass(n), l.find("." + y).removeClass(y + "d")
                            }, 300))
                        }), l.on("mouseleave", function () {
                            clearTimeout(e[i]), p[i] = setTimeout(function () {
                                l.hasClass(u) ? o.css({
                                    height: 0,
                                    top: o.position().top + o.height() / 2,
                                    opacity: 0
                                }) : o.css({
                                    width: 0,
                                    left: o.position().left + o.width() / 2,
                                    opacity: 0
                                })
                            }, t)
                        })), h.find("a").each(function () {
                            var t = a(this),
                                i = (t.parent(), t.siblings("." + d));
                            i[0] && !t.children("." + y)[0] && t.append('<span class="' + y + '"></span>'), t.off("click", f.clickThis).on("click", f.clickThis)
                        })
                    })
                }, breadcrumb: function () {
                    var t = ".layui-breadcrumb";
                    a(t + l).each(function () {
                        var t = a(this),
                            i = "lay-separator",
                            e = t.attr(i) || "/",
                            l = t.find("a");
                        l.next("span[" + i + "]")[0] || (l.each(function (t) {
                            t !== l.length - 1 && a(this).after("<span " + i + ">" + e + "</span>")
                        }), t.css("visibility", "visible"))
                    })
                }, progress: function () {
                    var t = "layui-progress";
                    a("." + t + l).each(function () {
                        var i = a(this),
                            e = i.find(".layui-progress-bar"),
                            l = e.attr("lay-percent");
                        e.css("width", function () {
                            return /^.+\/.+$/.test(l) ? 100 * new Function("return " + l)() + "%" : l
                        }()), i.attr("lay-showPercent") && setTimeout(function () {
                            e.html('<span class="' + t + '-text">' + l + "</span>")
                        }, 350)
                    })
                }, collapse: function () {
                    var t = "layui-collapse";
                    a("." + t + l).each(function () {
                        var t = a(this).find(".layui-colla-item");
                        t.each(function () {
                            var t = a(this),
                                i = t.find(".layui-colla-title"),
                                e = t.find(".layui-colla-content"),
                                l = "none" === e.css("display");
                            i.find(".layui-colla-icon").remove(), i.append('<i class="layui-icon layui-colla-icon">' + (l ? "&#xe602;" : "&#xe61a;") + "</i>"), i.off("click", f.collapse).on("click", f.collapse)
                        })
                    })
                }
            };
        return s[t] ? s[t]() : layui.each(s, function (t, a) {
            a()
        })
    }, s.prototype.render = s.prototype.init;
    var p = new s,
        b = a(document);
    p.render();
    var v = ".layui-tab-title li";
    b.on("click", v, f.tabClick), b.on("click", f.hideTabMore), a(window).on("resize", f.tabAuto), t(e, p)
});
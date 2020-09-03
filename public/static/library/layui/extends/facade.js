/**
 * 常用函数库
 * @version: 2.0
 * @Author:  JunStar
 * @Date:    2020-7-22 20:16:03
 * @Last Modified by:   JunStar
 * @Last Modified time: 2020-08-28 18:15:28
 */
layui.define(["jquery"], function (exports) {
    const MOD_NAME = "facade";
    let $ = layui.$;

    let facade = {
        //根据rule获取静态html的url地址
        getHtmlUrl: function (rule) {
            rule = rule.replace("\.html", "");
            rule = facade.trim(rule, "/");
            rule = rule.replace(".", "/");
            rule = facade.ltrim(rule, "admin/");
            rule = facade.ltrim(rule, "/admin/");
            rule = facade.ltrim(rule, "/a/");
            rule = facade.ltrim(rule, "a/");
            return "/a/" + rule + ".html";
        },

        //根据rule获取后台的url地址
        getAdminUrl: function (rule) {
            rule = rule.replace("\.html", "");
            rule = facade.trim(rule, "/");
            rule = facade.ltrim(rule, "admin/");
            rule = facade.ltrim(rule, "/admin/");
            rule = facade.ltrim(rule, "/a/");
            rule = facade.ltrim(rule, "a/");
            return "/admin/" + rule + ".html";
        },

        /**
         * 设置cookie
         * @param name
         * @param value
         * @param Days 有效天数
         *  - 为0时，有效期与会话Session一致
         *  - 大于0，有效期为天数
         *  - 小于0，删除cookie
         */
        setCookie: function (name, value, Days) {
            var exp = new Date();
            var expires = 0;
            if (Days > 0) {
                exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);
                expires = exp.toGMTString();
            } else if (Days == 0) {
                expires = 0;
            } else {
                exp.setTime(exp.getTime() - 1);
                expires = exp.toGMTString();
            }
            document.cookie = name + "=" + escape(value) + ";expires=" + expires + ";path=/";
        },

        //获取cookie
        getCookie: function (name) {
            var arr = document.cookie.match(new RegExp("(^| )" + name + "=([^;]*)(;|$)"));
            if (arr != null) {
                return (arr[2]);
            } else {
                return "";
            }
        },

        //删除cookie
        delCookie: function (name) {
            var cookieVal = facade.getCookie(name);
            if (cookieVal != null) {
                facade.setCookie(name, "", -1);
            }
        },

        //layer提示操作成功
        success: function (text) {
            layui.notice.success({
                title: "",
                message: text
            });
        },

        /**
         * layer操作失败提示
         * @param text  提示内容
         * @param title 弹窗标题
         * @param callback  点击确定后的匿名回调函数
         */
        error: function (text, title, callback) {
            if (title === undefined) {
                title = "错误提示信息"
            }
            let options = {
                closeBtn: 0
                , btnAlign: 'r'
                , skin: "laytp"
                , title: title
                , yes: function () {
                    if (typeof callback === "function") {
                        callback();
                    }
                    layui.layer.close(alertLayer);
                }
            };
            let alertLayer = layui.layer.alert(text, options);
        },

        //layer提示框
        alert: function (text, title) {
            if (title === undefined) {
                title = "提示信息"
            }
            layui.layer.alert(text, {
                closeBtn: 0
                , title: title
            });
        },

        //layer加载层
        loading: function (type) {
            if (type == null) {
                type = 0;
            }
            return layui.layer.load(type, {shade: 0.1});
        },

        //组装成url
        url: function (path, params) {
            let count = 0;
            let k;
            for (k in params) {
                if (params.hasOwnProperty(k)) {
                    count++;
                }
            }
            path = path.replace("\.html", "");
            if (typeof params !== "undefined" && count > 0) {
                params = $.param(params);
                let reg = new RegExp("=", "g");
                params = params.replace(reg, "/");
                let reg_1 = new RegExp("&", "g");
                params = params.replace(reg_1, "/");
                params = "/" + params;
            } else {
                params = "";
            }
            path = "/" + path.replace(/(^\/)|(\/$)/, "");
            return apiDomain + path + params + ".html";
        },

        //跳转页面
        redirect: function (url) {
            window.location.href = url;
        },

        /**
         * 向后台请求数据
         * @param options
         *  options.path          请求地址
         *  options.params        请求地址参数
         *  options.method        请求方式
         *  options.async         是否异步请求
         *  options.successAlert  请求成功是否弹窗提示
         * @returns {PromiseLike<T | never> | Promise<T | never> | *}
         */
        ajax: function (options) {
            if (options.successAlert == null) options.successAlert = true;
            if (options.async == null) options.async = true;
            if (options.method == null) options.method = "POST";
            if (options.params == null) options.params = {};
            layui.layTp.init.ajaxSet();//如果不加这行，登录后浏览器里面删除cookie，header里面依旧会传递token
            return $.ajax({
                url: facade.url(options.path),
                async: options.async,
                data: options.params,
                // TP6已经完全兼容application/json和application/x-www-form-urlencoded方式传递参数
                // 使用如下方式传递参数，后端使用$this->request->param();依旧能得到想要的参数
                // data : JSON.stringify(params),
                // contentType:"application/json",
                type: options.method,
                dataType: "json",
                timeout: 30000,
                success: function (res) {
                    if (res.code === 10401) {
                        facade.error(res["msg"], "重新登录提示", function () {
                            facade.delCookie("laytp_admin_token");
                            facade.redirect("/a/login.html");
                        });
                        return;
                    }
                    if (res.code === 1) {
                        facade.error(res["msg"], "失败提示");
                        return;
                    }
                    if (options.successAlert && res["code"] === 0) {
                        facade.success(res["msg"]);
                        return;
                    }
                    return res;
                },
                error: function (xhr) {
                    facade.error(xhr["responseJSON"]["message"], "异常提示");
                }
            }).then(function (res) {
                return res;
            });
        },

        /**
         * 去掉字符串左右两端指定字符
         * @param str 需要处理的字符串
         * @param char 字符，为空时表示去除空格
         */
        trim: function (str, char) {
            if (char) {
                return str.replace(new RegExp("^\\" + char + "+|\\" + char + "+$", "g"), "");
            } else {
                return str.replace(/^\s+|\s+$/g, "");
            }
        },

        /**
         * 去除字符串左侧指定字符
         * @param str 需要处理的字符串
         * @param char 字符，为空时表示去除空格
         */
        ltrim: function (str, char) {
            if (char) {
                return str.replace(new RegExp("^\\" + char + "+", "g"), "");
            } else {
                return str.replace(/^\s+|\s+$/g, "");
            }
        },

        /**
         * 去除字符串右侧指定字符
         * @param str 需要处理的字符串
         * @param char 字符，为空时表示去除空格
         */
        rtrim: function (str, char) {
            if (char) {
                return str.replace(new RegExp("\\" + char + "+$", "g"), "");
            } else {
                return str.replace(/^\s+|\s+$/g, "");
            }
        },

        /**
         * 判断元素value是否存在于array数组中
         * @param value
         * @param array
         * @returns {boolean}
         */
        inArray: function (value, array) {
            let index = $.inArray(value, array);
            return index >= 0;
        },

        /**
         * 从当前url中获取参数
         * @param name 参数名称
         * @returns {any}
         */
        getUrlParam: function (name) {
            var sear = window.location.search.substr(1);
            var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
            var r = sear.match(reg);
            return r ? sear.match(reg)[2] : null
        },

        //修改浏览器地址栏地址和标题栏标题
        editHistory: function (url) {
            var stateObject = {};
            var title = "LayTp2.0极速后台开发框架";
            history.replaceState(stateObject, title, url);
        },

        /**
         * 封装弹出层
         * @param options
         * {
         *  title 弹出层标题
         *  url 弹出层内容url，例:/a/auth.menu/add.html
         *  data 需要渲染的数据，用于弹出编辑表单，将ajax得到的静态html作为模板，通过laytpl的模板引擎把数据渲染到界面
         *  btn 弹出层底部按钮，只有两种设置方式，1.不设置，表示弹出一个表单，底部展示['确定','取消']两个按钮，点击确定按钮可以提交表单。2.设置为空字符串，表示弹出一个非表单层，仅展示页面，底部没有按钮，其他复杂的弹出层，比如有三个或者三个以上按钮，请自行编写JS实现
         *  width 弹出层占用屏幕宽度，默认:50%，默认值在/a/index.html中设置
         *  height 弹出层占用屏幕高度，默认:50%，默认值在/a/index.html中设置
         *  shade 弹出层背景阴影，默认:0.1，默认值在/a/index.html中设置
         * }
         * @param callback 匿名回调函数，允许为空
         *  执行匿名回调函数的条件：
         *  - 弹出层为表单层
         *  - 点击确定按钮，提交表单后，后端返回结果code=0
         */
        popupDiv: function (options, callback) {
            let defaultBtnPopupDiv = ['确定', '取消']; //facade.popupDiv()方法弹出层默认按钮
            let isFormDiv = true;//默认为弹出表单层
            if (options.width === undefined) options.width = defaultWidthPopupDiv;
            if (options.height === undefined) options.height = defaultHeightPopupDiv;
            if (options.shade === undefined) options.shade = defaultShadePopupDiv;
            if (options.btn === undefined) {
                options.btn = defaultBtnPopupDiv;
            } else {
                isFormDiv = false;
            }
            let screenWidth = document.body.offsetWidth;//屏幕宽度
            // let screenHeight = document.body.offsetHeight;//屏幕高度
            let layerWidth = 0;
            if (options.width.substring(options.width.length - 1) === "%") {
                layerWidth = facade.rtrim(options.width, "%") / 100 * screenWidth;
            } else if (options.width.substring(options.width.length - 2) === "px") {
                layerWidth = facade.rtrim(options.width, "px");
            }
            let left = (screenWidth - 230 - layerWidth) / 2 + 230;
            // let top = (screenHeight - 50 - facade.rtrim(options.height,"%")/100 * screenHeight)/2 + 50;
            let top = 95;
            $.ajax({
                url: facade.getHtmlUrl(options.url),
                success: function (res) {
                    let content = res;
                    if (options.data !== undefined) {
                        content = layui.laytpl(res).render(options.data);
                    }

                    if (isFormDiv) {
                        let contentArr = content.split("</form>");
                        let filter = (options.data !== undefined) ? "laytp-form-" + options.data.id : "laytp-form";
                        content = contentArr[0] + "<button class='laytp_submit' lay-filter='" + filter + "' lay-submit style='display:none;'>提交</button></form>" + contentArr[1];
                        let onEvent = "submit(" + filter + ")";

                        layui.form.on(onEvent, function (data) {
                            let form_action = $(data.form).attr("action");
                            let form_method = $(data.form).attr("method");
                            let type = (typeof form_method == "undefined") ? "POST" : form_method;
                            let postUrl = (typeof form_action == "undefined") ? facade.getAdminUrl(options.url) : form_action;
                            facade.ajax({
                                path: postUrl,
                                params: data.field,
                                method: type,
                                async: false
                            }).done(function (res) {
                                if (res.code === 0) {
                                    if (typeof funcController !== "undefined" && typeof funcController.tableRender === "function") {
                                        funcController.tableRender();
                                    }
                                    if (typeof funcRecycleController !== "undefined" && typeof funcRecycleController.tableRender === "function") {
                                        funcRecycleController.tableRender();
                                    }
                                    if (typeof callback === "function") {
                                        callback(data.field);
                                    }
                                    layui.layer.close(layerDiv);
                                }
                            });
                            return false;
                        });
                    }
                    var layerDivConfig = {
                        type: 1
                        , title: options.title
                        , anim: 2
                        , shade: options.shade
                        , content: content
                        , area: [options.width, options.height]
                        , offset: [top + "px", left + "px"]
                        , maxmin: true
                        , skin: "laytp"
                        , yes: function () {
                            $(".laytp_submit").click();
                        }
                    };
                    if (options.btn) layerDivConfig.btn = options.btn;
                    var css = $(res).attr("class");
                    if (css === "popup-card-content") {
                        layerDivConfig.skin = "laytp";
                    }
                    var layerDiv = layui.layer.open(layerDivConfig);
                    layui.layTpForm.render(layerDiv);
                },
                error: function (xhr) {
                    switch (xhr.status) {
                        case 404:
                            facade.error("文件 " + facade.getHtmlUrl(options.url) + " 不存在");
                            break;
                        case 500:
                        case 502:
                            facade.error(xhr["responseText"], "异常提示");
                            break;
                    }
                }
            });
        },

        /**
         * 封装弹出确认框
         * @param options
         * @param callback
         * @returns {boolean}
         */
        popupConfirm: function (options, callback) {
            if(options.url === undefined){
                facade.error("请设置需要请求的地址");
                return false;
            }
            if (options.params === undefined) {
                options.params = {};
            }
            var layerConfirm = layui.layer.confirm(options.text, {title: "确认继续", skin: "laytp"}, function () {
                facade.ajax({path: options.url, params: options.params}).done(function (res) {
                    if (res.code === 0) {
                        if (typeof funcController !== "undefined" && typeof funcController.tableRender === "function") {
                            funcController.tableRender();
                        }
                        if (typeof funcRecycleController !== "undefined" && typeof funcRecycleController.tableRender === "function") {
                            funcRecycleController.tableRender();
                        }
                        if (typeof callback === "function") {
                            callback();
                        }
                        layui.layer.close(layerConfirm);
                    }
                });
            });
        },

        /*
         * 封装下拉菜单，使用举例：批量操作
         * @param options array 需要展示的列表,数据格式类似:
         *  [
         *      {
                    action: "edit"//操作名称
                    ,title: "编辑"//文字标题
                    ,icon: "layui-icon-edit"//图标
                    ,node: module + "/" + controller + "/edit"
                    ,param: {}//操作节点需要传入的参数，为空可以不传
                    ,uri: facade.url(module + "/" + controller + "/edit",{id:id})//facade.url(node,param) = uri
                    ,switch_type: "popupDiv"//操作类型
                    ,table_id: "laytp-recycle-table"//数据表格的id，标识下拉菜单获取哪个数据表格的数据，因为现在不使用iframe，所以每个table_id都需要传递
                    ,width:"100%"//宽
                    ,height:"100%"//高
                    ,need_data:true//是否需要数据，默认为true，当为false时，不需要选中数据表的复选框
                    ,callback:"function_name"//回调函数名，当点击下拉菜单的此项时，会调用此函数，函数可以使用window.function_name进行定义，callback有值，则node,uri,switch_type,width,height,need_data都无效，如果函数需要传参，参数使用param赋值
                }
                ,{
                    action: "del",
                    title: "删除"
                    ,icon: "layui-icon-delete"
                    ,node: module + "/" + controller + "/del"//操作节点名称
                    ,param: {}//操作节点需要传入的参数，为空可以不传
                    ,uri: facade.url(module + "/" + controller + "/edit",{id:id})//facade.url(node,param) = uri
                    ,switch_type: "popupConfirm"
                    ,table_id: "laytp-recycle-table"//数据表格的id，标识下拉菜单获取哪个数据表格的数据，因为现在不使用iframe，所以每个table_id都需要传递
                    ,width:"100%"//宽
                    ,height:"100%"//高
                    ,need_data:true//是否需要数据，默认为true，当为false时，不需要选中数据表的复选框
                    ,callback:"function_name"//回调函数名，当点击下拉菜单的此项时，会调用此函数，函数可以使用window.function_name进行定义，callback有值，则node,uri,switch_type,width,height,need_data都无效，如果函数需要传参，参数使用param赋值
                }
         *  ]
         * @param elem string 渲染的document节点名称，举例：.action-more
         * @param checkAuth boolean 是否需要检测权限,true:需要检测当前用户是否有权限，false:不需要检测权限
         */
        dropDown: function (options, checkAuth, elem) {
            if (typeof checkAuth == "undefined" || checkAuth === "" || checkAuth === 0) {
                checkAuth = false;
            }
            if (typeof elem == "undefined" || (elem === "") || (elem === 0)) {
                elem = ".laytp-action-more";
            }

            for (options_k in options) {
                if (!options[options_k].uri) {
                    options[options_k].uri = facade.url(options[options_k].node, options[options_k].param);
                }
            }

            if (!checkAuth) {
                layui.dropdown.render({
                    elem: elem,
                    options: options
                });
            } else {
                var is_super_manager = true;
                if (is_super_manager) {
                    layui.dropdown.render({
                        elem: elem,
                        options: options
                    });
                } else {
                    let hasAuthOptions = [];
                    for (key in options) {
                        if (!options[key].hasOwnProperty("param")) {
                            options[key].param = {};
                        }
                        if (!options[key].hasOwnProperty("callback")) {
                            options[key].callback = "";
                        }
                        for (rk in rule_list) {
                            var node_str = (options[key].node) ? options[key].node : options[key].uri;
                            var uri = (options[key].node) ?
                                facade.url(options[key].node, options[key].param) :
                                options[key].uri;
                            if (node_str.slice(0, 1) == "/") {
                                node_str = node_str.slice(1);
                            }
                            var node_arr = node_str.split("/");
                            var auth_node = "";
                            if (node_arr[0] == "addons") {
                                auth_node = node_arr[0] + "/" + node_arr[1] + "/" + node_arr[2] + "/" + node_arr[3] + "/" + node_arr[4];
                            } else {
                                auth_node = node_arr[0] + "/" + node_arr[1] + "/" + node_arr[2];
                            }
                            var auth_rule = rule_list[rk];
                            if (auth_rule.slice(0, 1) == "/") {
                                auth_rule = auth_rule.slice(1);
                            }
                            if (auth_rule === auth_node) {
                                hasAuthOptions.push(
                                    {
                                        action: options[key].action
                                        , title: options[key].title
                                        , icon: options[key].icon
                                        , uri: uri
                                        , switch_type: options[key].switch_type
                                        , callback: options[key].callback
                                    }
                                );
                                break;
                            }
                        }
                    }
                    layui.dropdown.render({
                        elem: elem,
                        options: hasAuthOptions
                    });
                }
            }
        },

        //获取树插件选中的项
        getTreeCheckedIds: function (jsonObj) {
            var id = "";
            $.each(jsonObj, function (index, item) {
                if (id != "") {
                    id = id + "," + item.id;
                } else {
                    id = item.id;
                }
                var i = facade.getTreeCheckedIds(item.children);
                if (i != "") {
                    id = id + "," + i;
                }
            });
            return id;
        },
    };

    //输出模块
    exports(MOD_NAME, facade);

    //注入layui组件中，供全局调用
    layui.facade = facade;

    //注入window全局对象中，供全局调用
    window.facade = facade;
});
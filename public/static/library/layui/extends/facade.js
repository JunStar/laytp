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
        //判断是否为数组
        isArray: function (obj) {
            return obj instanceof Array;
        },

        //根据rule获取静态html的url地址
        getHtmlUrl: function (url) {
            url = url.replace(/\./g, "/");
            return url + ".html";
        },

        //根据url获取后台的url地址
        getAdminUrl: function (url) {
            return url;
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
            let exp = new Date();
            let expires = 0;
            if (Days === undefined) {
                Days = 0;
            }
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
            let arr = document.cookie.match(new RegExp("(^| )" + name + "=([^;]*)(;|$)"));
            if (arr != null) {
                return (arr[2]);
            } else {
                return "";
            }
        },

        //删除cookie
        delCookie: function (name) {
            let cookieVal = facade.getCookie(name);
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
            // layui.notice.error({title:title, message: text});
            if (title === undefined) {
                title = "错误提示信息"
            }
            let options = {
                closeBtn: 0
                , btnAlign: "r"
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
            if (typeof params !== "undefined" && count > 0) {
                let urlParams = {}, key;
                for (key in params) {
                    if (params[key]) urlParams[key] = params[key];
                }
                params = $.param(urlParams);
                let reg = new RegExp("=", "g");
                params = params.replace(reg, "/");
                let reg_1 = new RegExp("&", "g");
                params = params.replace(reg_1, "/");
                params = "/" + params;
            } else {
                params = "";
            }
            path = "/" + path.replace(/(^\/)|(\/$)/, "");
            return apiDomain + path + params;
        },

        //跳转页面
        redirect: function (url) {
            window.location.href = url;
        },

        /**
         * ajax封装
         * @param options
         *  options.path          请求地址
         *  options.params        请求地址参数
         *  options.method        请求方式,默认为POST
         *  options.async         是否异步请求，默认为true，是异步请求
         *  options.successAlert  请求成功是否弹窗提示
         * @returns {PromiseLike<T | never> | Promise<T | never> | *}
         */
        ajax: function (options) {
            /**
             * 如果不加layTp.init.ajaxSet();的代码，登录后浏览器里面删除cookie，header里面依旧会传递token
             * 没有直接使用layTp.init.ajaxSet();是因为layTp的init对象中有使用到facade.ajax方法，在这里又调用layTp.init会报错
             */
            let loading;
            $.ajaxSetup({
                timeout: 30000,
                headers: {
                    "LayTp-Admin-Token": facade.getCookie("laytp_admin_token"),
                    "Cache-Control": "no-cache"
                },
                beforeSend: function () {
                    loading = facade.loading();
                },
                complete: function () {
                    layui.layer.close(loading);
                }
            });

            if (options.successAlert == null) options.successAlert = true;
            if (options.async == null) options.async = true;
            if (options.method == null) options.method = "POST";
            if (options.params == null) options.params = {};
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
                success: function (res, statusText, xhr) {
                    if (xhr.status === 204) {
                        facade.error("后端接口没有返回结果", "异常提示");
                        return;
                    }
                    if (res.code === 10401) {
                        facade.error(res["msg"], "重新登录提示", function () {
                            facade.delCookie("laytp_admin_token");
                            facade.redirect("/admin/login.html");
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
                error: function (xhr, a, b, c) {
                    if (xhr["responseJSON"] === undefined) {
                        facade.error(xhr["responseText"], "异常提示");
                    } else {
                        facade.error("[Message:]" + xhr["responseJSON"]["message"] + "<br /><br />[File:]" + xhr["responseJSON"]["traces"][0]["file"] + "<br /><br />[Line:]" + xhr["responseJSON"]["traces"][0]["line"], "异常提示");
                    }
                    $("[class='laytp-hidden-submitBtn']").attr("disabled", false);
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
            let sear = window.location.search.substr(1);
            let reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
            let r = sear.match(reg);
            return r ? sear.match(reg)[2] : null
        },

        //修改浏览器地址栏地址和标题栏标题
        editHistory: function (url) {
            let stateObject = {};
            let title = "LayTp2.0极速后台开发框架";
            history.replaceState(stateObject, title, url);
        },

        /**
         * 封装弹出层
         * @param options
         * {
         *  title 必设，弹出层标题
         *  path 必设，请求的路由，例:plugin/core/auth.menu/add，会自动转并请求http://yourDomain/plugin/core/auth.menu/add.html页面作为弹出层内容，并且如果是表单内容，表单的提交地址就是http://yourDomain/plugin/core/auth.menu/add
         *  params 非必设，路由参数
         *  data 非必设，需要渲染的数据，用于弹出编辑表单，将ajax得到的静态html作为模板，通过laytpl的模板引擎把数据渲染到界面
         *  btn 非必设，弹出层底部按钮，只有两种设置方式，1.不设置，表示弹出一个表单，底部展示['确定','取消']两个按钮，点击确定按钮可以提交表单。2.设置为空字符串，表示弹出一个非表单层，仅展示页面，底部没有按钮，其他复杂的弹出层，比如有三个或者三个以上按钮，请自行编写JS实现
         *  width 非必设，弹出层占用屏幕宽度，默认:50%，默认值在/a/index.html中设置
         *  height 非必设，弹出层占用屏幕高度，默认:50%，默认值在/a/index.html中设置
         *  shade 非必设，弹出层背景阴影，默认:0.1，默认值在/a/index.html中设置
         * }
         * @param callback
         * callback 非必设，匿名回调函数，定义了回调函数，数据表格将不会自动刷新，需要自己在回调函数中调用funController.tableRender();刷新
         *      执行匿名回调函数的条件：
         *          - 弹出层为表单层
         *          - 点击确定按钮，提交表单后，后端返回结果code=0
         */
        popupDiv: function (options, callback) {
            if (!options.title) {
                facade.error("facade.popupDiv()没有设置title");
                return false;
            }
            if (!options.path) {
                facade.error("facade.popupDiv()没有设置path");
                return false;
            }
            if (!options.params) {
                options.params = {};
            }
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
            let clientHeight = document.documentElement.clientHeight;//屏幕高度
            let layerWidth = 0;
            let layerHeight = 0;
            if (options.width.substring(options.width.length - 1) === "%") {
                layerWidth = facade.rtrim(options.width, "%") / 100 * screenWidth;
            } else if (options.width.substring(options.width.length - 2) === "px") {
                layerWidth = facade.rtrim(options.width, "px");
            }
            let left = (screenWidth - 230 - layerWidth) / 2 + 230;

            if (options.height.substring(options.height.length - 1) === "%") {
                layerHeight = facade.rtrim(options.height, "%") / 100 * clientHeight;
            } else if (options.height.substring(options.height.length - 2) === "px") {
                layerHeight = facade.rtrim(options.height, "px");
            }
            let top = (clientHeight - layerHeight) / 2;
            $.ajax({
                async: false,//最好设置成同步请求，这样，在执行完popupDiv后，如果需要再对弹出层的元素进行处理，就不会出现BUG
                url: facade.getHtmlUrl(facade.url(options.path)),
                success: function (res) {
                    let content = res;
                    if (options.data !== undefined) {
                        content = layui.laytpl(res).render(options.data);
                    }

                    let filter;
                    if (isFormDiv) {
                        let contentArr = content.split("</form>");
                        filter = (options.data !== undefined) ? "laytp-form-" + options.data.id : "laytp-form";
                        content = contentArr[0] + "<button class='laytp-hidden-submitBtn' lay-filter='" + filter + "' lay-submit style='display:none;'>提交</button></form>" + contentArr[1];
                        let onEvent = "submit(" + filter + ")";

                        layui.form.on(onEvent, function (data) {
                            $("[class='laytp-hidden-submitBtn'][lay-filter='" + filter + "']").attr("disabled", true);
                            let form_action = $(data.form).attr("action");
                            let form_method = $(data.form).attr("method");
                            let type = (typeof form_method == "undefined") ? "POST" : form_method;
                            let postUrl = (typeof form_action == "undefined") ? facade.getAdminUrl(facade.url(options.path, options.params)) : form_action;
                            facade.ajax({
                                path: postUrl,
                                params: data.field,
                                method: type,
                                async: false
                            }).done(function (res) {
                                if (res.code === 0) {
                                    if (typeof callback === "function") {
                                        callback(data.field);
                                    } else {
                                        if (typeof funController !== "undefined" && typeof funController.tableRender === "function") {
                                            funController.tableRender();
                                        }
                                        if (typeof funRecycleController !== "undefined" && typeof funRecycleController.tableRender === "function") {
                                            funRecycleController.tableRender();
                                        }
                                    }
                                    layui.layer.close(layerDiv);
                                }
                                $("[class='laytp-hidden-submitBtn'][lay-filter='" + filter + "']").attr("disabled", false);
                            });
                            return false;
                        });
                    }
                    let layerDivConfig = {
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
                            $("[class='laytp-hidden-submitBtn'][lay-filter='" + filter + "']").click();
                        }
                    };
                    if (options.btn) layerDivConfig.btn = options.btn;
                    let css = $(res).attr("class");
                    if (css === "popup-content") {
                        layerDivConfig.skin = "laytp";
                    }
                    let layerDiv = layui.layer.open(layerDivConfig);
                    layui.layTpForm.render("#layui-layer" + layerDiv);
                },
                error: function (xhr) {
                    switch (xhr.status) {
                        case 404:
                            facade.error("文件 " + facade.getHtmlUrl(facade.url(options.path, options.params)) + " 不存在");
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
         * {
         *  path: 请求的路由
         *  params: 路由参数
         * }
         * @param callback
         * @returns {boolean}
         */
        popupConfirm: function (options, callback) {
            if (options.path === undefined) {
                facade.error("facade.popupConfirm()没有设置options.path");
                return false;
            }
            if (options.params === undefined) {
                options.params = {};
            }
            let layerConfirm = layui.layer.confirm(options.text, {title: "确认继续", skin: "laytp"}, function () {
                facade.ajax({path: options.path, params: options.params}).done(function (res) {
                    if (res.code === 0) {
                        if (typeof callback === "function") {
                            callback();
                        } else {
                            if (typeof funController !== "undefined" && typeof funController.tableRender === "function") {
                                funController.tableRender();
                            }
                            if (typeof funRecycleController !== "undefined" && typeof funRecycleController.tableRender === "function") {
                                funRecycleController.tableRender();
                            }
                        }
                        layui.layer.close(layerConfirm);
                    }
                });
            });
        },

        /**
         * 封装下拉菜单，使用举例：批量操作
         * @param options array 需要展示的列表，数据格式类似：
         *  [
         *      {
                    title: "编辑"//文字标题
                    , icon: "layui-icon-edit"//图标
                    , path: apiPrefix + "edit"
                    , params: "{'id':"+obj.data.id+"}"//操作节点需要传入的参数的json字符串，为空可以不传，格式很重要，否则会报js错误
                    , switchType: "popupDiv"//操作类型
                    , tableId: "laytp-recycle-table"//数据表格的id，标识下拉菜单获取哪个数据表格的数据，因为现在不使用iframe，所以每个table_id都需要传递
                    , width:"100%"//宽
                    , height:"100%"//高
                    , needData:true//是否需要数据，默认为true，当为false时，不需要选中数据表的复选框
                    , data: obj.data//当switchType=popupDiv时，用此data渲染到弹出层
                    , callback:"function_name"//回调函数名，当点击下拉菜单的此项时，会调用此函数，函数可以使用window.function_name进行定义，callback有值，则path,uri,switchType,width,height,need_data都无效，如果函数需要传参，参数使用param赋值
                }
         ,{
                    title: "删除"
                    , icon: "layui-icon-delete"
                    , path: apiPrefix + "del"//操作节点名称
                    , params: "{'id':"+obj.data.id+"}"//操作节点需要传入的参数的json字符串，为空可以不传，格式很重要，否则会报js错误
                    , switchType: "popupConfirm"
                    , tableId: "laytp-recycle-table"//数据表格的id，标识下拉菜单获取哪个数据表格的数据，因为现在不使用iframe，所以每个table_id都需要传递
                    , width:"100%"//宽
                    , height:"100%"//高
                    , needData:true//是否需要数据，默认为true，当为false时，不需要选中数据表的复选框
                    , callback:"function_name"//回调函数名，当点击下拉菜单的此项时，会调用此函数，函数可以使用window.function_name进行定义，callback有值，则path,uri,switchType,width,height,need_data都无效，如果函数需要传参，参数使用param赋值
                }
         *  ]
         * @param elem string 渲染的document节点名称，举例：.laytp-action-more
         * @param checkAuth boolean 是否需要检测权限，true：需要检测当前用户是否有权限，false:不需要检测权限
         */
        dropDown: function (options, checkAuth, elem) {
            if (typeof checkAuth === "undefined" || checkAuth === "" || checkAuth === 0) {
                checkAuth = false;
            }
            if (typeof elem === "undefined" || (elem === "") || (elem === 0)) {
                elem = ".laytp-action-more";
            }

            let opk;
            for (opk in options) {
                options[opk].url = facade.url(options[opk].path, options[opk].params);
            }

            if (!checkAuth) {
                layui.dropdown.render({
                    elem: elem,
                    options: options
                });
            } else {
                if (user.is_super_manager === 1) {
                    layui.dropdown.render({
                        elem: elem,
                        options: options
                    });
                } else {
                    let hasAuthOptions = [], key;
                    for (key in options) {
                        if (facade.hasAuth(options[key].path)) {
                            hasAuthOptions.push(options[key]);
                        }
                    }
                    if (hasAuthOptions.length > 0) {
                        layui.dropdown.render({
                            elem: elem,
                            options: hasAuthOptions
                        });
                    } else {
                        console.log("批量操作中的下拉菜单没有权限");
                    }
                }
            }
        },

        //获取树插件选中的项
        getTreeCheckedIds: function (jsonObj) {
            let id = "";
            $.each(jsonObj, function (index, item) {
                if (id != "") {
                    id = id + "," + item.id;
                } else {
                    id = item.id;
                }
                let i = facade.getTreeCheckedIds(item.children);
                if (i != "") {
                    id = id + "," + i;
                }
            });
            return id;
        },

        /**
         * 判断当前登录者，是否拥有某个节点的权限
         * @param path 完整的路由地址，例：apiPrefix + "add"
         *             这里一开始设计的是，只需要传递action，
         *             但是为了能自定义开发，能在页面上展示一个跨应用的按钮且进行权限控制，这里需要传递完整的路由地址
         * @returns {boolean}
         */
        hasAuth: function (path) {
            if (user.is_super_manager === 1) {
                return true;
            }
            path = facade.trim(path, "/");
            if (facade.inArray(path, authList)) {
                return true;
            } else {
                return false;
            }
        },
    };

    //输出模块
    exports(MOD_NAME, facade);

    //注入layui组件中，供全局调用
    layui.facade = facade;

    //注入window全局对象中，供全局调用
    window.facade = facade;
});
/**
 * 常用函数库
 * @version: 2.0
 * @Author:  JunStar
 * @Date:    2020-7-22 20:16:03
 * @Last Modified by:   JunStar
 * @Last Modified time: 2020-08-28 18:15:28
 */
layui.define([
    "context",
    "notice",
    "popup",
],function (exports) {
    const MOD_NAME = "facade";
    let $ = layui.$;

    let facade = {
        /**
         * 当前是否为与移动端
         * */
        isMobile: function(){
            if ($(window).width() <= 768) {
                return true;
            }
            return false;
        },

        //获取是否为静态界面独立域名部署模式
        isAdminDomainModel: function(){
            return (localStorage.getItem('adminDomainModel') === 'true');
        },

        /**
         * 兼容Html页面path
         *  兼容单域名和多域名部署
         * @param htmlPath
         * @returns {string}
         */
        compatibleHtmlPath: function(htmlPath){
            if(facade.isAdminDomainModel()){
                if(htmlPath.substring(0,7) === '/admin/'){
                    htmlPath = '/' + htmlPath.substring(7);
                }
            }else{
                if(htmlPath.substring(0,7) !== '/admin/'){
                    htmlPath = '/admin/' + facade.ltrim(htmlPath, '/');
                }
            }
            return htmlPath;
        },

        /**
         * 兼容Api地址
         *  兼容单域名和多域名部署
         * @param apiPath
         * @returns {string}
         */
        compatibleApiRoute: function(apiRoute){
            if(facade.getAdminApiDomain()){
                if(apiRoute.substring(0,7) === '/admin.' && apiRoute.substring(0,8) !== '/plugin/'){
                    apiRoute = '/' + apiRoute.substring(7);
                }
            }else{
                if(apiRoute.substring(0,7) !== '/admin.' && apiRoute.substring(0,8) !== '/plugin/'){
                    apiRoute = '/admin.' + facade.ltrim(apiRoute, '/');
                }
            }
            return apiRoute;
        },

        /**
         * 根据路由获取Api请求地址
         *  兼容单域名和多域名部署
         * @param apiRoute
         * @returns {string}
         */
        getApiUrlByRoute: function(apiRoute){
            return facade.getAdminApiDomain() + facade.compatibleApiRoute(apiRoute);
        },

        //获取静态资源访问域名
        getStaticDomain: function(){
            return localStorage.getItem('staticDomain');
        },

        //获取后台接口访问域名
        getAdminApiDomain: function(){
            return localStorage.getItem('adminApiDomain');
        },

        //获取文件后缀名
        getFileExt: function(filename){
            return filename.substring(filename.lastIndexOf('.')+1);
        },

        //判断是否为数组
        isArray: function (obj) {
            return obj instanceof Array;
        },

        //下划线转驼峰
        underlineToCamel: function(str, firstUpper){
            let ret = str.toLowerCase();
            ret = ret.replace( /_([\w+])/g, function( all, letter ) {
                return letter.toUpperCase();
            });
            if(firstUpper){
                ret = ret.replace(/\b(\w)(\w*)/g, function($0, $1, $2) {
                    return $1.toUpperCase() + $2;
                });
            }
            return ret;
        },

        //驼峰转下划线
        camelToUnderline: function(str, upper){
            const ret = str.replace(/([A-Z])/g,"_$1");
            if(upper){
                return ret.toUpperCase();
            }else{
                return ret.toLowerCase();
            }
        },

        //获取表单中编辑器的内容设置到表单元素中
        setEditorField: function(data){
            let obj = $(".editor");
            if(obj.length>0){
                layui.each(obj, function (key, item) {
                    let id = $(item).data('id');
                    let type = $(item).data('type');
                    try{
                        data.field[id] = $(item)[0].contentWindow.getEditorContent();//这一句不能省略，否则第一次提交表单时会获取不到值
                    }catch (e) {
                        throw new Error('ueditor编辑器未初始化完成，请勿提交表单');
                    }
                    // meditor编辑器有两个内容，
                    // 一个是Markdown语法的内容，一个是html标记的内容，
                    // Markdown语法的内容会存入字段，如果需要存储html的内容，自行在控制器中加入代码存储
                    if(type === "meditor"){
                        try{
                            data.field[id+"_html"] = $(item)[0].contentWindow.getHtmlContent();//这一句不能省略，否则第一次提交表单时会获取不到值
                        }catch (e) {
                            throw new Error('meditor编辑器未初始化完成，请勿提交表单');
                        }
                    }
                });
            }
            return data;
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

        //清空所有缓存数据
        clearCache: function(){
            localStorage.clear();
            sessionStorage.clear();
            var tokenKey = layui.context.get("tokenCookieKey") ? layui.context.get("tokenCookieKey") : "laytpAdminToken";
            facade.delCookie(tokenKey);
        },

        //layer提示操作成功
        success: function (text) {
            layui.notice.success(text);
        },

        /**
         * layer操作失败提示
         * @param text  提示内容
         * @param title 弹窗标题
         * @param callback  点击确定后的匿名回调函数
         */
        error: function (text, title, callback) {
            layui.notice.error(text);
            // if (title === undefined) {
            //     title = "错误提示信息"
            // }
            // let options = {
            //     closeBtn: 0
            //     , btnAlign: "r"
            //     , skin: "laytp"
            //     , title: title
            //     , yes: function () {
            //         if (typeof callback === "function") {
            //             callback();
            //         }
            //         layui.layer.close(alertLayer);
            //     }
            // };
            // let alertLayer = layui.layer.alert(text, options);
        },

        //layer提示框
        alert: function (text, title, callback) {
            if (title === undefined) {
                title = "提示信息"
            }
            layui.layer.alert(text, {
                closeBtn: 0
                , title: title
            }, callback);
        },

        //layer加载层
        loading: function (type) {
            if (type == null) {
                type = 0;
            }
            return layui.layer.load(type, {shade: 0.1});
        },

        //关闭layer加载层
        closeLoading: function (loading) {
            return layui.layer.close(loading);
        },

        /**
         * 根据Api请求的路由和需要传递的参数组合成url
         *  兼容单域名部署和多域名部署
         * @param route string
         * @param params object
         * @returns {string}
         */
        url: function (route, params) {
            let count = 0;
            let k;
            for (k in params) {
                if (params.hasOwnProperty(k)) {
                    count++;
                }
            }
            if (typeof params !== "undefined" && count > 0) {
                params = $.param(params);
            } else {
                params = "";
            }
            route = "/" + route.replace(/(^\/)|(\/$)/, "");
            route = facade.getApiUrlByRoute(route);
            return params ? route + "?" + params : route;
        },

        //组装成插件url
        pluginUrl: function (plugin, path, params) {
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
            return "/plugin/" + plugin + path + params;
        },

        //跳转页面，传入path，兼容单域名部署模式和多域名部署模式
        redirect: function (path) {
            top.location.href = facade.compatibleHtmlPath(path);
        },

        //跳转指定url页面
        redirectUrl: function (url) {
            top.location.href = url;
        },

        /**
         * ajax封装
         * @param options
         *  options.route         route和url二选一，优先使用route，请求接口的路由，函数内部会自行兼容单域名和多域名部署模式
         *  options.url           route和url二选一，请求接口的完整url地址，如果仅设置了url参数，那么url的值是什么，ajax请求的地址就是什么
         *  options.data          请求参数,调用时允许设置成对象，封装函数会判断为对象时，自动转换成json字符串
         *  options.type          请求方式,默认为POST
         *  options.async         是否异步请求，默认为true，是异步请求
         *  options.contentType   参数传递的格式，默认application/json
         *  options.dataType      解析数据返回的格式，默认json
         *  options.successAlert  请求成功是否弹窗提示，默认true
         *  options.errorAlert    请求失败是否弹窗提示，默认true
         *  options.showLoading   是否显示加载层，默认false，不显示加载层
         *  options.headers       ajax请求时，需要传递的headers，默认值使用ajaxHeaders全局变量进行定义
         * @returns {PromiseLike<T | never> | Promise<T | never> | *}
         */
        ajax: function (options) {
            /**
             * 如果不加laytp.init.ajaxSet();的代码，登录后浏览器里面删除cookie，header里面依旧会传递token
             * 没有直接使用laytp.init.ajaxSet();是因为laytp的init对象中有使用到facade.ajax方法，在这里又调用laytp.init会报错
             */
            if(!options.url && !options.route){
                return facade.error("facade.ajax()未定义options.url和options.route");
            }
            if(options.route){
                options.url = facade.getAdminApiDomain() + facade.compatibleApiRoute(options.route);
            }
            if (options.showLoading == null) options.showLoading = false;
            let loading;
            if (options.showLoading) {
                loading = facade.loading();
            }
            if (options.successAlert == null) options.successAlert = true;
            if (options.errorAlert == null) options.errorAlert = true;
            if (options.async == null) options.async = true;
            if (options.type == null) options.type = "POST";
            // TP6已经完全兼容application/json和application/x-www-form-urlencoded方式传递参数
            // 两种方式传递参数，后端使用$this->request->param();都能得到想要的参数
            // 只是为了html的name="sign[]"这种方式方便传递数组类型的参数，所以此处默认使用application/x-www-form-urlencoded方式
            if (options.contentType == null) options.contentType = "application/x-www-form-urlencoded";
            if (options.dataType == null) options.dataType = "json";
            if (options.data == null) options.data = {};
            if (typeof options.data === "object" && options.contentType === "application/json") options.data = JSON.stringify(options.data);
            if (options.headers == null) options.headers = layui.context.get("ajaxHeaders");

            // var defer = $.Deferred();
            return $.ajax({
                timeout: 30000,
                headers: options.headers,
                complete: function () {
                    if (options.showLoading) {
                        facade.closeLoading(loading);
                    }
                },
                // ajax请求无需cookie，此处禁止携带cookie进行请求
                xhrFields : {
                    withCredentials : false
                },
                url : options.url,
                async: options.async,
                data : options.data,
                contentType: options.contentType,//此参数是指定ajax请求的参数格式为json字符串
                type: options.type,
                dataType: "json",//此参数是指定将ajax请求返回的字符串结果转换为json对象
                success: function(res, statusText, xhr){
                    if (xhr.status === 204) {
                        facade.error("后端接口没有返回结果", "异常提示");
                    }
                    if (res.code === 10401) {
                        layui.popup.failure(res["msg"],function(){
                            facade.redirect("/admin/login.html");
                        });
                    }
                    if (options.errorAlert && res.code === 1) {
                        facade.error(res["msg"]);
                    }
                    if (options.successAlert && res["code"] === 0) {
                        facade.success(res["msg"]);
                    }
                    if( res.code > 10000 ){
                        facade.error(res["msg"]);
                    }
                    // defer.resolve(res);
                },
                error: function (xhr) {
                    if (xhr["responseJSON"] === undefined) {
                        facade.error(xhr["responseText"], "异常提示");
                    } else {
                        facade.error("[Message:]" + xhr["responseJSON"]["message"] + "<br /><br />[File:]" + xhr["responseJSON"]["traces"][0]["file"] + "<br /><br />[Line:]" + xhr["responseJSON"]["traces"][0]["line"], "异常提示");
                    }
                    // defer.fail();
                }
            });
            // return defer;

            // return $.ajax({
            //     timeout: 30000,
            //     headers: options.headers,
            //     complete: function () {
            //         if (options.showLoading) {
            //             facade.closeLoading(loading);
            //         }
            //     },
            //     url: options.url,
            //     async: options.async,
            //     // data: options.data,
            //     // TP6已经完全兼容application/json和application/x-www-form-urlencoded方式传递参数
            //     // 使用如下方式传递参数，后端使用$this->request->param();依旧能得到想要的参数
            //     data : options.data,
            //     contentType: options.contentType,//此参数是指定ajax请求的参数格式为json字符串
            //     type: options.type,
            //     dataType: "json",//此参数是指定将ajax请求返回的字符串结果转换为json对象
            //     success: function (res, statusText, xhr) {
            //         if (xhr.status === 204) {
            //             facade.error("后端接口没有返回结果", "异常提示");
            //             return;
            //         }
            //         if (res.code === 10401) {
            //             layui.popup.failure(res["msg"],function(){
            //                 facade.clearCache();
            //                 facade.redirect("/admin/login.html");
            //             });
            //             return;
            //         }
            //         if (options.errorAlert && res.code === 1) {
            //             facade.error(res["msg"]);
            //             return;
            //         }
            //         if (options.successAlert && res["code"] === 0) {
            //             facade.success(res["msg"]);
            //             return;
            //         }
            //         return res;
            //     },
            //     error: function (xhr) {
            //         if (xhr["responseJSON"] === undefined) {
            //             facade.error(xhr["responseText"], "异常提示");
            //         } else {
            //             facade.error("[Message:]" + xhr["responseJSON"]["message"] + "<br /><br />[File:]" + xhr["responseJSON"]["traces"][0]["file"] + "<br /><br />[Line:]" + xhr["responseJSON"]["traces"][0]["line"], "异常提示");
            //         }
            //         $("[class='laytp-hidden-submitBtn']").attr("disabled", false);
            //     }
            // }).then(function (res) {
            //     return res;
            // });
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
            let key;
            for (key in array) {
                if (array[key] == value) {
                    return true;
                }
            }
            return false;
            // let index = $.inArray(value, array);
            // return index >= 0;
        },

        /**
         * 从当前url中获取参数
         * @param name 参数名称
         * @returns {string}
         */
        getUrlParam: function (name) {
            let sear = window.location.search.substr(1);
            let reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
            let r = sear.match(reg);
            return r ? sear.match(reg)[2] : "";
        },

        /**
         * 转义html
         * @param s
         * @returns {string}
         */
        encodeHtml: function (s) {
            return (typeof s != "string") ? s :
                s.replace(/"|&|'|<|>|[\x00-\x20]|[\x7F-\xFF]|[\u0100-\u2700]/g,
                    function ($0) {
                        var c = $0.charCodeAt(0), r = ["&#"];
                        c = (c == 0x20) ? 0xA0 : c;
                        r.push(c);
                        r.push(";");
                        return r.join("");
                    });
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
         *  title  必设，弹出层标题
         *  path   url和path二选一，优先使用path，请求的静态文件path，例:plugin/core/auth.menu/add.html，函数内部会自动兼容单域名和多域名部署模式
         *  url    url和path二选一，优先使用path，弹窗内容的完整url，例:http://www.baidu.com/index.html
         *  shade  非必设，阴影，默认0.0001
         *  width  非必设，宽度，单位支持px和%，默认，手机模式下100%，PC模式下90%
         *  height 高度，单位支持px和%，默认，手机模式下100%，PC模式下90%
         *  closeBtn layer提供了两种风格的关闭按钮，可通过配置1和2来展示，如果不显示，则closeBtn: 1
         *  type   非必设，弹出层类型，1=页面层，2=iframe层，默认2
         * }
         */
        popupDiv: function (options) {
            let loading = facade.loading();
            if (!options.title) {
                facade.error("facade.popupDiv()没有设置title");
                facade.closeLoading(loading);
                return false;
            }
            if (!options.url && !options.path) {
                facade.error("facade.popupDiv()的url和path参数都没有设置");
                facade.closeLoading(loading);
                return false;
            }
            if(!options.width){
                options.width = facade.isMobile()?'100%':'90%';
            }
            if(!options.height){
                options.height = facade.isMobile()?'100%':'90%';
            }
            if (options.shade === undefined) options.shade = 0.0001;
            if(options.path){
                if(options.path.indexOf("?") !== -1){
                    options.url = facade.compatibleHtmlPath(options.path) + "&f=" + Math.random();
                }else{
                    options.url = facade.compatibleHtmlPath(options.path) + "?f=" + Math.random();
                }
            }
            if(options.closeBtn == null){
                options.closeBtn = 1;
            }
            if(options.type == null){
                options.type = 2;
            }
            let layerDivConfig = {
                type: options.type
                , title: options.title
                , anim: 2
                , shade: options.shade
                , content: options.url
                , area: [options.width, options.height]
                , maxmin: true
                , skin: "laytp"
                , closeBtn : options.closeBtn
            };
            facade.closeLoading(loading);
            return layui.layer.open(layerDivConfig);
        },

        /**
         * 封装弹出确认框
         * @param options
         * {
         *  text: 弹窗确认框的文本
         *  route route和url二选一，优先使用route，请求接口的路由，函数内部会自行兼容单域名和多域名部署模式
         *  url   route和url二选一，请求接口的完整url地址，如果仅设置了url参数，那么url的值是什么，ajax请求的地址就是什么
         *  data: 路由参数
         * }
         * @param callback
         * @returns {boolean}
         */
        popupConfirm: function (options, callback) {
            if (!options.url && !options.route) {
                facade.error("facade.popupConfirm()没有设置options.url和options.route");
                return false;
            }
            if (options.params === undefined) {
                options.params = {};
            }
            let layerConfirm = layui.layer.confirm(options.text, {title: "确认继续", skin: "laytp"}, function () {
                facade.ajax({route: options.route, url: options.url, data: options.data}).done(function (res) {
                    if (res.code === 0) {
                        if (typeof callback === "function") {
                            callback(res);
                        } else {
                            if (typeof funController !== "undefined" && typeof funController.tableRender === "function") {
                                funController.tableRender();
                            }
                            if (typeof funRecycleController !== "undefined" && typeof funRecycleController.tableRender === "function") {
                                funRecycleController.tableRender();
                            }
                        }
                    }
                });
                layui.layer.close(layerConfirm);
            });
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
         * 判断当前后台管理员，是否拥有某个节点的权限
         * @param path 完整的路由地址，例：apiPrefix + "add"
         *             这里一开始设计的是，只需要传递action，
         *             但是为了能自定义开发，能在页面上展示一个跨应用的按钮且进行权限控制，这里需要传递完整的路由地址
         * @returns {boolean}
         */
        hasAuth: function (path) {
            var authList = layui.context.get('authList');
            var ruleList = [], key;
            for(key in authList){
                if(authList[key]['rule']){
                    ruleList.push(authList[key]['rule']);
                }
            }
            if(facade.isAdminDomainModel()){
                if(path.substring(0,7) === '/admin.'){
                    path = '/' + path.substring(7);
                }
            }
            if (facade.inArray(path, ruleList)) {
                return true;
            } else {
                return false;
            }
        },

        /**
         * 格式化数据表格的数据
         * @param res
         * @param isPage
         * @returns {{msg: *, code: *, data: *, count: PaymentItem | number | p.fileLength}}
         */
        parseTableData: function (res, isPage) {
            if(!res){
                facade.error('接口返回结果为空');
                return false;
            }
            if(res.code === 10401){
                layui.popup.failure(res["msg"],function(){
                    facade.redirect("/admin/login.html");
                });
            }
            if(isPage !== false){
                return {
                    "code": res.code, //解析接口状态
                    "msg": res.msg, //解析提示文本
                    "count": res.data.total, //解析数据长度
                    "data": res.data.data //解析数据列表
                };
            }else{
                return {
                    "code": res.code, //解析接口状态
                    "msg": res.msg, //解析提示文本
                    "count": res.data.length, //解析数据长度
                    "data": res.data //解析数据列表
                };
            }
        },

        /**
         * 删除数组指定元素
         * @param arr
         * @param delVal
         */
        arrayRemoveItem: function(arr, delVal) {
            if (arr instanceof Array) {
                var index = arr.indexOf(delVal);
                if (index > -1) {
                    arr.splice(index, 1);
                }
            }
        },

        // 获取对象属性，如果对象不存在此属性，就返回空字符串
        getObjAttr: function(object, attr){
            if(object.hasOwnProperty(attr)){
                return object[attr];
            }else{
                return '';
            }
        }
    };

    //输出模块
    exports(MOD_NAME, facade);

    //注入layui组件中，供全局调用
    layui.facade = facade;

    //注入window全局对象中，供全局调用
    window.facade = facade;
});
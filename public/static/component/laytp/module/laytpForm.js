/**
 * 表单元素渲染插件
 *  表单元素    附加选项
 *  下拉框     单选 - 无限极分类
 * @version: 1.0
 * @Author:  JunStar
 * @Date:    2020-08-17 18:13:50
 * @Last Modified by:   JunStar
 * @Last Modified time: 2020-08-28 18:16:07
 */
layui.define([
    'xmSelect',
    'laytpUpload',
    'laytpIcon',
    'laydate',
    'colorpicker'
], function (exports) {
    const MOD_NAME = "laytpForm";
    let $ = layui.$
        , facade = layui.facade
    ;

    window.xmSelectObj = {};

    let laytpForm = {};

    //延迟对象数组
    let defArr = [];

    /**
     * 初始化所有表单元素的数据，比如下拉框的待选择项和选定项
     */
    laytpForm.initData = {
        /**
         * 默认下拉框
         * <select
         *  name="name"//提交表单时的name
         *  id="id"//节点的id值
         *  data-source="/plugin/core/auth.menu/getSelectOptionData" //数据源，插件自动拼接参数all_data=1
         *  data-valueField="id" //option中value属性使用列表的哪个字段，默认为id
         *  data-showField="name" //option中，文本显示使用列表中哪个字段，默认为name
         *  data-selected="1" //选中项的valueField的值，如果data-valueField属性的值为id，那么当前属性data-selected的值就是数据源中某一个选项的id值，用于添加表单表单元素的默认选中值
         *  data-placeholder="请选择" //类似placeholder，允许为空，没有默认值，当没有定义当前属性，或者当前属性的值定义为空时，将不会有提示行，默认会选中数据源中的第一个数据
         * >
         * </select>
         * @param parentElem 父节点
         */
        select: function (parentElem) {
            let obj = (typeof parentElem === "undefined") ? $("select") : $("select", parentElem);
            layui.each(obj, function (key, item) {
                let source = $(item).data("source");
                $(item).removeAttr("data-source");
                if (source) {
                    let selected = $(item).data("selected");
                    let valueField = $(item).data("valuefield") ? $(item).data("valuefield") : 'id';
                    let showField = $(item).data("showfield") ? $(item).data("showfield") : 'name';
                    let placeholder = $(item).data("placeholder");
                    //将ajax的延迟对象，存入延迟对象数组defArr中
                    defArr.push(
                        facade.ajax({
                            method: "GET",
                            route: source,
                            data: {"all_data": 1},
                            successAlert: false
                        }).then(function (res) {
                            if (placeholder) {
                                $(item).append("<option value=\"\">" + placeholder + "</option>");
                            }
                            for (key in res.data) {
                                let optionValue = (valueField !== "k") ? res.data[key][valueField] : key;
                                let optionText = (showField !== "v") ? res.data[key][showField] : res.data[key];
                                if (selected === optionValue) {
                                    $(item).append("<option value='" + optionValue + "' selected='selected'>" + optionText + "</option>");
                                } else {
                                    $(item).append("<option value='" + optionValue + "'>" + optionText + "</option>");
                                }
                            }
                        })
                    );
                }
            });
        },

        /**
         * xmSelect下拉选择组件
         *  - 获取父级窗口xmSelect组件已经选中的值，console.log(parent.xmSelectObj[options.name].getValue());
         *  - 获取当前窗口xmSelect组件已经选中的值，console.log(xmSelectObj[options.name].getValue());
         * <div class="xmSelect"//class="xmSelect"是xmSelect组件渲染的标识
         *      data-name="name"//必设，提交表单时的name
         *      data-sourceType="route"//非必设，默认为data，标注数据类型，data=直接解析数据；route=ajax请求后台api接口数据；
         *      //必设，data-source支持两种数据结构数组和对象，url类型也可以返回这两种数据结构
         *      data-source="[{name:'唱歌',value:'1'},{name:'跳舞',value:'2'},{name:'朗诵',value:'3'},{name:'武术',value:'4'}]"//这种，对应的data-sourceType="data"，不需要指定data-textField和data-valueField，默认使用name的值作为展示的文本，value的值作为表单提交的值
         *      data-source="[{text:'唱歌',id:'1'},{text:'跳舞',id:'2'},{text:'朗诵',id:'3'},{text:'武术',id:'4'}]"//这种，对应的data-sourceType="data"，需要指定data-textField="text"，data-valueField="id"
         *      data-source="{1:唱歌,2:跳舞,3:朗诵,4:武术}"//这种，对应的data-sourceType="data"，key就是表单提交的值，value就是展示的文本
         *      data-source="plugin/core/auth.menu/getTreeList" //这种，对应的data-sourceType="route"，这里是一个Api接口地址，返回的数据结构是数组或者对象，具体请参考当data-sourceType="data"时，data-source的说明
         *      data-params='{"is_tree":1,"all_data":1}'//请求后台接口地址时，需要传递的参数，json字符串，这个在自动生成时，不会用到
         *      data-sourceTree="true"//非必设，是否展示成树形结构，与data-source连用，当为true时，需要展示data-source的值是树形结构的数据
         *      data-strict="false"//树形结构是否严格父子级，无需设置，值都是false
         *      data-treeType="tree"//非必设，树形结构展示方式，默认tree，可选项tree、tree-group和cascader，cascader为级联模式
         *      data-textField="name"//非必设，默认为name，当数据源的数据结构是一个数组时，显示的文本字段名称，当数据源数据结构是一个对象时，此设置无意义
         *      data-subTextField="value"//非必设，附属的文本字段名称，仅在下拉框列表中展示，如果返回的数据结果中有对象，比如后台是使用with关联得到的数据，支持使用.号取对象的数据
         *      data-valueField="value"//非必设，默认为value，当数据源的数据结构是一个数组时，提交表单的值，当数据源数据结构是一个对象时，此设置无意义
         *      data-iconField="icon"//非必设，当数据源的数据结构是一个数组时，显示文本前展示的图标字段名，为空或者不设置，则显示文本前无图标，当数据源数据结构是一个对象时，此设置无意义
         *      data-radio="true"//非必设，单选模式，true=开启单选模式，false=关闭单选模式，默认false
         *      data-selected="1,2"//非必设，默认选中的数据，需要在data中存在，默认不选中任何项
         *      data-max="2"//非必设，多选模式下，最多可选个数，默认不限制
         *      data-placeholder="请选择****"//非必设，类似placeholder，未选中数据时，提示文字，默认为"请选择"
         *      data-layVerify="required"//非必设，与layui的lay-verify相同
         *      data-layVerType="tips"//非必设，与layui的lay-verType相同
         *      data-direction="up"//非必设，下拉方向，增加这个参数是因为xmSelect没有做成漂浮，在弹出窗最底层有xmSelect时，会影响美观度
         *      data-paging="true"//非必设，是否分页，目前仅在data-sourceType="route"时有效，且后台返回的数据结构是使用thinkphp的paginate方法返回的数据结构
         *      data-onchange="xmcb"//非必设，监听选中变化的函数名称，类似select元素的onchage事件，这里需要输入的是函数名称字符串，函数的定义方式和参数详解如下：
         *          window.xmcb = function(params){
         *              console.log(params);
         *              //params是一个对象，有arr、change、isAdd三个属性
         *              //arr是当前选中项组成的数组，是完成change或者说是click事件后留下的选中项
         *              //change是点击下拉框项组成的数组，因为xmSelect支持下拉框分组，树形结构列表，能点击一个按钮选中/取消选中多个项目，所以也是一个数组
         *              //isAdd表示change里面的内容是选中，还是取消选中操作
         *          }
         *          //params输出举例
         *          arr: Array(1)
         0: {name: "单行文本输入框", value: "input", __node: {…}}
         length: 1
         __proto__: Array(0)
         change: Array(1)
         0: {name: "文本域", value: "textarea", __node: {…}}
         length: 1
         __proto__: Array(0)
         isAdd: false
         __proto__: Object
         * ></div>
         * @param parentElem
         */
        xmSelect: function (parentElem) {
            let obj = (typeof parentElem === "undefined") ? $(".xmSelect") : $(".xmSelect", parentElem);
            layui.each(obj, function (key, item) {
                let name = $(item).data("name");
                if (!name) facade.error("xmSelect组件未定义name属性");
                let sourceType = $(item).data("sourcetype") ? $(item).data("sourcetype") : "data";
                let source = $(item).data("source");
                if (!source) {
                    facade.error('xmSelect插件未定义source');
                    return false;
                }
                source = source.toString();
                let textField = $(item).data("textfield");
                let subTextField = $(item).data("subtextfield");
                let radio = $(item).data("radio") === true;
                let strict = (typeof $(item).data("strict") !== "undefined") ? $(item).data("strict") : !radio;
                //这里是第一次渲染
                let options = {
                    el: item
                    , name: name
                    , language: 'zn'
                    , data: []
                    , filterable: true
                    , filterMethod: function (val, item, index, prop) {
                        if (val) {
                            //主标题中包含的大小写都搜索出来
                            if (item[textField].toLowerCase().indexOf(val.toLowerCase()) !== -1) {
                                return true;
                                //副标题中包含的大小写都搜索出来
                            } else if (subTextField && item[subTextField].toLowerCase().indexOf(val.toLowerCase()) !== -1) {
                                return true;
                            }
                            return false;//其他的就不要了
                        } else {
                            return true;
                        }
                    }
                    , radio: radio
                    , searchTips: "输入关键字进行搜索"
                    , maxMethod: function (selectedData) {
                        facade.error("最多可选" + selectedData.length + "个数据");
                    }
                    , layVerify: $(item).data("layverify") ? $(item).data("layverify") : ""
                    , layVerType: $(item).data("layvertype") ? $(item).data("layvertype") : ""
                    , clickClose: $(item).data("radio") === true
                    , tips: $(item).data("placeholder") ? $(item).data("placeholder") : "请选择"
                    , toolbar: {
                        show: !($(item).data("radio") === true),
                        list: ['ALL', 'REVERSE', 'CLEAR']
                    }
                    , theme: {
                        color: localStorage.getItem("theme-color-context")
                    }
                };
                let max = $(item).data("max");
                if (max) options.max = max;
                options.direction = $(item).data("direction") ? $(item).data("direction") : "auto";
                options.textField = $(item).data("textfield") ? $(item).data("textfield") : "name";
                options.subTextField = $(item).data("subtextfield") ? $(item).data("subtextfield") : "";
                options.valueField = $(item).data("valuefield") ? $(item).data("valuefield") : "value";
                options.iconField = $(item).data("iconfield") ? $(item).data("iconfield") : "";
                options.sourceTree = $(item).data("sourcetree") === true;
                options.treeType = ($(item).data("treetype") === 'cascader') ? 'cascader' : 'tree';
                options.onchange = $(item).data("onchange") ? $(item).data("onchange") : "";
                if (options.onchange) {
                    options.on = function (params) {
                        if (typeof window[options.onchange] === "function") {
                            window[options.onchange].call(this, params);
                        } else {
                            facade.error(options.onchange + "回调函数未定义");
                        }
                    };
                }
                //model是设置选中后，input框的模板
                options.model = {
                    label: {
                        block: {
                            template: function (item) {
                                let template = item[options.textField];
                                template = options.iconField ? '<i class="' + item[options.iconField] + ' margin-right5"></i>' + template : template;
                                return template;
                            },
                        },
                    }
                };
                //这里是设置选中后，input框中的每一项，删除图标是否显示
                options.model.label.block.showIcon = !($(item).data("radio") === true);
                //这里是设置下拉框前面的单选按钮或者复选框是否显示
                options.model.icon = ($(item).data("radio") === true && !options.sourceTree) ? "hidden" : "show";
                //template是设置下拉框的模板
                options.template = function (item) {
                    let template = item.item[options.textField];
                    template = options.iconField ? '<i class="' + item.item[options.iconField] + ' margin-right5"></i>' + template : template;
                    if(options.subTextField.indexOf('.') !== -1){
                        var subTextFieldArr = options.subTextField.split('.');
                        if(item.item[subTextFieldArr[0]]){
                            template = template + '<span style="position: absolute; right: 10px; color: #8799a3">' + item.item[subTextFieldArr[0]][subTextFieldArr[1]] + '</span>';
                        }
                    }else{
                        template = options.subTextField ? template + '<span style="position: absolute; right: 10px; color: #8799a3">' + item.item[options.subTextField] + '</span>' : template;
                    }
                    return template;
                };
                options.prop = {
                    name: options.textField
                    , value: options.valueField
                };
                if (options.sourceTree) {
                    if(options.treeType === 'tree'){
                        options.tree = {
                            //是否显示树状结构
                            show: true,
                            //是否展示三角图标
                            showFolderIcon: true,
                            //是否显示虚线
                            showLine: true,
                            //间距
                            indent: 20,
                            //默认展开节点的数组, 为 true 时, 展开所有节点
                            expandedKeys: true,
                            //是否严格遵守父子模式
                            strict: strict,
                            //是否开启极简模式
                            simple: true,
                            clickExpand: false,
                            clickCheck: true,
                        };
                    }else if(options.treeType === 'tree-group'){
                        options.tree = {
                            //是否显示树状结构
                            show: false,
                            //是否展示三角图标
                            showFolderIcon: true,
                            //是否显示虚线
                            showLine: true,
                            //间距
                            indent: 20,
                            //默认展开节点的数组, 为 true 时, 展开所有节点
                            expandedKeys: true,
                            //是否严格遵守父子模式
                            strict: strict,
                            //是否开启极简模式
                            simple: true,
                            clickExpand: false,
                            clickCheck: true,
                        };
                    }else{
                        options.cascader = {
                            show: true,
                            indent: 200,
                            //是否严格遵守父子模式
                            strict: strict,
                        };
                    }
                }
                options.autoRow = true; // 如果选中的项目过多，会自动变动输入框的高度
                if (sourceType === "route") {
                    //这个ajax请求无需存入延迟对象数组，因为xmSelect是一个一个进行渲染的，不像layui.form.render()方法，xmSelect没有全局统一方法一次性渲染所有的xmSelect
                    //xmSelect的分页效果由js实现，不管分页与否，都查询出所有数据集，参数传递all_data即为查询出所有数据集
                    let params = $(item).data("params");
                    let ajaxParams = {};
                    if(options.sourceTree){
                        ajaxParams.is_tree = 1;
                    }else{
                        ajaxParams.all_data = 1;
                    }
                    if (params) {
                        params = eval(params);
                        for (key in params) {
                            ajaxParams[key] = params[key];
                        }
                    }
                    facade.ajax({
                        method: "GET",
                        route: source,
                        data: ajaxParams,
                        successAlert: false
                    }).done(function (res) {
                        let paging = $(item).data("paging") === true;
                        if (paging) {
                            options.paging = true;
                            options.pageSize = 10;
                        }
                        options.data = res.data;
                        window.xmSelectObj[options.name] = xmSelect.render(options);
                        let selected = $(item).data("selected");
                        if (selected && selected !== "undefined") {
                            selected = selected.toString();
                            window.xmSelectObj[options.name].setValue(selected.split(','));
                        }
                    });

                    // let params = $(item).data("params");
                    // let ajaxParams = {};
                    // if (params) {
                    //     params = eval(params);
                    //     for (key in params) {
                    //         ajaxParams[key] = params[key];
                    //     }
                    // }
                    // let paging = $(item).data("paging") === true;
                    // if (paging) {
                    //     options.remoteSearch = true;
                    //     options.paging = true;
                    //     options.pageRemote = true;
                    //     options.remoteMethod = function(val, cb, show, pageIndex){
                    //         ajaxParams['page'] = pageIndex;
                    //         if(typeof ajaxParams['search_param'] === "undefined"){
                    //             ajaxParams['search_param'] = {};
                    //         }
                    //         ajaxParams['search_param'][options.textField] = {
                    //             "value" : val,
                    //             "condition" : "LIKE"
                    //         };
                    //         facade.ajax({
                    //             method: "GET",
                    //             route: source,
                    //             data: ajaxParams,
                    //             successAlert: false
                    //         }).done(function (res) {
                    //             for(k in options.initValue){
                    //                 for(key in res.data.data){
                    //                     if(res.data.data[key][options.valueField].toString() === options.initValue[k].toString()){
                    //                         res.data.data[key].selected = true;
                    //                     }else{
                    //                         res.data.data[key].selected = false;
                    //                     }
                    //                 }
                    //             }
                    //             // 使用selected及时设置回调需要默认选中的项
                    //             options.initValue = [];
                    //             cb(res.data.data, res.data.last_page);
                    //         }).fail(function(){
                    //             cb([], 0);
                    //         });
                    //     };
                    //     xmSelect.render(options);
                    // }else{
                    //     let params = $(item).data("params");
                    //     let ajaxParams = {};
                    //     if (params) {
                    //         params = eval(params);
                    //         for (key in params) {
                    //             ajaxParams[key] = params[key];
                    //         }
                    //     }
                    //     ajaxParams['all_data'] = 1;
                    //     facade.ajax({
                    //         method: "GET",
                    //         route: source,
                    //         data: ajaxParams,
                    //         successAlert: false
                    //     }).done(function (res) {
                    //         options.data = res.data;
                    //         xmSelect.render(options);
                    //     });
                    // }
                } else if (sourceType === "data") {
                    options.data = eval(source);
                    window.xmSelectObj[options.name] = xmSelect.render(options);
                    let selected = $(item).data("selected");
                    if (selected && selected !== "undefined") {
                        selected = selected.toString();
                        window.xmSelectObj[options.name].setValue(selected.split(','));
                    }
                }
            });
        },

        /**
         * 联动下拉框，linkage的下拉框会渲染成普通select下拉框组件
         * <select class="linkageSelect"
         *         lay-search="true"
         *         lay-filter="id"
         *         id="id"
         *         name="id"
         *         data-leftField=""//左关联字段，为空表示联动下拉框的第一个下拉框
         *         data-rightField=""//右关联字段，为空表示联动下拉框的最后一个下拉框
         *         data-url=""//下拉框数据源url地址，对应一个数据表的index
         *         data-params='{"field":{"value":"fieldVla","condition":"="}}'
         *         data-showField="short_name"
         *         data-searchField="pid"//数据源搜索字段
         *         data-searchCondition="="//数据源搜索条件，默认为=
         *         data-searchVal="0"//数据源搜索值
         *         data-selectedVal=""//已选中的值，编辑页面
         *         data-onchange="lscb"//非必设，监听选中变化的函数名称，类似select元素的onchage事件，这里需要输入的是函数名称字符串，函数的定义方式和参数详解如下：
         *         window.lscb = function(data){
         *              console.log(data);
         *              //data值与layui.form.on('select(' + id + ')', function (data) {});监听中的data值一样
         *         }
         * ></select>
         */
        linkageSelect: function (parentElem) {
            let obj = (typeof parentElem === "undefined") ? $(".linkageSelect") : $(".linkageSelect", parentElem);
            layui.each(obj, function (key, item) {
                let id = $(item).attr('id');//id属性，必须
                if (!id) {
                    facade.error('linkageSelect组件未定义id属性');
                }
                let showField = $(item).data('showfield') ? $(item).data('showfield') : 'name';//显示的字段，默认是name
                let url = $(item).data('url');//下拉框数据源url地址，对应一个数据表的index
                let params = $(item).data('params');
                let searchField = $(item).data('searchfield') ? $(item).data('searchfield') : 'pid';//搜索的字段，默认是pid
                let searchCondition = $(item).data('searchcondition') ? $(item).data('searchcondition') : '=';
                let searchVal = $(item).data('searchval') ? $(item).data('searchval') : 0;//搜索字段的值，默认0,如果只想选某个省下面的城市和地区可以设置这个值
                let selectedVal = $(item).data('selectedval');//选中的值，非必填
                let leftField = $(item).data('leftfield');//左关联字段，为空或者不设置时，表示第一个下拉框
                let rightField = $(item).data('rightfield');//右关联字段，为空或者不设置时，表示最后一个下拉框
                let onchange = $(item).data("onchange") ? $(item).data("onchange") : "";//onchange回调函数名
                if(!leftField && !rightField){
                    facade.error("linkageSelect组件的data-leftField和data-rightField属性不能都为空");
                }
                //填充联动的第一个下拉框数据
                if (leftField === "" || leftField === undefined) {
                    let searchParam;
                    if(params){
                        searchParam = params;
                    }else{
                        searchParam = {};
                    }
                    searchParam[searchField] = {};
                    searchParam[searchField].value = searchVal;
                    searchParam[searchField].condition = searchCondition;
                    facade.ajax({
                        method: "GET",
                        route: url,
                        data: {search_param: searchParam, all_data: 1},
                        successAlert: false
                    }).done(function (res) {
                        let optionTips = $(item).children().first().prop("outerHTML");
                        $(item).empty();
                        $(item).append(optionTips);
                        let optionHtml, key;
                        for (key in res.data) {
                            if (selectedVal === res.data[key]['id']) {
                                optionHtml = '<option value="' + res.data[key]['id'] + '" selected="selected">' + res.data[key][showField] + '</option>';
                            } else {
                                optionHtml = '<option value="' + res.data[key]['id'] + '">' + res.data[key][showField] + '</option>';
                            }

                            $(item).append(optionHtml);
                        }
                        layui.form.render('select');
                        // 如果第一个下拉框有onchange，就执行onchange
                        if(onchange){
                            var nowData = {};
                            nowData.elem = $("#"+id);
                            nowData.othis = {};
                            nowData.value = selectedVal;
                            window[onchange].call(this, nowData);
                        }
                    });
                }
                //监听所有下拉框onchange事件
                if (rightField) {
                    layui.form.on('select(' + id + ')', function (data) {
                        if (data.value) {
                            let rightSearchUrl = $("#" + rightField, parentElem).data('url');
                            let rightParams = $("#" + rightField, parentElem).data('params');
                            let rightSearchField = $("#" + rightField, parentElem).data('searchfield') ? $("#" + rightField, parentElem).data('searchfield') : 'pid';//搜索的字段，默认是pid
                            let rightSearchParam;
                            if(rightParams){
                                rightSearchParam = rightParams;
                            }else{
                                rightSearchParam = {}
                            }
                            rightSearchParam[rightSearchField] = {};
                            rightSearchParam[rightSearchField].value = data.value;
                            rightSearchParam[rightSearchField].condition = $("#" + rightField, parentElem).data('searchcondition') ? $("#" + rightField, parentElem).data('searchcondition') : "=";
                            facade.ajax({
                                method: "GET",
                                route: rightSearchUrl,
                                data: {search_param: rightSearchParam, all_data: 1},
                                successAlert: false
                            }).done(function (res) {
                                let rightOptionTips = $("#" + rightField, parentElem).children().first().prop("outerHTML");
                                $("#" + rightField, parentElem).empty();
                                $("#" + rightField, parentElem).append(rightOptionTips);
                                let rightOptionHtml, key;
                                for (key in res.data) {
                                    rightOptionHtml = '<option value="' + res.data[key]['id'] + '">' + res.data[key]['name'] + '</option>';
                                    $("#" + rightField, parentElem).append(rightOptionHtml);
                                }

                                let nextRightField = $("#" + rightField, parentElem).data('rightfield');
                                let nextOptionTips = "";
                                while (nextRightField !== "" && nextRightField !== undefined) {
                                    nextOptionTips = $("#" + nextRightField, parentElem).children().first().prop("outerHTML");
                                    $("#" + nextRightField, parentElem).empty();
                                    $("#" + nextRightField, parentElem).append(nextOptionTips);
                                    nextRightField = $("#" + nextRightField, parentElem).data('rightfield');
                                }
                                layui.form.render('select');

                                // 如果有onchange 就执行onchage
                                if(onchange){
                                    var nowData = {};
                                    nowData.elem = $("#"+id);
                                    nowData.othis = {};
                                    nowData.value = selectedVal;
                                    window[onchange].call(this, nowData);
                                }
                            });
                        } else {
                            let rightOptionTips = $("#" + rightField, parentElem).children().first().prop("outerHTML");
                            $("#" + rightField, parentElem).empty();
                            $("#" + rightField, parentElem).append(rightOptionTips);
                            let nextRightField = $("#" + rightField, parentElem).data('rightfield');
                            let nextOptionTips = "";
                            while (nextRightField !== "" && nextRightField !== undefined) {
                                nextOptionTips = $("#" + nextRightField, parentElem).children().first().prop("outerHTML");
                                $("#" + nextRightField, parentElem).empty();
                                $("#" + nextRightField, parentElem).append(nextOptionTips);
                                nextRightField = $("#" + nextRightField, parentElem).data('rightfield');
                            }
                            layui.form.render('select');

                            // 如果有onchange 就执行onchange
                            if(onchange){
                                var nowData = {};
                                nowData.elem = $("#"+id);
                                nowData.othis = {};
                                nowData.value = selectedVal;
                                window[onchange].call(this, nowData);
                            }
                        }
                    });
                }else{
                    // 如果有onchange 就执行onchage
                    if(onchange){
                        layui.form.on('select(' + id + ')', function (data) {
                            var nowData = {};
                            nowData.elem = $("#"+id);
                            nowData.othis = {};
                            nowData.value = selectedVal;
                            window[onchange].call(this, nowData);
                        });
                    }
                }
                //如果有选中值，就请求渲染右侧下拉框
                if (selectedVal !== "" && selectedVal !== undefined) {
                    if (rightField !== "" && rightField !== undefined) {
                        let selectedRightSearchUrl = $("#" + rightField, parentElem).data('url');
                        let selectedRightParams = $("#" + rightField, parentElem).data('params');
                        let selectedRightSearchField = $("#" + rightField, parentElem).data('searchfield') ? $("#" + rightField, parentElem).data('searchfield') : 'pid';//搜索的字段，默认是pid
                        let selectedRightSearchParam;
                        if(selectedRightParams){
                            selectedRightSearchParam = selectedRightParams;
                        }else{
                            selectedRightSearchParam = {};
                        }
                        selectedRightSearchParam[selectedRightSearchField] = {};
                        selectedRightSearchParam[selectedRightSearchField].value = selectedVal;
                        selectedRightSearchParam[selectedRightSearchField].condition = $("#" + selectedRightSearchField, parentElem).data('searchcondition') ? $("#" + selectedRightSearchField).data('searchcondition') : "=";
                        facade.ajax({
                            method: "GET",
                            route: selectedRightSearchUrl,
                            data: {search_param: selectedRightSearchParam, all_data: 1},
                            successAlert: false
                        }).done(function (res) {
                            let selectedRightOptionTips = $("#" + rightField, parentElem).children().first().prop("outerHTML");
                            $("#" + rightField, parentElem).empty();
                            $("#" + rightField, parentElem).append(selectedRightOptionTips);
                            let rightSelectedVal = $("#" + rightField, parentElem).data('selectedval');
                            let selectedOptionHtml, key;
                            for (key in res.data) {
                                if (rightSelectedVal === res.data[key]['id']) {
                                    selectedOptionHtml = '<option value="' + res.data[key]['id'] + '" selected="selected">' + res.data[key][showField] + '</option>';
                                } else {
                                    selectedOptionHtml = '<option value="' + res.data[key]['id'] + '">' + res.data[key][showField] + '</option>';
                                }
                                $("#" + rightField, parentElem).append(selectedOptionHtml);
                            }
                            layui.form.render('select');

                            // 如果右侧下拉框有选中值和onchange，就执行右侧下拉框的onchange
                            var rightOnchange = $("#" + rightField, parentElem).data('onchange');
                            if (rightSelectedVal && rightOnchange) {
                                var rightData = {};
                                rightData.elem = $("#"+rightField);
                                rightData.othis = {};
                                rightData.value = rightSelectedVal;
                                window[rightOnchange].call(this, rightData);
                            }
                        });
                    }
                }
            });
        },

        /**
         * 上传组件
         * <div class="laytpUpload"//class="laytpUpload"是上传组件渲染的标识
         *      data-name="name"//必设，提交表单时的name
         *      data-type="local"//非必设，上传方式，目前允许使用的上传方式有
         *          - local=本地上传;
         *          - ali-oss=阿里云OSS上传;
         *          - qiniu-kodo=七牛云KODO上传
         *      data-viaServer="via/unVia"//非必设，是否经过服务器端，默认via，经过服务器，此参数的有效性与data-type的值相关
         *          - 当data-type=local时，此参数无效
         *          - 当data-type=ali-oss时，且此参数为via时，是由客户端先把文件上传到服务器上，然后再由服务器上传到阿里云OSS
         *          - 当data-type=ali-oss时，且此参数为unVia时，是由客户端先请求服务器端得到阿里云的STS临时上传凭证，然后由客户端把文件直接上传到阿里云oss
         *          - 当data-type=qiniu-kodo时，情况与data-type=ali-oss时相似
         *      data-showUploadBtn="true"//非必设，是否显示上传按钮，默认true
         *      data-showChoiceBtn="true"//非必设，是否显示选择附件按钮，默认true
         *      data-fileCategoryId="integer"//非必设，所属分类的ID，默认0
         *      data-accept="image"//非必设，允许上传的类型，image=图片，video=视频，audio=音频，file=任意文件，默认为image
         *      data-width="500"//非必设，accept="image"时，允许上传图片的最大宽度，单位px，默认不限制
         *      data-height="300"//非必设，accept="image"时，允许上传图片的最大高度，单位px，默认不限制
         *      data-multi="true"//非必设，多文件模式，true=开启多文件模式，false=关闭多文件模式，默认false
         *      data-max="2"//非必设，多文件模式下有效的参数，设置允许最多上传的文件个数，默认不限制个数
         *      data-dir=""//非必设，上传的目录，允许不传，不传就传到storage目录下，如果不为空，则在storage目录下创建对应的目录，允许使用/指明多级目录
         // *      data-url=""//非必设，文件上传请求的后台地址，默认为facade.url("/admin.common/upload")，自定义的url返回数据格式要和/admin.common/upload保持一致
         *      data-uploaded=""//非必设，已经上传的文件路径，多个以英文半角的逗号+空格进行分割，用于编辑页面展示已经上传的文件
         *      data-uploadedId=""//非必设，已经上传的文件列表的ID值，多个以英文半角的逗号进行分割，用于编辑页面展示已经上传的文件Id
         *      data-uploadedFilename=""//非必设，已经上传的文件列表的数据库存储的文件名，多个以英文半角逗号+空格进行分割，用于编辑页面展示已经上传的文件名，目前仅上传文件时需要展示
         *      data-uploadedCallback="callbackFun"//非必设，传入字符串，上传成功后的回调函数名称
         *          - 比如定义的值为callbackFun，然后在全局定义如下函数即可执行回调
         *              function callbackFun(file){
         *                  console.log(file);//得到file的信息
         *                  //这里写上传成功后的回调操作
         *                  return facade.success('上传成功');//如果你设置了回调函数，那么上传成功的提示要自己调用函数进行返回
         *              }
         *      data-mime="*"//非必设，允许上传的文件后缀名，为空使用后台常规管理->系统配置->上传配置设置mime的值
         *      data-size="100mb"//非必设，允许上传的文件大小，单位b,B,k,K,kb,KB,m,M,mb,MB,g,G,gb,GB，为空使用后台常规管理->系统配置->上传配置设置size的值
         *      data-layVerify="required"//非必设，与layui的lay-verify相同
         *      data-layVerType="tips"//非必设，与layui的lay-verType相同
         * ></div>
         */
        upload: function (parentElem) {
            let obj = (typeof parentElem === "undefined") ? $(".laytpUpload") : $(".laytpUpload", parentElem);
            layui.each(obj, function (key, item) {
                let name = $(item).data("name");
                if (!name) facade.error("laytpUpload组件未定义name属性");
                let options = {
                    parentElem: parentElem
                    , el: item
                    , name: name
                    , type: $(item).data("type") ? $(item).data("type") : "local"
                    , viaServer: $(item).data("viaserver") ? $(item).data("viaserver") : 'via'
                    , fileCategoryId: $(item).data("filecategoryid") ? $(item).data("filecategoryid") : 0
                    , accept: $(item).data("accept") ? $(item).data("accept") : "image"
                    , width: $(item).data("width") ? $(item).data("width") : "0"
                    , height: $(item).data("height") ? $(item).data("height") : "0"
                    , mime: $(item).data("mime") ? $(item).data("mime") : ""
                    , size: $(item).data("size") ? $(item).data("size") : ""
                    , multi: $(item).data("multi") === true
                    , max: $(item).data("max")
                    , dir: $(item).data("dir") ? $(item).data("dir") : ""
                    , showUploadBtn: typeof $(item).data("showuploadbtn") === "undefined" || $(item).data("showuploadbtn") === true
                    , showChoiceBtn: typeof $(item).data("showchoicebtn") === "undefined" || $(item).data("showchoicebtn") === true
                    , uploaded: $(item).data("uploaded") && $(item).data("uploaded") !== "undefined" ? $(item).data("uploaded") : ""
                    , uploadedId: $(item).data("uploadedid") && $(item).data("uploadedid") !== "undefined" ? $(item).data("uploadedid") : ""
                    , uploadedFilename: $(item).data("uploadedfilename")&& $(item).data("uploadedfilename") !== "undefined" ? $(item).data("uploadedfilename") : ""
                    , uploadedCallback: $(item).data("uploadedcallback") ? $(item).data("uploadedcallback") : ""
                };
                options.layVerify = $(item).data("layverify") ? $(item).data("layverify") : "";
                options.layVerType = $(item).data("layvertype") ? $(item).data("layvertype") : "";
                options.params = {
                    "accept": options.accept
                };
                if (options.dir) options.params.dir = options.dir;
                if (options.width) options.params.width = options.width;
                if (options.height) options.params.height = options.height;
                if (options.mime) options.params.mime = options.mime;
                if (options.size) options.params.size = options.size;
                if (options.type) options.params.upload_type = options.type;
                if (options.fileCategoryId) options.params.file_category_id = options.fileCategoryId;
                if (options.viaServer === 'via') {
                    layui.laytpUpload.render(options);
                } else {
                    if (options.type === 'ali-oss') {
                        layui.laytpUpload.renderStsOss(options);
                    } else if (options.type === 'qiniu-kodo') {
                        layui.laytpUpload.renderTokenKodo(options);
                    }
                }
            });
        },

        /**
         * 选择图标组件
         * <div class="laytpIcon"//class="laytpIcon"是选择图标组件渲染的标识
         *      data-name="name"//提交表单时的name
         *      data-value="layui-icon layui-icon-rate-half"//已经选中的图标，用于编辑页面
         *      data-placeholder="请选择****"//非必设，默认为"请选择"，未选中数据时，提示文字
         *      data-layVerify="required"//非必设，与layui的lay-verify相同
         *      data-layVerType="tips"//非必设，与layui的lay-verType相同
         * ></div>
         */
        icon: function (parentElem) {
            let obj = (typeof parentElem === "undefined") ? $(".laytpIcon") : $(".laytpIcon", parentElem);
            layui.each(obj, function (key, item) {
                let name = $(item).data("name");
                if (!name) facade.error("laytpIcon组件未定义name属性");
                let options = {
                    el: item
                    , parentElem: parentElem
                    , name: name
                    , value: $(item).data("value") ? $(item).data("value") : ""
                    , placeholder: $(item).data("placeholder") ? $(item).data("placeholder") : "请选择"
                    , layVerify: $(item).data("layverify") ? $(item).data("layverify") : ""
                    , layVerType: $(item).data("layvertype") ? $(item).data("layvertype") : ""
                };
                layui.laytpIcon.render(options);
            });
        },

        /**
         * 时间选择器
         * <input //必须为input元素
         *  type="text" //type必须等于text，单行输入框
         *  class="layui-input laydate" //class中有laydate表示这个单行输入框需要渲染成时间选择器
         *  data-type="month" //data-type表示时间选择器的类型，默认为datetime，year=年选择器，只提供年列表选择，month=年月选择器，只提供年-月选择，date=日期选择器，可选择格式：年-月-日，time=时间选择器，可选择格式为时:分:秒，datetime=日期时间选择器，可选择格式为：年-月-日 时:分:秒
         *  date-isRange="false" // 表示是否为范围选择器，默认为false
         *  data-done="时间选择完毕之后的回调函数名" // 非必填
         - 比如定义的值为callbackFun，然后在全局定义如下函数即可执行回调
         function callbackFun(value, date, endDate){
                    console.log(value); //得到日期生成的值，如：2017-08-18
                    console.log(date); //得到日期时间对象：{year: 2017, month: 8, date: 18, hours: 0, minutes: 0, seconds: 0}
                    console.log(endDate); //得结束的日期时间对象，开启范围选择（range: true）才会返回。对象成员同上。
               }
         *  name="date" //提交表单时的name
         *  id="date" //元素的id值
         *  autocomplete="off" //不要浏览器记住已经输入过的值进行输入提示
         *  placeholder="请选择时间" //输入框为空时的提示文字
         *  />
         */
        laydate: function (parentElem) {
            // return true;
            let obj = (typeof parentElem === "undefined") ? $("input[type='text']").filter(".laydate") : $("input[type='text']", parentElem).filter(".laydate");
            layui.each(obj, function (key, item) {
                let elem = item;
                let type = $(item).data('type') ? $(item).data('type') : 'datetime';
                let isRange = $(item).data('isrange');
                let doneFun = $(item).data('done');
                layui.laydate.render({
                    elem: elem //指定元素
                    , type: type
                    , range: isRange
                    , done: function(value, date, endDate){
                        if(doneFun){
                            window[doneFun].call(this, value, date, endDate);
                        }
                        // doneFun(value, date, endDate);
                        // console.log(value); //得到日期生成的值，如：2017-08-18
                        // console.log(date); //得到日期时间对象：{year: 2017, month: 8, date: 18, hours: 0, minutes: 0, seconds: 0}
                        // console.log(endDate); //得结束的日期时间对象，开启范围选择（range: true）才会返回。对象成员同上。
                    }
                });
            });
        },

        /**
         * 颜色选择器
         * <div class="colorPicker"
         *  data-id="color_picker"//输入框的id属性值，默认和name相同，当name有中括号时，jquery获取不到对象，需要单独设置id值
         *  data-name="color_picker"//提交表单的name
         *  data-color="#000"//默认颜色
         *  data-format="hex"//颜色格式，hex(16进制色值，例:#000)，rgb(例：rgb(255,255,255))
         *  data-alpha="true"//开启透明度，默认false
         *  data-predefine="true"//是否开启预定义颜色
         *  data-colors="['#ff4500','#1e90ff','rgba(255, 69, 0, 0.68)','rgb(255, 120, 0)']"//开启预定义颜色后，设置预定义颜色的数组
         * ></div>
         */
        colorPicker: function (parentElem) {
            let obj = (typeof parentElem === "undefined") ? $("div").filter(".colorPicker") : $("div", parentElem).filter(".colorPicker");
            layui.each(obj, function (key, item) {
                let name = $(item).data("name");
                let id = $(item).data("id") ? $(item).data("id") : name;
                let colorPickerTemplate =
                    '<div class="layui-input-inline" style="margin-right: -1px;">' +
                    '   <input type="text" class="layui-input" id="' + id + '" name="' + name + '" readonly="readonly" />' +
                    '</div>' +
                    '<div class="layui-input-inline">' +
                    '   <div id="color_picker_' + id + '"></div>' +
                    '</div>'
                ;
                $(item).html(colorPickerTemplate);

                let options = {};
                options.elem = "#color_picker_" + id;
                let color = $(item).data("color");
                if (color) {
                    options.color = color;
                    $("#" + id).val(color);
                }
                let format = $(item).data("format");
                if (format) options.format = format;
                let alpha = $(item).data("alpha");
                if (alpha) options.alpha = alpha;
                let predefine = $(item).data("predefine");
                if (predefine) options.predefine = predefine;
                let colors = $(item).data("colors");
                if (colors) options.colors = eval(colors);
                options.size = 'xs';

                options.change = function (color) {
                    $("#" + id).val(color);
                };

                options.done = function (color) {
                    $("#" + id).val(color);
                };
                layui.colorpicker.render(options);
            });
        },

        /**
         * 编辑器
         *  此方法已废弃，实现方式变更，使用iframe调用编辑器页面进行展示
         *  原因：多种编辑器在单页面下进行加载展示，会有js、css冲突的情况，改成iframe就不会出现此问题
         */
        // editorRender: function (parentElem) {
        //     let obj = (typeof parentElem === "undefined") ? $(".editor") : $(".editor", parentElem);
        //     layui.each(obj, function (key, item) {
        //         let type = $(item).data('type');
        //         let id = $(item).attr('id');
        //         layui.use(type, function () {
        //             layui[type].render(id, parentElem);
        //         });
        //     });
        // },
    };

    /**
     * 渲染所有的表单元素
     */
    laytpForm.render = function (parentElem, callback) {
        //执行表单初始化方法
        layui.each(laytpForm.initData, function (key, item) {
            if (typeof item == "function") {
                item(parentElem);
            }
        });

        // 在表单初始化方法中，如果有ajax请求，允许将ajax延迟对象存入全局的defArr数组
        // 接下来，等待所有的延迟对象ajax请求执行完成，渲染表单元素
        $.when.apply($, defArr).done(function () {
            layui.form.render();
            if (typeof callback == "function") {
                callback();
            }
        });
    };

    /**
     * 数据表格表单元素的渲染
     */
    laytpForm.tableForm = {
        /**
         * 开关类型单选
         * @param field 字段名
         * @param d 数据表格所有数据集合
         * @param dataList 值举例：{"open":{"value":1,"text":"是"},"close":{"value":0,"text":"否"}}
         * @param filter 自定义filter，用于自定义监听表格单选按钮点击事件
         * @param isTreeTable 是否为树形表格
         * @param switchVal 新增参数，当前按钮选中状态值。
         *                  当按钮的选中状态不是由数据结果的某个字段值定义，而是使用多个字段进行判断或者使用数据集中某个对象里面的值进行判断得到时，
         *                  在调用switch前，自行判断得到按钮状态值，然后使用此参数传递。此参数的优先级高于d[field]的值
         * @returns {string}
         */
        switch: function (field, d, dataList, filter, isTreeTable, switchVal) {
            var switchNowVal = switchVal ? switchVal : d[field];
            let funcName = facade.underlineToCamel(field, true);
            let url = window.apiPrefix + "set" + funcName;
            if (facade.hasAuth(url)) {
                if (typeof filter === "undefined") {
                    filter = "laytp-table-switch";
                }
                let layText = dataList.open.text + "|" + dataList.close.text;
                return "<input openValue='" + dataList.open.value + "' closeValue='" + dataList.close.value + "' idVal='" + d.id + "' type='checkbox' name='" + field + "' value='" + dataList.open.value + "' lay-skin='switch' lay-text='" + layText + "' lay-filter='" + filter + "' " + ((switchNowVal == dataList.open.value) ? "checked='checked'" : "") + " />";
            } else {
                return laytp.tableFormatter.status(field, switchNowVal, {
                    "value": [dataList.open.value, dataList.close.value],
                    "text": [dataList.open.text, dataList.close.text]
                }, isTreeTable);
            }
        },

        /**
         * 回收站开关类型单选，回收站开关和普通开关分开的原因是，回收站开关请求后台的url不同
         * @param field 字段名
         * @param d 数据表格所有数据集合
         * @param dataList 值举例：{"open":{"value":1,"text":"是"},"close":{"value":0,"text":"否"}}
         * @param filter 自定义filter，用于自定义监听表格单选按钮点击事件
         * @param isTreeTable 是否为树形表格
         * @returns {string}
         */
        recycleSwitch: function (field, d, dataList, filter, isTreeTable) {
            let funcName = facade.underlineToCamel(field, true);
            let url = window.apiPrefix + "set" + funcName;
            if (facade.hasAuth(url)) {
                if (typeof filter === "undefined") {
                    filter = "laytp-recycle-table-switch";
                }
                let layText = dataList.open.text + "|" + dataList.close.text;
                return "<input openValue='" + dataList.open.value + "' closeValue='" + dataList.close.value + "' idVal='" + d.id + "' type='checkbox' name='" + field + "' value='" + dataList.open.value + "' lay-skin='switch' lay-text='" + layText + "' lay-filter='" + filter + "' " + ((d[field] == dataList.open.value) ? "checked='checked'" : "") + " />";
            } else {
                return laytp.tableFormatter.status(field, d[field], {
                    "value": [dataList.open.value, dataList.close.value],
                    "text": [dataList.open.text, dataList.close.text]
                }, isTreeTable);
            }
        },

        /**
         * 开启表格编辑的单行输入框，如果当前登录者有设置这个字段的权限，数据表格中渲染成单行输入框
         * @param field
         * @param d
         * @param url
         * @param callback
         * @returns {string|boolean}
         */
        editInput: function (field, d, url, callback) {
            if (typeof url === "undefined") {
                return d[field];
            }
            if (typeof callback === "undefined") {
                callback = '';
            }
            if (facade.hasAuth(url)) {
                return '' +
                    '<div class="layui-input-inline">' +
                    '   <div class="layui-input-inline layui-table-input-inline">' +
                    '       <input type="text" class="layui-input layui-table-input" autocomplete="off" ' +
                    'style="height:28px;" id="layui-table-input_' + d.id + '" value="' + d[field] + '" ' +
                    'data-id="' + d.id + '" ' +
                    'data-value="' + d[field] + '" ' +
                    'data-field="' + field + '" ' +
                    'data-callback="' + callback + '" ' +
                    'data-url="' + url + '" />' +
                    '   </div>' +
                    '</div>';
            } else {
                return d[field];
            }
        },

        /**
         * 开启表格编辑的单行输入框，如果当前登录者有设置这个字段的权限，则数据表格中渲染成单行输入框
         * @param field
         * @param d
         * @param url
         * @returns {string|boolean}
         */
        recycleEditInput: function (field, d, url) {
            if (typeof url === "undefined") {
                return d[field];
            }
            if (facade.hasAuth(url)) {
                return '' +
                    '<div class="layui-input-inline">' +
                    '   <div class="layui-input-inline layui-table-input-inline">' +
                    '       <input type="text" class="layui-input layui-recycle-table-input" autocomplete="off" style="height:28px;" id="layui-recycle-table-input_' + d.id + '" value="' + d[field] + '" data-id="' + d.id + '" data-value="' + d[field] + '" data-field="' + field + '" data-url="' + url + '" />' +
                    '   </div>' +
                    '</div>';
            } else {
                return d[field];
            }
        },
    };

    /**
     * 监听数据表格表单元素相关事件
     */
    laytpForm.onTableForm = {
        /**
         * 监听，数据表格，单选按钮点击事件
         */
        laytpTableSwitch: function () {
            layui.form.on("switch(laytp-table-switch)", function (obj) {
                if (!window.apiPrefix) {
                    facade.error("window.apiPrefix未定义");
                    return false;
                }
                let openValue = obj.elem.attributes["openValue"].nodeValue;
                let closeValue = obj.elem.attributes["closeValue"].nodeValue;
                let idVal = obj.elem.attributes["idVal"].nodeValue;
                let funcName = facade.underlineToCamel(this.name, true);
                let url = window.apiPrefix + "set" + funcName;
                if (facade.hasAuth(url)) {
                    let postData = {};
                    if (obj.elem.checked) {
                        postData = {field_val: openValue, id: idVal, is_recycle: 0};
                    } else {
                        postData = {field_val: closeValue, id: idVal, is_recycle: 0};
                    }
                    facade.ajax({route: url, data: postData});
                } else {
                    facade.error("无权进行此操作");
                }
            });
        },

        /**
         * 监听，回收站数据表格，单选按钮点击事件
         */
        laytpRecycleTableSwitch: function () {
            layui.form.on("switch(laytp-recycle-table-switch)", function (obj) {
                if (!window.apiPrefix) {
                    facade.error("window.apiPrefix未定义");
                    return false;
                }
                let openValue = obj.elem.attributes["openValue"].nodeValue;
                let closeValue = obj.elem.attributes["closeValue"].nodeValue;
                let idVal = obj.elem.attributes["idVal"].nodeValue;
                let funcName = facade.underlineToCamel(this.name, true);
                let url = window.apiPrefix + "set" + funcName;
                if (facade.hasAuth(url)) {
                    let postData = {};
                    if (obj.elem.checked) {
                        postData = {field_val: openValue, id: idVal, is_recycle: 1};
                    } else {
                        postData = {field_val: closeValue, id: idVal, is_recycle: 1};
                    }
                    facade.ajax({route: url, data: postData, showLoading: true});
                } else {
                    facade.error("无权进行此操作");
                }
            });
        },

        /**
         * 监听，数据表格，可编辑单行输入框，失去焦点事件，进行编辑
         */
        tableEditInput: function () {
            $(document).off("blur", ".layui-table-input").on('blur', '.layui-table-input', function () {
                let obj = $(this);
                let url = obj.data('url');
                if (!url) {
                    facade.error("输入框编辑url未定义");
                    return false;
                }
                let id = obj.data('id');
                let value = obj.val();
                let field = obj.data('field');
                let oldVal = obj.data('value');
                let callback = obj.data('callback');
                if (value.toString() !== oldVal.toString()) {
                    if (facade.hasAuth(url)) {
                        facade.ajax({
                            route: url,
                            data: {"field": field, "field_val": value, "id": id, is_recycle: 0},
                            successAlert: true
                        }).done(function (res) {
                            if (res.code === 0) {
                                if(callback){
                                    eval(callback + "()");
                                }else{
                                    funController.tableRender();
                                }
                            }
                        });
                    } else {
                        facade.error("无权进行此操作");
                    }
                }
            });
        },

        /**
         * 监听，回收站数据表格，可编辑单行输入框，失去焦点事件，进行编辑
         */
        recycleTableEditInput: function () {
            $(document).off("blur", ".layui-recycle-table-input").on('blur', '.layui-recycle-table-input', function () {
                let obj = $(this);
                let url = obj.data('url');
                if (!url) {
                    facade.error("输入框编辑url未定义");
                    return false;
                }
                let id = obj.data('id');
                let value = obj.val();
                let field = obj.data('field');
                let oldVal = obj.data('value');
                if (value.toString() !== oldVal.toString()) {
                    if (facade.hasAuth(url)) {
                        facade.ajax({
                            route: url,
                            data: {"field": field, "field_val": value, "id": id, is_recycle: 1},
                            successAlert: true,
                            showLoading: true
                        }).done(function (res) {
                            if (res.code === 0) {
                                funRecycleController.tableRender();
                            }
                        });
                    } else {
                        facade.error("无权进行此操作");
                    }
                }
            });
        },

        /**
         * 监听搜索表单提交事件
         */
        laytpSearchFormSubmit: function () {
            layui.form.on("submit(laytp-search-form)", function (obj) {
                funController.tableRender(obj.field);
                return false;
            });
        },

        /**
         * 监听回收站搜索表单提交事件
         */
        laytpRecycleSearchFormSubmit: function () {
            layui.form.on('submit(laytp-recycle-search-form)', function (obj) {
                funRecycleController.tableRender(obj.field);
                return false;
            });
        },

        /**
         * 重置搜索表单的搜索条件
         */
        laytpSearchFormReset: function () {
            $(document).off("click", ".laytp-search-form-reset").on("click", ".laytp-search-form-reset", function () {
                var thisObj = $(this);
                var formObj = thisObj.closest("form");
                // 设置这个表单下所有xmSelect的值为空
                $(".xmSelect", formObj).each(function(index, item){
                    xmSelectObj[$(item).data('name')].setValue([]);
                });
                formObj.trigger("reset");
                funController.tableRender();
            });
        },

        /**
         * 重置回收站搜索表单的搜索条件
         */
        laytpRecycleSearchFormReset: function () {
            $(document).off("click", ".laytp-recycle-search-form-reset").on("click", ".laytp-recycle-search-form-reset", function () {
                $(".layui-form").trigger("reset");
                funRecycleController.tableRender();
            });
        },

        /**
         * 导出搜索条件下的数据
         */
        laytpSearchFormExport: function () {
            $(document).off("click", ".laytp-search-form-export").on("click", ".laytp-search-form-export", function () {
                let data = layui.form.val("search-form");
                facade.ajax({
                    route: apiPrefix + "export",
                    params: data
                }).done(function (res) {
                    if (res.code === 0) {
                        // 创建a标签，设置属性，并触发点击下载
                        var $a = $("<a>");
                        $a.attr("href", res.data.file);
                        $a.attr("download", res.data.filename);
                        $("body").append($a);
                        $a[0].click();
                        $a.remove();
                    }
                });
            });
        },

        /**
         * 导出回收站搜索条件下的数据
         */
        laytpRecycleSearchFormExport: function () {
            $(document).off("click", ".laytp-recycle-search-form-export").on("click", ".laytp-recycle-search-form-export", function () {
                let data = layui.form.val("recycle-search-form");
                facade.ajax({
                    route: apiPrefix + "recycleExport",
                    params: data
                }).done(function (res) {
                    if (res.code === 0) {
                        // 创建a标签，设置属性，并触发点击下载
                        var $a = $("<a>");
                        $a.attr("href", res.data.file);
                        $a.attr("download", res.data.filename);
                        $("body").append($a);
                        $a[0].click();
                        $a.remove();
                    }
                });
            });
        },
    };

    /**
     * 监听数据表格表单元素
     */
    layui.each(laytpForm.onTableForm, function (key, item) {
        if (typeof item == "function") {
            item()
        }
    });

    //输出模块
    exports(MOD_NAME, laytpForm);

    //注入layui组件中，供全局调用
    layui.laytpForm = laytpForm;

    //注入window全局对象中，供全局调用
    window.laytpForm = laytpForm;
});
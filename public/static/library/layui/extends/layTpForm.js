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
layui.define(["jquery", "facade"], function (exports) {
    const MOD_NAME = "layTpForm";
    let $ = layui.$
        , facade = layui.facade
    ;

    let layTpForm = {};

    //延迟对象数组
    let defArr = [];

    /**
     * 初始化所有表单元素的数据，比如下拉框的待选择项和选定项
     */
    layTpForm.initData = {
        /**
         * 默认下拉框
         * <select
         *  name="name"//提交表单时的name
         *  id="id"//节点的id值
         *  data-source="/plugin/core/auth.menu/getSelectOptionData" //数据源，插件自动拼接参数layui_select=1
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
                            path: source,
                            params: {"layui_select": 1},
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
         * xmSelect下拉多选组件
         * <div class="xmSelect"//class="xmSelect"是xmSelect组件渲染的标识
         *      data-name="name"//必设，提交表单时的name
         *      data-sourceType="url"//非必设，默认为data，标注数据类型，url表示ajax请求数据，data表示直接解析数据
         *      //必设，data-source支持两种数据结构数组和对象，url类型也可以返回这两种数据结构
         *      data-source="[{name:'唱歌',value:'1'},{name:'跳舞',value:'2'},{name:'朗诵',value:'3'},{name:'武术',value:'4'}]"//这种，对应的data-sourceType="data"，不需要指定data-textField和data-valueField，默认使用name的值作为展示的文本，value的值作为表单提交的值
         *      data-source="[{text:'唱歌',id:'1'},{text:'跳舞',id:'2'},{text:'朗诵',id:'3'},{text:'武术',id:'4'}]"//这种，对应的data-sourceType="data"，需要指定data-textField="text"，data-valueField="id"
         *      data-source="{1:唱歌,2:跳舞,3:朗诵,4:武术}"//这种，对应的data-sourceType="data"，key就是表单提交的值，value就是展示的文本
         *      data-source="plugin/core/auth.menu/getTreeList" //这种，对应的data-sourceType="url"，这里是一个Api接口地址，返回的数据结构是数组或者对象，具体请参考当data-sourceType="data"时，data-source的说明
         *      data-sourceTree="true"//非必设，是否展示成树形结构，与data-source连用，当为true时，需要展示data-source的值是树形结构的数据
         *      data-textField="name"//非必设，默认为name，当数据源的数据结构是一个数组时，显示的文本字段名称，当数据源数据结构是一个对象时，此设置无意义
         *      data-subTextField="value"//非必设，附属的文本字段名称，仅在下拉框列表中展示
         *      data-valueField="value"//非必设，默认为value，当数据源的数据结构是一个数组时，提交表单的值，当数据源数据结构是一个对象时，此设置无意义
         *      data-iconField="icon"//非必设，当数据源的数据结构是一个数组时，显示文本前展示的图标字段名，为空或者不设置，则显示文本前无图标，当数据源数据结构是一个对象时，此设置无意义
         *      data-radio="true"//非必设，单选模式，true=开启单选模式，false=关闭单选模式，默认false
         *      data-selected="1,2"//非必设，默认选中的数据，需要在data中存在，默认不选中任何项
         *      data-max="2"//非必设，多选模式下，最多可选个数，默认不限制
         *      data-placeholder="请选择****"//非必设，类似placeholder，未选中数据时，提示文字，默认为"请选择"
         *      data-layVerify="required"//非必设，与layui的lay-verify相同
         *      data-layVerType="tips"//非必设，与layui的lay-verType相同
         *      data-direction="up"//非必设，下拉方向，增加这个参数是因为xmSelect没有做成漂浮，在弹出窗最底层有xmSelect时，会影响美观度
         *      data-paging="true"//非必设，是否分页，目前仅在data-sourceType="url"时有效，且后台返回的数据结构是使用thinkphp的paginate方法返回的数据结构
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
                let source = $(item).data("source").toString();
                let options = {
                    el: item
                    , name: name
                    , language: 'zn'
                    , data: []
                    , filterable: true
                    , radio: $(item).data("radio") === true
                    , searchTips: "输入关键字进行搜索"
                    , maxMethod: function (selectedData) {
                        facade.error("最多可选" + selectedData.length + "个数据");
                    }
                    , layVerify: $(item).data("layverify") ? $(item).data("layverify") : ""
                    , layVerType: $(item).data("layvertype") ? $(item).data("layvertype") : ""
                    , clickClose: $(item).data("radio") === true
                    , tips: $(item).data("placeholder") ? $(item).data("placeholder") : "请选择"
                    , toolbar: {show: !($(item).data("radio") === true)}
                };
                let max = $(item).data("max");
                if (max) options.max = max;
                let selected = $(item).data("selected");
                if (selected && selected !== "undefined") {
                    selected = selected.toString();
                    options.initValue = selected.split(',');
                }
                options.direction = $(item).data("direction") ? $(item).data("direction") : "auto";
                let xmSelectObj = xmSelect.render(options);
                options.textField = $(item).data("textfield") ? $(item).data("textfield") : "name";
                options.subTextField = $(item).data("subtextfield") ? $(item).data("subtextfield") : "";
                options.valueField = $(item).data("valuefield") ? $(item).data("valuefield") : "value";
                options.iconField = $(item).data("iconfield") ? $(item).data("iconfield") : "";
                options.sourceTree = $(item).data("sourcetree") === true;
                let updateOptions = {};
                options.onchange = $(item).data("onchange") ? $(item).data("onchange") : "";
                if (options.onchange) {
                    updateOptions.on = function (params) {
                        window[options.onchange].call(this, params);
                    };
                }
                //model是设置选中后，input框的模板
                updateOptions.model = {
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
                updateOptions.model.label.block.showIcon = !($(item).data("radio") === true);
                //这里是设置下拉框前面的单选按钮或者复选框是否显示
                updateOptions.model.icon = ($(item).data("radio") === true && !options.sourceTree) ? "hidden" : "show";
                //template是设置下拉框的模板
                updateOptions.template = function (item) {
                    let template = item.name;
                    template = options.iconField ? '<i class="' + item.item[options.iconField] + ' margin-right5"></i>' + template : template;
                    template = options.subTextField ? template + '<span style="position: absolute; right: 10px; color: #8799a3">' + item.item[options.subTextField] + '</span>' : template;
                    return template;
                };
                updateOptions.prop = {
                    name: options.textField
                    , value: options.valueField
                };
                if (options.sourceTree) {
                    updateOptions.tree = {
                        //是否显示树状结构
                        show: true,
                        //是否展示三角图标
                        showFolderIcon: true,
                        //是否显示虚线
                        showLine: false,
                        //间距
                        indent: 20,
                        //默认展开节点的数组, 为 true 时, 展开所有节点
                        expandedKeys: true,
                        //是否严格遵守父子模式
                        strict: false,
                        //是否开启极简模式
                        simple: true,
                    };
                }
                if (sourceType === "url") {
                    //这个ajax请求无需存入延迟对象数组，因为xmSelect是一个一个进行渲染的，不像layui.form.render()方法，xmSelect没有全局统一方法一次性渲染所有的xmSelect
                    facade.ajax({path: source, successAlert: false}).done(function (res) {
                        let paging = $(item).data("paging") === true;
                        if (paging) {
                            updateOptions.paging = true;
                            updateOptions.pageSize = res.data["per_page"];
                            updateOptions.data = res.data.data;
                        } else {
                            updateOptions.data = res.data;
                        }
                        xmSelectObj.update(updateOptions);
                    });
                } else if (sourceType === "data") {
                    updateOptions.data = eval(source);
                    xmSelectObj.update(updateOptions);
                }
            });
        },

        /**
         * 上传组件
         * <div class="layTpUpload"//class="layTpUpload"是上传组件渲染的标识
         *      data-name="name"//必设，提交表单时的name
         *      data-accept="image"//非必设，允许上传的类型，image=图片，video=视频，audio=音频，file=任意文件，默认为image
         *      data-width="500"//非必设，accept="image"时，要求上传图片的最大宽度，默认不限制
         *      data-height="300"//非必设，accept="image"时，要求上传图片的最大高度，默认不限制
         *      data-multi="true"//非必设，多文件模式，true=开启多文件模式，false=关闭多文件模式，默认false
         *      data-max="2"//非必设，多文件模式下有效的参数，设置允许最多上传的文件个数，默认不限制个数
         *      data-dir=""//非必设，上传的目录，允许不传，不传就传到storage目录下，如果不为空，则在storage目录下创建对应的目录，允许使用/指明多级目录
         *      data-url=""//非必设，文件上传请求的后台地址，默认为facade.url("plugin/core/common/upload", {'accept': options.accept, 'upload_dir': options.upload_dir})，自定义的url返回数据格式要和admin/common/upload保持一致
         *      data-uploaded=""//非必设，已经上传的文件列表，多个以英文半角的逗号+空格进行分割，用于编辑页面展示已经上传过的文件
         *      data-mime="*"//非必设，允许上传的文件类型，同时检测后缀和文件实际类型，为空使用系统默认配置
         *      data-size="100mb"//非必设，允许上传的文件大小，单位b,k,kb,m,mb,g,gb，为空使用系统默认配置
         *      data-layVerify="required"//非必设，与layui的lay-verify相同
         *      data-layVerType="tips"//非必设，与layui的lay-verType相同
         * ></div>
         */
        upload: function (parentElem) {
            let obj = (typeof parentElem === "undefined") ? $(".layTpUpload") : $(".layTpUpload", parentElem);
            layui.each(obj, function (key, item) {
                let name = $(item).data("name");
                if (!name) facade.error("layTpUpload组件未定义name属性");
                let options = {
                    parentElem: parentElem
                    , el: item
                    , name: name
                    , accept: $(item).data("accept") ? $(item).data("accept") : "image"
                    , inputWidth: $(item).data("inputwidth") ? $(item).data("inputwidth") : "70%"
                    , width: $(item).data("width") ? $(item).data("width") : "0"
                    , height: $(item).data("height") ? $(item).data("height") : "0"
                    , mime: $(item).data("mime") ? $(item).data("mime") : ""
                    , size: $(item).data("size") ? $(item).data("size") : ""
                    , multi: $(item).data("multi") === true
                    , max: $(item).data("max")
                    , dir: $(item).data("dir") ? $(item).data("dir") : ""
                    , uploaded: $(item).data("uploaded") ? $(item).data("uploaded") : ""
                };
                options.url = $(item).data("url") ? $(item).data("url") : facade.url("plugin/core/common/upload");
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
                layui.layTpUpload.render(options);
            });
        },

        /**
         * 选择图标组件
         * <div class="layTpIcon"//class="layTpIcon"是选择图标组件渲染的标识
         *      data-name="name"//提交表单时的name
         *      data-value="layui-icon layui-icon-rate-half"//已经选中的图标，用于编辑页面
         *      data-placeholder="请选择****"//非必设，默认为"请选择"，未选中数据时，提示文字
         *      data-layVerify="required"//非必设，与layui的lay-verify相同
         *      data-layVerType="tips"//非必设，与layui的lay-verType相同
         * ></div>
         */
        icon: function (parentElem) {
            let obj = (typeof parentElem === "undefined") ? $(".layTpIcon") : $(".layTpIcon", parentElem);
            layui.each(obj, function (key, item) {
                let name = $(item).data("name");
                if (!name) facade.error("layTpIcon组件未定义name属性");
                let options = {
                    el: item
                    , parentElem: parentElem
                    , name: name
                    , value: $(item).data("value") ? $(item).data("value") : ""
                    , placeholder: $(item).data("placeholder") ? $(item).data("placeholder") : "请选择"
                    , layVerify: $(item).data("layverify") ? $(item).data("layverify") : ""
                    , layVerType: $(item).data("layvertype") ? $(item).data("layvertype") : ""
                };
                layui.layTpIcon.render(options);
            });
        },

        /**
         * 时间选择器
         * <input //必须为input元素
         *  type="text" //type必须等于text，单行输入框
         *  class="layui-input laydate" //class中有laydate表示这个单行输入框需要渲染成时间选择器
         *  date-type="month" //data-type表示时间选择器的类型，默认为datetime，year=年选择器，只提供年列表选择，month=年月选择器，只提供年-月选择，date=日期选择器，可选择格式：年-月-日，time=时间选择器，可选择格式为时:分:秒，datetime=日期时间选择器，可选择格式为：年-月-日 时:分:秒
         *  date-isRange="false" // 表示是否为范围选择器，默认为false
         *  name="date" //提交表单时的name
         *  id="date" //元素的id值
         *  autocomplete="off" //不要浏览器记住已经输入过的值进行输入提示
         *  placeholder="请选择时间" //输入框为空时的提示文字
         *  />
         */
        laydate: function (parentElem) {
            let obj = (typeof parentElem === "undefined") ? $("input[type='text']").filter(".laydate") : $("input[type='text']", parentElem).filter(".laydate");
            layui.each(obj, function (key, item) {
                let elem = item;
                let type = $(item).data('type') ? $(item).data('type') : 'datetime';
                let isRange = $(item).data('isrange');
                layui.laydate.render({
                    elem: elem //指定元素
                    , type: type
                    , range: isRange
                });
            });
        }
    };

    /**
     * 渲染所有的表单元素
     */
    layTpForm.render = function (parentElem) {
        //执行表单初始化方法
        layui.each(layTpForm.initData, function (key, item) {
            if (typeof item == "function") {
                item(parentElem)
            }
        });

        // 在表单初始化方法中，如果有ajax请求，允许将ajax延迟对象存入全局的defArr数组
        // 接下来，等待所有的延迟对象ajax请求执行完成，渲染表单元素
        $.when.apply($, defArr).done(function () {
            layui.form.render();
        });
    };

    /**
     * 数据表格表单元素的渲染
     */
    layTpForm.tableForm = {
        /**
         * 开关类型单选
         * @param field 字段名
         * @param d 数据表格所有数据集合
         * @param dataList 值举例：{"open":{"value":1,"text":"是"},"close":{"value":0,"text":"否"}}
         * @returns {string}
         */
        switch: function (field, d, dataList) {
            let layText = dataList.open.text + "|" + dataList.close.text;
            return "<input openValue='" + dataList.open.value + "' closeValue='" + dataList.close.value + "' idVal='" + d.id + "' type='checkbox' name='" + field + "' value='" + dataList.open.value + "' lay-skin='switch' lay-text='" + layText + "' lay-filter='laytp-table-switch' " + ((d[field] == dataList.open.value) ? "checked='checked'" : "") + " />";
        },

        /**
         * 回收站开关类型单选，回收站开关和普通开关分开的原因是，回收站开关请求后台的url不同
         * @param field 字段名
         * @param d 数据表格所有数据集合
         * @param dataList 值举例：{"open":{"value":1,"text":"是"},"close":{"value":0,"text":"否"}}
         * @returns {string}
         */
        recycleSwitch: function (field, d, dataList) {
            let layText = dataList.open.text + "|" + dataList.close.text;
            return "<input openValue='" + dataList.open.value + "' closeValue='" + dataList.close.value + "' idVal='" + d.id + "' type='checkbox' name='" + field + "' value='" + dataList.open.value + "' lay-skin='switch' lay-text='" + layText + "' lay-filter='laytp-recycle-table-switch' " + ((d[field] == dataList.open.value) ? "checked='checked'" : "") + " />";
        },
    };

    /**
     * 监听数据表格表单元素相关事件
     */
    layTpForm.onTableForm = {
        /**
         * 监听，数据表格，单选按钮点击事件
         */
        laytpTableSwitch: function () {
            layui.form.on("switch(laytp-table-switch)", function (obj) {
                if (facade.hasAuth(apiPrefix + "tableEdit")) {
                    let openValue = obj.elem.attributes["openValue"].nodeValue;
                    let closeValue = obj.elem.attributes["closeValue"].nodeValue;
                    let idVal = obj.elem.attributes["idVal"].nodeValue;
                    let postData = {};
                    if (obj.elem.checked) {
                        postData = {field: this.name, field_val: openValue, ids: idVal};
                    } else {
                        postData = {field: this.name, field_val: closeValue, ids: idVal};
                    }
                    facade.ajax({path: apiPrefix + "tableEdit", params: postData});
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
                if (facade.hasAuth(apiPrefix + "recycleTableEdit")) {
                    let openValue = obj.elem.attributes["openValue"].nodeValue;
                    let closeValue = obj.elem.attributes["closeValue"].nodeValue;
                    let idVal = obj.elem.attributes["idVal"].nodeValue;
                    let postData = {};
                    if (obj.elem.checked) {
                        postData = {field: this.name, field_val: openValue, ids: idVal};
                    } else {
                        postData = {field: this.name, field_val: closeValue, ids: idVal};
                    }
                    facade.ajax({path: appName + "/" + controller + "/recycleTableEdit", params: postData});
                } else {
                    facade.error("无权进行此操作");
                }
            });
        },

        /**
         * 监听搜索表单提交事件
         */
        layTpSearchFormSubmit: function () {
            layui.form.on("submit(laytp-search-form)", function (obj) {
                funController.tableRender(obj.field);
                //如果顶部有tab切换，且搜索表单设置了tab切换的字段值进行搜索，同时修改tab切换选中的项目
                let tabField = $(".laytp-tab-click-search").data("field");
                if (tabField) {
                    let tabFieldValue = $("#" + tabField).val();
                    layui.each($(".laytp-tab-click-search"), function (key, item) {
                        $(item).removeClass("layui-this");
                        if ($(item).data('value').toString() === tabFieldValue.toString()) {
                            $(item).addClass("layui-this");
                        }
                    });
                }
                return false;
            });
        },

        /**
         * 监听回收站搜索表单提交事件
         */
        layTpRecycleSearchFormSubmit: function () {
            layui.form.on('submit(laytp-recycle-search-form)', function (obj) {
                funRecycleController.tableRender(obj.field);
                $("[lay-event='show-hidden-recycle-search-form']").html(" 隐藏搜索");
                return false;
            });
        },

        /**
         * 重置搜索表单的搜索条件
         */
        layTpSearchFormReset: function () {
            $(document).off("click", ".laytp-search-form-reset").on("click", ".laytp-search-form-reset", function () {
                $(".layui-form").trigger("reset");
                funController.tableRender();
                //如果顶部有tab切换，且搜索表单设置了tab切换的字段值进行搜索，同时修改tab切换选中的项目
                let tabField = $(".laytp-tab-click-search").data("field");
                if (tabField) {
                    let tabFieldValue = $("#" + tabField).val();
                    layui.each($(".laytp-tab-click-search"), function (key, item) {
                        $(item).removeClass("layui-this");
                        if ($(item).data('value').toString() === tabFieldValue.toString()) {
                            $(item).addClass("layui-this");
                        }
                    });
                }
            });
        },

        /**
         * 重置回收站搜索表单的搜索条件
         */
        layTpRecycleSearchFormReset: function () {
            $(document).off("click", ".laytp-recycle-search-form-reset").on("click", ".laytp-recycle-search-form-reset", function () {
                $(".layui-form").trigger("reset");
                funRecycleController.tableRender();
            });
        }
    };

    /**
     * 监听数据表格表单元素
     */
    layui.each(layTpForm.onTableForm, function (key, item) {
        if (typeof item == "function") {
            item()
        }
    });

    //输出模块
    exports(MOD_NAME, layTpForm);

    //注入layui组件中，供全局调用
    layui.layTpForm = layTpForm;

    //注入window全局对象中，供全局调用
    window.layTpForm = layTpForm;
});
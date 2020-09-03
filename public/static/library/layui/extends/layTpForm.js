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
         * 下拉框
         * <select
         *  data-source="/admin/auth.menu/getTreeList" //数据源
         *  data-selected="1" //选中项的id值，用于添加表单表单元素的默认选中值
         *  data-value-field="id" //option中value属性使用列表的哪个字段，默认为id，特殊字段，k，表示使用列表的key，用于特殊的列表渲染，比如系统配置，所属分组，返回的数据结构是["basic"=>"基础配置","upload"=>"上传配置"]
         *  data-show-field="name" //option中，文本显示使用列表中哪个字段，默认为name，特殊字段，v，表示使用列表的value，用于特殊列表渲染，比如系统配置，所属分组，返回的数据结构是["basic"=>"基础配置","upload"=>"上传配置"]
         * >
         * </select>
         * @param layerDiv 弹出层标识
         */
        select: function (layerDiv) {
            let obj = (typeof layerDiv === "undefined") ? $("select") : $("select", "#layui-layer" + layerDiv);
            layui.each(obj, function (key, item) {
                let source = $(item).data("source");
                $(item).removeAttr("data-source");
                if (source) {
                    let selected = $(item).data("selected");
                    $(item).removeAttr("data-selected");
                    let valueField = $(item).data("value-field") ? $(item).data("value-field") : 'id';
                    $(item).removeAttr("data-value-field");
                    let showField = $(item).data("show-field") ? $(item).data("show-field") : 'name';
                    $(item).removeAttr("data-show-field");
                    //将ajax的延迟对象，存入延迟对象数组defArr中
                    defArr.push(
                        facade.ajax({path: source, successAlert: false}).then(function (res) {
                            $(item).append("<option value=\"\">------ 请选择 ------</option>");
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
         * xmSelect
         * <div class="xm-select"
         *  data-name="name"//提交表单时的name
         *  data-type="url"//标注数据类型，url表示ajax请求数据，data表示直接解析数据
         *  data-source="admin/auth.menu/getTreeList" //数据源，当data-type="url"时，这里是一个接口地址，当data-type="data"时，这里是一个数组字符串
         *  data-selected="[1,2]"//默认选中的数据，需要在data中存在
         *  data-max="2"//最多可选个数
         * ></div>
         * @param layerDiv
         */
        xmSelect:function(layerDiv){
            let obj = (typeof layerDiv === "undefined") ? $(".xm-select") : $(".xm-select", "#layui-layer" + layerDiv);
            layui.each(obj, function (key, item) {
                let name = $(item).data("name");
                if(!name){
                    facade.error("xmSelect组件未定义name属性");
                }
                let options = {
                    el: item
                    , name: name
                    , language: 'zn'
                    , data: eval($(item).data("source"))
                    , filterable: true
                    , searchTips: "输入关键字进行搜索"
                    , maxMethod:function(selected){
                        facade.error("最多可选" + selected.length + "个数据");
                    }
                };
                let max = $(item).data("max");
                if(max)options.max = max;
                let selected = $(item).data("selected");
                if(selected)options.initValue = eval(selected);
                xmSelect.render(options);
            });
        },

        /**
         * 上传组件
         */
        upload:function(layerDiv){
            let obj = (typeof layerDiv === "undefined") ? $(".layTpUpload") : $(".layTpUpload", "#layui-layer" + layerDiv);
            layui.each(obj, function (key, item) {
                let options = {};
                options.id = $(item).data('id');
                options.name = $(item).data('name');
                options.accept = $(item).data('accept') ? $(item).data('accept') : "image";
                options.is_multiple = $(item).data('is_multiple') === "true";
                options.upload_dir = $(item).data('upload_dir') ? $(item).attr('upload_dir') : "";
                options.upload_url = $(item).data('upload_url') ? $(item).data('upload_url') : facade.url("admin/common/upload", {
                    'accept': options.accept,
                    'upload_dir': options.upload_dir
                });
                layui.layTpUpload.render(options);
            });
        }
    };

    /**
     * 渲染所有的表单元素
     */
    layTpForm.render = function (layerDiv) {
        //执行表单初始化方法
        layui.each(layTpForm.initData, function (key, item) {
            if (typeof item == "function") {
                item(layerDiv)
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
         * @param data_list 值举例：{"open":{"value":1,"text":"是"},"close":{"value":0,"text":"否"}}
         * @returns {string}
         */
        switch: function (field, d, data_list) {
            let lay_text = data_list.open.text + "|" + data_list.close.text;
            return "<input open_value='" + data_list.open.value + "' close_value='" + data_list.close.value + "' id_val='" + d.id + "' type='checkbox' name='" + field + "' value='" + data_list.open.value + "' lay-skin='switch' lay-text='" + lay_text + "' lay-filter='laytp-table-switch' " + ((d[field] == data_list.open.value) ? "checked='checked'" : "") + " />";
        },

        /**
         * 回收站开关类型单选，回收站开关和普通开关分开的原因是，回收站开关请求后台的进行修改字段的地址不同
         * @param field 字段名
         * @param d 数据表格所有数据集合
         * @param data_list 值举例：{"open":{"value":1,"text":"是"},"close":{"value":0,"text":"否"}}
         * @returns {string}
         */
        recycleSwitch: function (field, d, data_list) {
            let lay_text = data_list.open.text + "|" + data_list.close.text;
            return "<input open_value='" + data_list.open.value + "' close_value='" + data_list.close.value + "' id_val='" + d.id + "' type='checkbox' name='" + field + "' value='" + data_list.open.value + "' lay-skin='switch' lay-text='" + lay_text + "' lay-filter='laytp-recycle-table-switch' " + ((d[field] == data_list.open.value) ? "checked='checked'" : "") + " />";
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
                let open_value = obj.elem.attributes["open_value"].nodeValue;
                let close_value = obj.elem.attributes["close_value"].nodeValue;
                let id_val = obj.elem.attributes["id_val"].nodeValue;
                let post_data = {};
                if (obj.elem.checked) {
                    post_data = {field: this.name, field_val: open_value, ids: id_val};
                } else {
                    post_data = {field: this.name, field_val: close_value, ids: id_val};
                }
                facade.ajax({path: "/admin/" + controller + "/setStatus", params: post_data});
            });
        },
        /**
         * 监听，回收站数据表格，单选按钮点击事件
         */
        laytpRecycleTableSwitch: function () {
            layui.form.on("switch(laytp-recycle-table-switch)", function (obj) {
                let open_value = obj.elem.attributes["open_value"].nodeValue;
                let close_value = obj.elem.attributes["close_value"].nodeValue;
                let id_val = obj.elem.attributes["id_val"].nodeValue;
                let post_data = {};
                if (obj.elem.checked) {
                    post_data = {field: this.name, field_val: open_value, ids: id_val};
                } else {
                    post_data = {field: this.name, field_val: close_value, ids: id_val};
                }
                facade.ajax({path: "/admin/" + controller + "/setRecycleStatus", params: post_data});
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
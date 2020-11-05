layui.use(["layTp"], function () {
    const
        $ = layui.$
        , tree = layui.layTpTree
        , facade = layui.facade
        , funController = {}
    ;

    let nowTableId = "";

    defaultHeightPopupDiv = "85%";
    defaultWidthPopupDiv = "760px";

    funController.getTreeTable = function () {
        facade.ajax({
            path: "plugin/core/autocreate.curd/getTreeTableList",
            successAlert: false,
            async: false
        }).done(function (res) {
            nowTableId = res.data[0].children[0].id;
            let treeData = getTreeData(res.data);

            tree.render({
                elem: '#tableList'
                , data: treeData
                , showCheckbox: false
                , accordion: true
                , id: 'tableList'
                , edit: ['del']
                , onlyIconControl: true
                , click: function (obj) {
                    if (typeof obj.data.children === "undefined") {
                        $('.layui-tree-txt').css('color', '');
                        $('.layui-tree-txt').css('font-size', '12px');
                        $('.layui-tree-txt').css('font-weight', 'normal');
                        $('.layui-tree-txt', obj.elem).css('color', 'var(--laytp-head-bg)');
                        $('.layui-tree-txt', obj.elem).css('font-size', '14px');
                        $('.layui-tree-txt', obj.elem).css('font-weight', 'bold');
                        nowTableId = obj.data.id;
                        $("#table_id").val(nowTableId);
                        $("[lay-filter=laytp-search-form]").click();
                    }
                }
            });

            $('.layui-tree-txt').eq(1).css('color', 'var(--laytp-head-bg)');
            $('.layui-tree-txt').eq(1).css('font-size', '14px');
            $('.layui-tree-txt').eq(1).css('font-weight', 'bold');
        });
    };

    funController.tableRender = function (where) {
        layui.table.render({
            elem: "#laytp-table"
            , id: controller
            , url: facade.url(apiPrefix + "getFieldList")
            , toolbar: "#curd_field_toolbar"
            , where: where
            // , where: {search_param: {table_id: {value: tableId, condition: "="}}}
            , even: true
            , method: "POST"
            , cellMinWidth: 80
            , loading: false
            , page: true
            , parseData: function (res) { //res 即为原始返回的数据
                return {
                    "code": res.code, //解析接口状态
                    "msg": res.msg, //解析提示文本
                    "count": res.data.total, //解析数据长度
                    "data": res.data.data //解析数据列表
                };
            }
            , cols: [[
                {type: "checkbox"}
                , {field: "field", title: "字段名称", align: "center"}
                , {field: "comment", title: "字段注释", align: "center"}
                , {field: "form_type", title: "表单元素", align: "center"}
                , {field: "operation", title: "操作", align: "center", toolbar: "#default_operation", width: 140}
            ]]
        });

        //监听数据表格顶部左侧按钮点击事件
        layui.table.on("toolbar(laytp-table)", function (obj) {
            if (defaultTableToolbar.indexOf(obj.event) !== -1) {
                //默认按钮点击事件
                layTp.tableToolbar(obj);
            } else {
                //自定义按钮点击事件
                switch (obj.event) {
                    //自定义按钮点击事件
                    case "addField":
                        table_id = nowTableId;
                        facade.popupDiv({
                            title: "添加字段",
                            path: "/plugin/core/autocreate.curd.field/add",
                            data: {table_id: nowTableId},
                            callback: function () {
                                $("[lay-filter=laytp-search-form]").click();
                            }
                        });
                        break;
                }
            }
        });

        //监听数据表格[操作列]按钮点击事件
        layui.table.on("tool(laytp-table)", function (obj) {
            if (obj.event === "del") {
                facade.popupConfirm({
                    text: "真的删除么?",
                    path: "plugin/core/autocreate.curd.field/del",
                    params: {ids: obj.data.id}
                }, function () {
                    $("[lay-filter=laytp-search-form]").click();
                });
            } else if (obj.event === "edit") {
                //弹出编辑层
                facade.popupDiv({
                    title: "编辑字段",
                    path: "/plugin/core/autocreate.curd.field/edit",
                    data: obj.data,
                    callback: function () {
                        $("[lay-filter=laytp-search-form]").click();
                    }
                });
                formTypeChangePrivate(obj.data.form_type, obj.data);
                if (obj.data.form_type === 'xm_select') {
                    selectDataFrom(obj.data.addition.data_from_type, obj.data);
                    selectDataFromTable(obj.data.addition.table_id, obj.data);
                }
                if (obj.data.form_type === 'linkage_select') {
                    linkageField(nowTableId, obj.data);
                    selectLinkageSearchTable(obj.data.addition.table_id, obj.data);
                }
            }
        });

        //监听鼠标双击行事件，双击行表示进行编辑
        layui.table.on("rowDouble(laytp-table)", function (obj) {
            obj.event = "edit";
            layTp.tableTool(obj);
        });
    };

    window.funController = funController;

    $(document).ready(function () {
        funController.getTreeTable();
        $("#table_id").val(nowTableId);
        $("[lay-filter=laytp-search-form]").click();

        //添加表，绑定点击事件
        $("a[lay-event='addTable']").on("click", function () {
            facade.popupDiv({
                title: "添加表"
                , path: "plugin/core/autocreate.curd.table/add"
                , height: "400px"
                , callback: function () {
                    funController.getTreeTable();
                }
            });
        });

        $(document).off("click", ".laytp-search-form-reset").on("click", ".laytp-search-form-reset", function () {
            $(".layui-form").trigger("reset");
            $("#table_id").val(nowTableId);
            $("[lay-filter=laytp-search-form]").click();
        });

        $(document).off("click", ".add-item").on("click", ".add-item", function () {
            let clickObj = $(this);
            let template = '<tr>' +
                '<td align="right">' +
                '<input type="text" class="layui-input" name="addition[value][]" />' +
                '</td>' +
                '<td>' +
                '<input type="text" class="layui-input" name="addition[text][]" />' +
                '</td>' +
                '<td align="center">' +
                '<input {{# if(d.formType === "checkbox"){ }}type="checkbox"{{# }else{ }}type="radio"{{# } }} lay-skin="primary" name="addition[default][]" /> ' +
                '</td>' +
                '<td>' +
                '<a class="layui-btn layui-btn-primary layui-btn-sm layui-icon layui-icon-delete del-item"></a>' +
                '</td>' +
                '</tr>';
            clickObj.parent().parent().before(layui.laytpl(template).render({formType: formType}));
            layui.form.render('radio');
            layui.form.render('checkbox');
        });

        $(document).off("click", ".add-color-picker").on("click", ".add-color-picker", function () {
            let clickObj = $(this);
            let template =
                '<tr>' +
                '   <td>' +
                '       <div class="colorPicker"' +
                '           data-name="addition[colors][]"' +
                '           data-id="addition_colors_{{d.randomKey}}"' +
                '       ></div>' +
                '   </td>' +
                '   <td>' +
                '       <a class="layui-btn layui-btn-primary layui-btn-sm layui-icon layui-icon-delete del-item"></a>' +
                '   </td>' +
                '</tr>'
            ;
            clickObj.parent().parent().before(layui.laytpl(template).render({
                formType: formType,
                randomKey: Math.random() * 10000000000000000000
            }));
            layui.layTpForm.render(clickObj.parent().parent().prev());
        });

        $(document).off("click", ".del-item").on("click", ".del-item", function () {
            let clickObj = $(this);
            clickObj.parent().parent().remove();
        });

        layui.form.on('select(data-from)', function(data){
            selectDataFrom(data.value);
        });

        layui.form.on('select(select-table)', function (data) {
            selectDataFromTable(data.value);
        });

        layui.form.on('select(linkage-select-table)', function (data) {
            linkageField(data.value);
        });
    });

    let table_id;
    window.setTableId = function (params) {
        table_id = params.arr[0].value;
    };

    function selectDataFrom(value, editData) {
        if (typeof editData === "undefined") {
            editData = {
                addition: {
                    table_id: "",
                }
            }
        }
        if (value === "table") {
            let xmSelectSetDataTable =
                '    <div class="layui-row margin-bottom6">' +
                '       <div class="layui-col-lg6 layui-col-md6 layui-col-sm6 layui-col-xs6">' +
                '           <label class="layui-form-label layui-form-required">数据表</label>' +
                '           <div class="layui-input-block">' +
                '           <select class="layui-select" name="addition[table_id]" lay-filter="select-table"' +
                '                data-source="/plugin/core/autocreate.curd.table/index"' +
                '                data-showField="table"\n' +
                '                data-placeholder="请选择数据表"\n' +
                '                data-selected="{{ d.addition.table_id }}"\n' +
                '           ></select>' +
                '           </div>' +
                '       </div>' +
                '       <div class="layui-col-lg6 layui-col-md6 layui-col-sm6 layui-col-xs6">' +
                '           <label class="layui-form-label layui-form-required" title="默认不限制，仅多选有效">主标题字段</label>' +
                '           <div class="layui-input-block">' +
                '               <select class="layui-select" name="addition[title_field]" id="titleField">' +
                '                   <option value="">请选择字段</option>' +
                '               </select>' +
                '           </div>' +
                '       </div>' +
                '    </div>' +
                '    <div class="layui-row margin-bottom6">' +
                '       <div class="layui-col-lg6 layui-col-md6 layui-col-sm6 layui-col-xs6">' +
                '           <label class="layui-form-label layui-form-required">副标题字段</label>' +
                '           <div class="layui-input-block">' +
                '               <select class="layui-select" name="addition[sub_title_field]" id="subTitleField">' +
                '                   <option value="">请选择字段</option>' +
                '               </select>' +
                '           </div>' +
                '       </div>' +
                '       <div class="layui-col-lg6 layui-col-md6 layui-col-sm6 layui-col-xs6">' +
                '           <label class="layui-form-label layui-form-required">图标字段</label>' +
                '           <div class="layui-input-block">' +
                '               <select class="layui-select" name="addition[icon_field]" id="iconField">' +
                '                   <option value="">请选择字段</option>' +
                '               </select>' +
                '           </div>' +
                '       </div>' +
                '    </div>'
            ;
            $("#setData").html(layui.laytpl(xmSelectSetDataTable).render(editData));
            layui.form.render('select');
            layui.layTpForm.render("#setData");
        } else {

        }
    }

    function selectDataFromTable(table_id_param, editData) {
        if (typeof editData === "undefined") {
            editData = {
                addition: {
                    title_field: "",
                    sub_title_field: "",
                    icon_field: ""
                }
            }
        }
        facade.ajax({
            path: "/plugin/core/autocreate.curd/getFieldList",
            params: {
                search_param: {
                    table_id: {
                        value: table_id_param,
                        condition: "="
                    }
                }
            },
            successAlert: false
        }).then(function (res) {
            if (res.code === 0) {
                $("#titleField").html('<option value="">请选择字段</option>');
                $("#subTitleField").html('<option value="">请选择字段</option>');
                $("#iconField").html('<option value="">请选择字段</option>');
                let key;
                let data = res.data.data;
                for (key in res.data.data) {
                    if (parseInt(editData.addition.title_field) === data[key]["id"]) {
                        $("#titleField").append("<option value='" + data[key]["id"] + "' selected='selected'>" + data[key]["field"] + "</option>");
                    } else {
                        $("#titleField").append("<option value='" + data[key]["id"] + "'>" + data[key]["field"] + "</option>");
                    }
                    if (parseInt(editData.addition.sub_title_field) === data[key]["id"]) {
                        $("#subTitleField").append("<option value='" + data[key]["id"] + "' selected='selected'>" + data[key]["field"] + "</option>");
                    } else {
                        $("#subTitleField").append("<option value='" + data[key]["id"] + "'>" + data[key]["field"] + "</option>");
                    }
                    if (parseInt(editData.addition.icon_field) === data[key]["id"]) {
                        $("#iconField").append("<option value='" + data[key]["id"] + "' selected='selected'>" + data[key]["field"] + "</option>");
                    } else {
                        $("#iconField").append("<option value='" + data[key]["id"] + "'>" + data[key]["field"] + "</option>");
                    }
                }
                layui.form.render('select');
            }
        });
    }

    function linkageField(table_id_param, editData) {
        facade.ajax({
            path: "/plugin/core/autocreate.curd/getFieldList",
            params: {
                search_param: {
                    table_id: {
                        value: table_id_param,
                        condition: "="
                    }
                }
            },
            successAlert: false
        }).then(function (res) {
            if (res.code === 0) {
                $("#leftLinkageField").html('<option value="">请选择字段，不选表示当前字段为联动下拉框中第一个下拉框</option>');
                $("#rightLinkageField").html('<option value="">请选择字段，不选表示当前字段为联动下拉框中最后一个下拉框</option>');
                let key;
                let data = res.data.data;
                for (key in res.data.data) {
                    if (parseInt(editData.addition.left_linkage_field) === data[key]["id"]) {
                        $("#leftLinkageField").append("<option value='" + data[key]["id"] + "' selected='selected'>" + data[key]["field"] + "</option>");
                    } else {
                        $("#leftLinkageField").append("<option value='" + data[key]["id"] + "'>" + data[key]["field"] + "</option>");
                    }
                    if (parseInt(editData.addition.right_linkage_field) === data[key]["id"]) {
                        $("#rightLinkageField").append("<option value='" + data[key]["id"] + "' selected='selected'>" + data[key]["field"] + "</option>");
                    } else {
                        $("#rightLinkageField").append("<option value='" + data[key]["id"] + "'>" + data[key]["field"] + "</option>");
                    }
                }
                layui.form.render('select');
            }
        });
    }

    function selectLinkageSearchTable(table_id_param, editData) {
        if (typeof editData === "undefined") {
            editData = {
                addition: {
                    title_field: "",
                    sub_title_field: "",
                    icon_field: ""
                }
            }
        }
        facade.ajax({
            path: "/plugin/core/autocreate.curd/getFieldList",
            params: {
                search_param: {
                    table_id: {
                        value: table_id_param,
                        condition: "="
                    }
                }
            },
            successAlert: false
        }).then(function (res) {
            if (res.code === 0) {
                $("#linkageTitleField").html('<option value="">请选择字段</option>');
                $("#linkageSubTitleField").html('<option value="">请选择字段</option>');
                $("#linkageIconField").html('<option value="">请选择字段</option>');
                let key;
                let data = res.data.data;
                for (key in res.data.data) {
                    if (parseInt(editData.addition.title_field) === data[key]["id"]) {
                        $("#linkageTitleField").append("<option value='" + data[key]["id"] + "' selected='selected'>" + data[key]["field"] + "</option>");
                    } else {
                        $("#linkageTitleField").append("<option value='" + data[key]["id"] + "'>" + data[key]["field"] + "</option>");
                    }
                    if (parseInt(editData.addition.sub_title_field) === data[key]["id"]) {
                        $("#linkageSubTitleField").append("<option value='" + data[key]["id"] + "' selected='selected'>" + data[key]["field"] + "</option>");
                    } else {
                        $("#linkageSubTitleField").append("<option value='" + data[key]["id"] + "'>" + data[key]["field"] + "</option>");
                    }
                    if (parseInt(editData.addition.icon_field) === data[key]["id"]) {
                        $("#linkageIconField").append("<option value='" + data[key]["id"] + "' selected='selected'>" + data[key]["field"] + "</option>");
                    } else {
                        $("#linkageIconField").append("<option value='" + data[key]["id"] + "'>" + data[key]["field"] + "</option>");
                    }
                }
                layui.form.render('select');
            }
        });
    }

    function getTreeData(data) {
        let key;
        for (key in data) {
            data[key].spread = true;
            if (data[key].children != null && data[key].children.length > 0) {
                data[key].children = getTreeData(data[key].children);
            }
        }
        return data;
    }

    let formType;
    window.formTypeChangePrivate = function (formTypeParam, editData) {
        formType = formTypeParam;
        if (typeof editData === "undefined") {
            editData = {
                addition: {
                    max: "",
                    default: "",
                    close_value: "",
                    close_text: "",
                    open_value: "",
                    open_text: "",
                    group_name: "",
                    left_linkage_field: "",
                    right_linkage_field: "",
                }
            };
        }
        //定义没有附加设置的表单元素数组
        let noHtmlArr = ["plugin_core_user_id", "password", "textarea"];
        //定义有多个选项的表单元素数组
        let optionsArr = ["select", "radio", "checkbox"];
        //定义有多个选项的表单元素Html
        let optionsTemplate =
            '<table class="layui-table">' +
            '<thead>' +
            '<tr>' +
            '<th>待选项的值</th>' +
            '<th>待选项的文本</th>' +
            '<th>默认选中</th>' +
            '<th>删除</th>' +
            '</tr>' +
            '</thead>' +
            '<tbody>' +
            '{{# let key; }}' +
            '{{# for(key in d.addition.value){ }}' +
            '<tr>' +
            '<td align="right">' +
            '<input type="text" class="layui-input" name="addition[value][]" value="{{d.addition.value[key]}}" />' +
            '</td>' +
            '<td>' +
            '<input type="text" class="layui-input" name="addition[text][]" value="{{d.addition.text[key]}}" />' +
            '</td>' +
            '<td>' +
            '<input {{# if(d.form_type === "checkbox"){ }}type="checkbox" {{# if(d.addition.value[key] === d.addition.default[key]){ }}checked="checked"{{# } }}{{# }else{ }}type="radio" {{# if(d.addition.value[key] === d.addition.default){ }}checked="checked"{{# } }}{{# } }} name="addition[default][]" lay-skin="primary" /> ' +
            '</td>' +
            '<td>' +
            '<a class="layui-btn layui-btn-primary layui-btn-sm layui-icon layui-icon-delete del-item"></a>' +
            '</td>' +
            '</tr>' +
            '{{# } }}' +
            '<tr>' +
            '<td colspan="4">' +
            '<a class="layui-btn layui-btn-primary layui-btn-sm layui-icon layui-icon-add-1 add-item">追加选项</a>' +
            '</td>' +
            '</tr>' +
            '</tbody>' +
            '</table>'
        ;
        //定义有附加设置的表单元素数组具体的附加设置的html
        let inputTemplate =
            '<table class="layui-table">' +
            '<tbody>' +
            '<tr>' +
            '<td align="right">输入验证</td>' +
            '<td>' +
            '   <select name="addition[verify]">' +
            '       <option value="">不限制</option>' +
            '       <option value="email" {{# if(d.addition.verify === "email"){ }}selected="selected"{{# } }}>Email</option>' +
            '       <option value="phone" {{# if(d.addition.verify === "phone"){ }}selected="selected"{{# } }}>手机号码</option>' +
            '       <option value="number" {{# if(d.addition.verify === "number"){ }}selected="selected"{{# } }}>数字</option>' +
            '       <option value="url" {{# if(d.addition.verify === "url"){ }}selected="selected"{{# } }}>链接</option>' +
            '       <option value="identity" {{# if(d.addition.verify === "identity"){ }}selected="selected"{{# } }}>身份证</option>' +
            '   </select>' +
            '</td>' +
            '</tr>' +
            '</tbody>' +
            '</table>';
        let switchTemplate =
            '<table class="layui-table">' +
            '<tbody>' +
            '<tr>' +
            '<td align="right">' +
            '关闭状态的值' +
            '</td>' +
            '<td>' +
            '<input type="text" class="layui-input" name="addition[close_value]" value="{{d.addition.close_value}}" />' +
            '</td>' +
            '</tr>' +
            '<tr>' +
            '<td align="right">' +
            '关闭状态的文本' +
            '</td>' +
            '<td>' +
            '<input type="text" class="layui-input" name="addition[close_text]" value="{{d.addition.close_text}}" />' +
            '</td>' +
            '</tr>' +
            '<tr>' +
            '<td align="right">' +
            '打开状态的值' +
            '</td>' +
            '<td>' +
            '<input type="text" class="layui-input" name="addition[open_value]" value="{{d.addition.open_value}}" />' +
            '</td>' +
            '</tr>' +
            '<tr>' +
            '<td align="right">' +
            '打开状态的文本' +
            '</td>' +
            '<td>' +
            '<input type="text" class="layui-input" name="addition[open_text]" value="{{d.addition.open_text}}" />' +
            '</td>' +
            '</tr>' +
            '</tbody>' +
            '</table>';
        let xm_selectTemplate =
            '<div class="layui-card">' +
            '  <div class="layui-card-header">基础设置</div>' +
            '  <div class="layui-card-body">' +
            '    <div class="layui-row margin-bottom6">' +
            '       <div class="layui-col-lg6 layui-col-md6 layui-col-sm6 layui-col-xs6">' +
            '           <label class="layui-form-label layui-form-required">单选还是多选</label>' +
            '           <div class="layui-input-block">' +
            '               <select class="layui-select" name="addition[single_multi_type]">' +
            '                   <option value="single" {{# if(d.addition.single_multi_type === "single"){ }}selected="selected"{{# } }}>单选</option>' +
            '                   <option value="multi" {{# if(d.addition.single_multi_type === "multi"){ }}selected="selected"{{# } }}>多选</option>' +
            '               </select>' +
            '           </div>' +
            '       </div>' +
            '       <div class="layui-col-lg6 layui-col-md6 layui-col-sm6 layui-col-xs6">' +
            '           <label class="layui-form-label layui-form-required" title="默认不限制，仅多选有效">最多可选个数</label>' +
            '           <div class="layui-input-block">' +
            '               <input type="text" class="layui-input" name="addition[max]" value="{{ d.addition.max }}" placeholder="默认不限制，仅多选有效" />' +
            '           </div>' +
            '       </div>' +
            '    </div>' +
            '    <div class="layui-row margin-bottom6">' +
            '       <div class="layui-col-lg6 layui-col-md6 layui-col-sm6 layui-col-xs6">' +
            '           <label class="layui-form-label layui-form-required">下拉方向</label>' +
            '           <div class="layui-input-block">' +
            '               <select class="layui-select" name="addition[direction]">' +
            '                   <option value="">自动</option>' +
            '                   <option value="up" {{# if(d.addition.direction === "up"){ }}selected="selected"{{# } }}>向上</option>' +
            '                   <option value="down" {{# if(d.addition.direction === "down"){ }}selected="selected"{{# } }}>向下</option>' +
            '               </select>' +
            '           </div>' +
            '       </div>' +
            '       <div class="layui-col-lg6 layui-col-md6 layui-col-sm6 layui-col-xs6">' +
            '           <label class="layui-form-label layui-form-required">数据来源方式</label>' +
            '           <div class="layui-input-block">' +
            '               <select class="layui-select" name="addition[data_from_type]" lay-filter="data-from">' +
            '                   <option value="">请选择数据来源</option>' +
            '                   <option value="data" {{# if(d.addition.data_from_type === "data"){ }}selected="selected"{{# } }}>自定义</option>' +
            '                   <option value="table" {{# if(d.addition.data_from_type === "table"){ }}selected="selected"{{# } }}>数据表</option>' +
            '               </select>' +
            '           </div>' +
            '       </div>' +
            '    </div>' +
            '  </div>' +
            '  <div class="layui-card-header">数据设置</div>' +
            '  <div class="layui-card-body" id="setData">' +
            '   请选择数据来源方式' +
            '  </div>' +
            '</div>'
        ;

        let linkage_selectTemplate =
            '<div class="layui-card">' +
            '  <div class="layui-card-header">基础设置</div>' +
            '  <div class="layui-card-body">' +
            '    <div class="layui-row margin-bottom6">' +
            '       <div>' +
            '           <label class="layui-form-label layui-form-required">分组名</label>' +
            '           <div class="layui-input-block">' +
            '               <input type="text" class="layui-input" name="addition[group_name]" placeholder="分组名，例：地区设置。相同分组名在同一个表单item中" value="{{d.addition.group_name}}" />' +
            '           </div>' +
            '       </div>' +
            '    </div>' +
            '    <div class="layui-row margin-bottom6">' +
            '       <div>' +
            '           <label class="layui-form-label layui-form-required">左关联字段</label>' +
            '           <div class="layui-input-block">' +
            '               <select class="layui-select" name="addition[left_linkage_field]" id="leftLinkageField">' +
            '                   <option value="">请选择字段，不选表示当前字段为联动下拉框中第一个下拉框</option>' +
            '               </select>' +
            '           </div>' +
            '       </div>' +
            '    </div>' +
            '    <div class="layui-row margin-bottom6">' +
            '       <div>' +
            '           <label class="layui-form-label layui-form-required">右关联字段</label>' +
            '           <div class="layui-input-block">' +
            '               <select class="layui-select" name="addition[right_linkage_field]" id="rightLinkageField">' +
            '                   <option value="">请选择字段，不选表示当前字段为联动下拉框中最后一个下拉框</option>' +
            '               </select>' +
            '           </div>' +
            '       </div>' +
            '    </div>' +
            '  </div>' +
            '  <div class="layui-card-header">下拉搜索设置</div>' +
            '  <div class="layui-card-body">' +
            '    <div class="layui-row margin-bottom6">' +
            '       <div class="layui-col-lg6 layui-col-md6 layui-col-sm6 layui-col-xs6">' +
            '           <label class="layui-form-label layui-form-required">搜索的表名</label>' +
            '           <div class="layui-input-block">' +
            '           <select class="layui-select" name="addition[table_id]" lay-filter="linkage-select-table"' +
            '                data-source="/plugin/core/autocreate.curd.table/index"' +
            '                data-showField="table"\n' +
            '                data-placeholder="请选择数据表"\n' +
            '                data-selected="{{ d.addition.table_id }}"\n' +
            '           ></select>' +
            '           </div>' +
            '       </div>' +
            '       <div class="layui-col-lg6 layui-col-md6 layui-col-sm6 layui-col-xs6">' +
            '           <label class="layui-form-label layui-form-required">主标题字段</label>' +
            '           <div class="layui-input-block">' +
            '               <select class="layui-select" name="addition[title_field]" id="linkageTitleField">' +
            '                   <option value="">请选择字段（同时用于搜索）</option>' +
            '               </select>' +
            '           </div>' +
            '       </div>' +
            '    </div>' +
            '    <div class="layui-row margin-bottom6">' +
            '       <div class="layui-col-lg6 layui-col-md6 layui-col-sm6 layui-col-xs6">' +
            '           <label class="layui-form-label layui-form-required">副标题字段</label>' +
            '           <div class="layui-input-block">' +
            '               <select class="layui-select" name="addition[sub_title_field]" id="linkageSubTitleField">' +
            '                   <option value="">请选择数据表</option>' +
            '               </select>' +
            '           </div>' +
            '       </div>' +
            '       <div class="layui-col-lg6 layui-col-md6 layui-col-sm6 layui-col-xs6">' +
            '           <label class="layui-form-label layui-form-required">图标字段</label>' +
            '           <div class="layui-input-block">' +
            '               <select class="layui-select" name="addition[icon_field]" id="linkageIconField">' +
            '                   <option value="">请选择字段</option>' +
            '               </select>' +
            '           </div>' +
            '       </div>' +
            '    </div>' +
            '  </div>' +
            '</div>'
        ;

        let laydateTemplate =
            '<table class="layui-table">' +
            '<tbody>' +
            '<tr>' +
            '<td align="right">时间格式</td>' +
            '<td>' +
            '   <select name="addition[date_type]">' +
            '       <option value="datetime" {{# if(d.addition.date_type === "datetime"){ }}selected="selected"{{# } }}>年-月-日 时:分:秒</option>' +
            '       <option value="date" {{# if(d.addition.date_type === "date"){ }}selected="selected"{{# } }}>年-月-日</option>' +
            '       <option value="time" {{# if(d.addition.date_type === "time"){ }}selected="selected"{{# } }}>时:分:秒</option>' +
            '       <option value="month" {{# if(d.addition.date_type === "month"){ }}selected="selected"{{# } }}>年-月</option>' +
            '       <option value="year" {{# if(d.addition.date_type === "year"){ }}selected="selected"{{# } }}>年</option>' +
            '   </select>' +
            '</td>' +
            '</tr>' +
            '</tbody>' +
            '</table>'
        ;

        let color_pickerTemplate =
            '<div class="layui-card">' +
            '  <div class="layui-card-header">基础设置</div>' +
            '  <div class="layui-card-body">' +
            '    <div class="layui-row margin-bottom6">' +
            '       <div>' +
            '           <label class="layui-form-label layui-form-required">默认颜色</label>' +
            '           <div class="layui-input-block colorPicker"' +
            '                data-name="addition[color]"' +
            '                data-id="addition_color"' +
            '           ></div>' +
            '       </div>' +
            '    </div>' +
            '    <div class="layui-row margin-bottom6">' +
            '       <div class="layui-col-lg6 layui-col-md6 layui-col-sm6 layui-col-xs6">' +
            '           <label class="layui-form-label layui-form-required">颜色格式</label>' +
            '           <div class="layui-input-block">' +
            '               <select class="layui-select" name="addition[format]" id="leftLinkageField">' +
            '                   <option value="hex">hex</option>' +
            '                   <option value="rgb">rgb</option>' +
            '               </select>' +
            '           </div>' +
            '       </div>' +
            '       <div class="layui-col-lg6 layui-col-md6 layui-col-sm6 layui-col-xs6">' +
            '           <label class="layui-form-label layui-form-required">开启透明度</label>' +
            '           <div class="layui-input-block">' +
            '               <input type="checkbox" name="addition[alpha]" value="1" title="开启">' +
            '           </div>' +
            '       </div>' +
            '    </div>' +
            '  </div>' +
            '  <div class="layui-card-header">预定义颜色设置</div>' +
            '  <div class="layui-card-body">' +
            '    <div class="layui-row margin-bottom6">' +
            '       <div>' +
            '           <label class="layui-form-label layui-form-required">开启预定义</label>' +
            '           <div class="layui-input-block">' +
            '               <input type="checkbox" name="addition[predefine]" value="1" title="开启">' +
            '           </div>' +
            '       </div>' +
            '    </div>' +
            '    <div class="layui-row margin-bottom6">' +
            '       <div>' +
            '           <label class="layui-form-label layui-form-required">待选颜色</label>' +
            '           <table class="layui-table">' +
            '               <thead>' +
            '                   <tr>' +
            '                       <th>颜色值</th>' +
            '                       <th>删除</th>' +
            '                   </tr>' +
            '               </thead>' +
            '               <tbody>' +
            '               {{# let key; }}' +
            '               {{# for(key in d.addition.colors){ }}' +
            '                   <tr>' +
            '                       <td align="right">' +
            '                           <div class="colorPicker"' +
            '                                data-name="addition[colors][]"' +
            '                                data-id="addition_colors_{{key}}"' +
            '                           ></div>' +
            '                       </td>' +
            '                       <td>' +
            '                           <a class="layui-btn layui-btn-primary layui-btn-sm layui-icon layui-icon-delete del-item"></a>' +
            '                       </td>' +
            '                   </tr>' +
            '               {{# } }}' +
            '                   <tr>' +
            '                       <td colspan="4">' +
            '                           <a class="layui-btn layui-btn-primary layui-btn-sm layui-icon layui-icon-add-1 add-color-picker">追加选项</a>' +
            '                       </td>' +
            '                   </tr>' +
            '               </tbody>' +
            '           </table>' +
            '       </div>' +
            '    </div>' +
            '  </div>' +
            '</div>'
        ;

        if (noHtmlArr.indexOf(formType) !== -1) {
            $("#addition").html("<div style=\"padding: 9px 5px;\">无</div>");
        } else if (optionsArr.indexOf(formType) !== -1) {
            $("#addition").html(layui.laytpl(optionsTemplate).render(editData));
            layui.form.render();
        } else {
            $("#addition").html(layui.laytpl(eval(formType + "Template")).render(editData));
            layui.form.render();
            layui.layTpForm.render("#addition");
        }
    };

    window.formTypeChange = function (params) {
        let formType = params.arr[0].value;
        formTypeChangePrivate(formType);
    };
});
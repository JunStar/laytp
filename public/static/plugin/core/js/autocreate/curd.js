layui.use(["layTp"], function () {
    const
        $ = layui.$
        , tree = layui.layTpTree
        , facade = layui.facade
        , funController = {}
    ;

    let nowTableId = "";

    defaultHeightPopupDiv = "85%";

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

    function selectDataFrom(value, editData) {
        console.log(editData);
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
                '       <div class="layui-inline layui-col-lg5 layui-col-md5 layui-col-sm5 layui-col-xs5">' +
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
                '       <div class="layui-inline layui-col-lg5 layui-col-md5 layui-col-sm5 layui-col-xs5">' +
                '           <label class="layui-form-label layui-form-required" title="默认不限制，仅多选有效">主标题字段</label>' +
                '           <div class="layui-input-block">' +
                '               <select class="layui-select" name="addition[title_field]" id="titleField">' +
                '                   <option value="">请选择字段</option>' +
                '               </select>' +
                '           </div>' +
                '       </div>' +
                '    </div>' +
                '    <div class="layui-row margin-bottom6">' +
                '       <div class="layui-inline layui-col-lg5 layui-col-md5 layui-col-sm5 layui-col-xs5">' +
                '           <label class="layui-form-label layui-form-required">副标题字段</label>' +
                '           <div class="layui-input-block">' +
                '               <select class="layui-select" name="addition[sub_title_field]" id="subTitleField">' +
                '                   <option value="">请选择字段</option>' +
                '               </select>' +
                '           </div>' +
                '       </div>' +
                '       <div class="layui-inline layui-col-lg5 layui-col-md5 layui-col-sm5 layui-col-xs5">' +
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

    function selectDataFromTable(table_id, editData) {
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
                        value: table_id,
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

    function linkageField(table_id, editData) {
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
                        value: table_id,
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
        console.log(editData);
        if (typeof editData === "undefined") {
            editData = {
                addition: {
                    max: "",
                    default: "",
                    close_value: "",
                    close_text: "",
                    open_value: "",
                    open_text: "",
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
            '</table>';
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
            '       <div class="layui-inline layui-col-lg5 layui-col-md5 layui-col-sm5 layui-col-xs5">' +
            '           <label class="layui-form-label layui-form-required">单选还是多选</label>' +
            '           <div class="layui-input-block">' +
            '               <select class="layui-select" name="addition[single_multi_type]">' +
            '                   <option value="single" {{# if(d.addition.single_multi_type === "single"){ }}selected="selected"{{# } }}>单选</option>' +
            '                   <option value="multi" {{# if(d.addition.single_multi_type === "multi"){ }}selected="selected"{{# } }}>多选</option>' +
            '               </select>' +
            '           </div>' +
            '       </div>' +
            '       <div class="layui-inline layui-col-lg5 layui-col-md5 layui-col-sm5 layui-col-xs5">' +
            '           <label class="layui-form-label layui-form-required" title="默认不限制，仅多选有效">最多可选个数</label>' +
            '           <div class="layui-input-block">' +
            '               <input type="text" class="layui-input" name="addition[max]" value="{{ d.addition.max }}" placeholder="默认不限制，仅多选有效" />' +
            '           </div>' +
            '       </div>' +
            '    </div>' +
            '    <div class="layui-row margin-bottom6">' +
            '       <div class="layui-inline layui-col-lg5 layui-col-md5 layui-col-sm5 layui-col-xs5">' +
            '           <label class="layui-form-label layui-form-required">下拉方向</label>' +
            '           <div class="layui-input-block">' +
            '               <select class="layui-select" name="addition[direction]">' +
            '                   <option value="">自动</option>' +
            '                   <option value="up" {{# if(d.addition.direction === "up"){ }}selected="selected"{{# } }}>向上</option>' +
            '                   <option value="down" {{# if(d.addition.direction === "down"){ }}selected="selected"{{# } }}>向下</option>' +
            '               </select>' +
            '           </div>' +
            '       </div>' +
            '       <div class="layui-inline layui-col-lg5 layui-col-md5 layui-col-sm5 layui-col-xs5">' +
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
            '<table class="layui-table">' +
            '<tbody>' +
            '<tr>' +
            '<td align="right">' +
            '分组名' +
            '</td>' +
            '<td>' +
            '<input type="text" class="layui-input" name="addition[close_value]" value="{{d.addition.close_value}}" lay-verify="required"\n' +
            '                                           lay-verType="tips" autocomplete="off" placeholder="分组名，例:地区设置。相同分组名在同一个表单item中" />' +
            '</td>' +
            '</tr>' +
            '<tr>' +
            '<td align="right">' +
            '搜索的表名' +
            '</td>' +
            '<td>' +
            '           <select class="layui-select" name="addition[table_id]" lay-filter="linkage-select-table"' +
            '                data-source="/plugin/core/autocreate.curd.table/index"' +
            '                data-showField="table"\n' +
            '                data-placeholder="请选择数据表"\n' +
            '                data-selected="{{ d.addition.table_id }}"\n' +
            '           ></select>' +
            '</td>' +
            '</tr>' +
            '<tr>' +
            '<td align="right">' +
            '左关联字段' +
            '</td>' +
            '<td>' +
            '<select class="layui-select" name="addition[left_linkage_field]" id="leftLinkageField">' +
            '   <option value="">请选择字段</option>' +
            '</select>' +
            '</td>' +
            '</tr>' +
            '<tr>' +
            '<td align="right">' +
            '右联动字段' +
            '</td>' +
            '<td>' +
            '<select class="layui-select" name="addition[right_linkage_field]" id="rightLinkageField">' +
            '   <option value="">请选择字段</option>' +
            '</select>' +
            '</td>' +
            '</tr>' +
            '</tbody>' +
            '</table>';

        if (noHtmlArr.indexOf(formType) !== -1) {
            $("#addition").html("<div style=\"padding: 9px 5px;\">无</div>");
        } else if (optionsArr.indexOf(formType) !== -1) {
            $("#addition").html(layui.laytpl(optionsTemplate).render(editData));
            layui.form.render();
            layui.layTpForm.render("#addition");
        } else {
            $("#addition").html(layui.laytpl(eval(formType + "Template")).render(editData));
            layui.form.render();
            layui.layTpForm.render("#addition");
        }
    };

    window.formTypeChange = function (params) {
        let formType = params.arr[0].value;
        formTypeChangePrivate(formType);
    }
});
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
                        // var element =
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
                facade.popupDiv({
                    title: "添加字段",
                    path: "/plugin/core/autocreate.curd.field/edit",
                    data: obj.data,
                    callback: function () {
                        $("[lay-filter=laytp-search-form]").click();
                    }
                });
            }
            // if (defaultTableTool.indexOf(obj.event) !== -1) {
            //     layTp.tableTool(obj);
            // } else {
            //     // //自定义按钮点击事件
            //     // switch(obj.event){
            //     // //自定义按钮点击事件
            //     // case "":
            //     //
            //     //     break;
            //     // }
            // }
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
            console.log("添加表");
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
            clickObj.parent().parent().before('<tr>' +
                '<td align="right">' +
                '<input type="text" class="layui-input" name="additional[]" />' +
                '</td>' +
                '<td>' +
                '<input type="text" class="layui-input" name="additional[]" />' +
                '</td>' +
                '<td>' +
                '<a class="layui-btn layui-btn-primary layui-btn-sm layui-icon layui-icon-delete del-item"></a>' +
                '</td>' +
                '</tr>');
        });

        $(document).off("click", ".del-item").on("click", ".del-item", function () {
            let clickObj = $(this);
            clickObj.parent().parent().remove();
        });

        layui.form.on('select(data-from)', function(data){
            if(data.value === "table"){
                let xmSelectSetDataTable =
                    '    <div class="layui-row margin-bottom6">' +
                    '       <div class="layui-inline layui-col-lg5 layui-col-md5 layui-col-sm5 layui-col-xs5">' +
                    '           <label class="layui-form-label layui-form-required">数据表</label>' +
                    '           <div class="layui-input-block">' +
                    '           <select class="layui-select" lay-filter="select-table"' +
                    '                data-source="/plugin/core/autocreate.curd.table/index"' +
                    '                data-showField="table"\n' +
                    '                data-placeholder="请选择数据表"\n' +
                    '           ></select>' +
                    '           </div>' +
                    '       </div>' +
                    '       <div class="layui-inline layui-col-lg5 layui-col-md5 layui-col-sm5 layui-col-xs5">' +
                    '           <label class="layui-form-label layui-form-required" title="默认不限制，仅多选有效">主标题字段</label>' +
                    '           <div class="layui-input-block">' +
                    '               <select class="layui-select" id="titleField">' +
                    '                   <option value="">请选择字段</option>' +
                    '               </select>' +
                    '           </div>' +
                    '       </div>' +
                    '    </div>' +
                    '    <div class="layui-row margin-bottom6">' +
                    '       <div class="layui-inline layui-col-lg5 layui-col-md5 layui-col-sm5 layui-col-xs5">' +
                    '           <label class="layui-form-label layui-form-required">副标题字段</label>' +
                    '           <div class="layui-input-block">' +
                    '               <select class="layui-select" id="subTitleField">' +
                    '                   <option value="">请选择字段</option>' +
                    '               </select>' +
                    '           </div>' +
                    '       </div>' +
                    '       <div class="layui-inline layui-col-lg5 layui-col-md5 layui-col-sm5 layui-col-xs5">' +
                    '           <label class="layui-form-label layui-form-required">图标字段</label>' +
                    '           <div class="layui-input-block">' +
                    '               <select class="layui-select" id="iconField">' +
                    '                   <option value="">请选择字段</option>' +
                    '               </select>' +
                    '           </div>' +
                    '       </div>' +
                    '    </div>'
                ;
                $("#setData").html(xmSelectSetDataTable);
                layui.form.render('select');
                layui.layTpForm.render("#setData");
            }else{

            }
        });

        layui.form.on('select(select-table)', function (data) {
            facade.ajax({
                path: "/plugin/core/autocreate.curd/getFieldList",
                params: {
                    search_param: {
                        table_id: {
                            value: data.value,
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
                        $("#titleField").append("<option value='" + data[key]["id"] + "'>" + data[key]["field"] + "</option>");
                        $("#subTitleField").append("<option value='" + data[key]["id"] + "'>" + data[key]["field"] + "</option>");
                        $("#iconField").append("<option value='" + data[key]["id"] + "'>" + data[key]["field"] + "</option>");
                    }
                    layui.form.render('select');
                }
            });
        });
    });

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

    window.formTypeChange = function (params) {
        let formType = params.arr[0].value;
        //定义没有附加设置的表单元素数组
        let noHtmlArr = ["plugin_core_user_id", "password", "textarea"];
        //定义有多个选项的表单元素数组
        let optionsArr = ["select", "radio", "checkbox"];
        //定义有多个选项的表单元素Html
        let optionsHtml =
            '<table class="layui-table">' +
            '<thead>' +
            '<tr>' +
            '<th>待选项的值</th>' +
            '<th>待选项的文本</th>' +
            '<th>操作</th>' +
            '</tr>' +
            '</thead>' +
            '<tbody>' +
            '<tr>' +
            '<td align="right">' +
            '<input type="text" class="layui-input" name="additional[]" />' +
            '</td>' +
            '<td>' +
            '<input type="text" class="layui-input" name="additional[]" />' +
            '</td>' +
            '<td>' +
            '<a class="layui-btn layui-btn-primary layui-btn-sm layui-icon layui-icon-delete del-item"></a>' +
            '</td>' +
            '</tr>' +
            '<tr>' +
            '<td colspan="3">' +
            '<a class="layui-btn layui-btn-primary layui-btn-sm layui-icon layui-icon-add-1 add-item">追加选项</a>' +
            '</td>' +
            '</tr>' +
            '<tr>' +
            '<td align="right">' +
            '默认选中项的值，多个以英文逗号隔开' +
            '</td>' +
            '<td colspan="2">' +
            '<input type="text" class="layui-input" name="additional[]" />' +
            '</td>' +
            '</tr>' +
            '</tbody>' +
            '</table>';
        //定义有附加设置的表单元素数组具体的附加设置的html
        let inputHtml =
            '<table class="layui-table">' +
            '<tbody>' +
            '<tr>' +
            '<td align="right">输入验证</td>' +
            '<td><select name="additional[]">' +
            '<option value="">不限制</option>' +
            '<option value="email">Email</option>' +
            '<option value="phone">手机号码</option>' +
            '<option value="number">数字</option>' +
            '<option value="url">链接</option>' +
            '<option value="identity">身份证</option>' +
            '</select>' +
            '</td>' +
            '</tr>' +
            '</tbody>' +
            '</table>';
        let switchHtml =
            '<table class="layui-table">' +
            '<tbody>' +
            '<tr>' +
            '<td align="right">' +
            '关闭状态的值' +
            '</td>' +
            '<td>' +
            '<input type="text" class="layui-input" name="additional[]" />' +
            '</td>' +
            '</tr>' +
            '<tr>' +
            '<td align="right">' +
            '关闭状态的文本' +
            '</td>' +
            '<td>' +
            '<input type="text" class="layui-input" name="additional[]" />' +
            '</td>' +
            '</tr>' +
            '<tr>' +
            '<td align="right">' +
            '打开状态的值' +
            '</td>' +
            '<td>' +
            '<input type="text" class="layui-input" name="additional[]" />' +
            '</td>' +
            '</tr>' +
            '<tr>' +
            '<td align="right">' +
            '打开状态的文本' +
            '</td>' +
            '<td>' +
            '<input type="text" class="layui-input" name="additional[]" />' +
            '</td>' +
            '</tr>' +
            '</tbody>' +
            '</table>';
        let xm_selectHtml =
            '<div class="layui-card">' +
            '  <div class="layui-card-header">基础设置</div>' +
            '  <div class="layui-card-body">' +
            '    <div class="layui-row margin-bottom6">' +
            '       <div class="layui-inline layui-col-lg5 layui-col-md5 layui-col-sm5 layui-col-xs5">' +
            '           <label class="layui-form-label layui-form-required">单选还是多选</label>' +
            '           <div class="layui-input-block">' +
            '               <select class="layui-select">' +
            '                   <option value="single">单选</option>' +
            '                   <option value="multi">多选</option>' +
            '               </select>' +
            '           </div>' +
            '       </div>' +
            '       <div class="layui-inline layui-col-lg5 layui-col-md5 layui-col-sm5 layui-col-xs5">' +
            '           <label class="layui-form-label layui-form-required" title="默认不限制，仅多选有效">最多可选个数</label>' +
            '           <div class="layui-input-block">' +
            '               <input type="text" class="layui-input" name="additional[]" placeholder="默认不限制，仅多选有效" />' +
            '           </div>' +
            '       </div>' +
            '    </div>' +
            '    <div class="layui-row margin-bottom6">' +
            '       <div class="layui-inline layui-col-lg5 layui-col-md5 layui-col-sm5 layui-col-xs5">' +
            '           <label class="layui-form-label layui-form-required">下拉方向</label>' +
            '           <div class="layui-input-block">' +
            '               <select class="layui-select">' +
            '                   <option value="">自动</option>' +
            '                   <option value="up">向上</option>' +
            '                   <option value="down">向下</option>' +
            '               </select>' +
            '           </div>' +
            '       </div>' +
            '       <div class="layui-inline layui-col-lg5 layui-col-md5 layui-col-sm5 layui-col-xs5">' +
            '           <label class="layui-form-label layui-form-required">数据来源方式</label>' +
            '           <div class="layui-input-block">' +
            '               <select class="layui-select" lay-filter="data-from">' +
            '                   <option value="">请选择数据来源</option>' +
            '                   <option value="data">自定义</option>' +
            '                   <option value="table">数据表</option>' +
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

        if (noHtmlArr.indexOf(formType) !== -1) {
            $("#additional").html("<div style=\"padding: 9px 5px;\">无</div>");
        } else if (optionsArr.indexOf(formType) !== -1) {
            $("#additional").html(optionsHtml);
        } else {
            $("#additional").html(eval(formType + "Html"));
            layui.form.render();
        }
    }
});
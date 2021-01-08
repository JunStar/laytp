/**
 * 选择图标组件
 * @version: 1.0
 * @Author:  JunStar
 * @Date:    2020-09-03 11:33:43
 * @Last Modified by:   JunStar
 * @Last Modified time: 2020-09-03 11:33:48
 */
layui.define(["jquery"], function (exports) {
    const MOD_NAME = "layTpIcon";

    let layTpIcon = {
        //默认需要展示的html
        renderHtml: function (options) {
            let template =
                "<div class=\"layui-input-inline\" style=\"width:70%;\">\n" +
                "    <input type=\"text\" id=\"{{d.name}}\" name=\"{{d.name}}\" value='{{d.value}}' placeholder=\"{{d.placeholder}}\" class=\"layui-input\" lay-verify=\"{{d.layVerify}}\" lay-verType=\"{{d.layVerType}}\" autocomplete=\"off\">\n" +
                "</div>\n" +
                "<div class=\"layui-inline\">\n" +
                "    <i id=\"{{d.name}}_i\" class='{{d.value}}'></i>\n" +
                "</div>\n" +
                "<div class=\"layui-inline\">\n" +
                "    <a class=\"layui-btn layui-btn-default layui-btn-sm\" id=\"select-icon-{{d.name}}\" data-parentElem=\"{{d.parentElem}}\">选择图标</a>\n" +
                "</div>";

            return layui.laytpl(template).render(options);
        },

        //选择图标弹窗的html
        chooseIconHtml: function (jsonData) {
            let template =
                "<div class=\"choose_icon\">\n" +
                "   <div>\n" +
                "       <ul class=\"list-inline\">\n" +
                "       {{#  layui.each(d.data, function(index, item){ }}\n" +
                "       <li class=\"pop-select-to-input\" data-inputValue=\"{{ item.name }}\" data-parentElem=\"{{ d.parentElem }}\">\n" +
                "           <i class=\"{{ item.name }}\"></i>\n" +
                "       </li>\n" +
                "       {{#  }); }}\n" +
                "       </ul>\n" +
                "   </div>\n" +
                "</div>"
            return layui.laytpl(template).render(jsonData);
        },

        render: function (options) {
            let tabIndex;
            //在节点后面添加需要展示的html
            $(options.el).after(layTpIcon.renderHtml(options));
            //选择图标按钮点击事件
            $(document).off("click", "#select-icon-" + options.name).on("click", "#select-icon-" + options.name, function () {
                layui.facade.loading();

                let width = "45%";
                let height = "50%";
                let left = (document.body.offsetWidth - 230 - facade.rtrim(width, "%") / 100 * document.body.offsetWidth) / 2 + 230;
                let layuiIconsHtml = "";
                let fontAwesomeIconsHtml = "";
                let parentElem = $(this).data("parentelem");

                let defAjaxArr = [];

                defAjaxArr.push(
                    $.ajax({
                        "url": apiDomain + "/static/plugin/core/data/layuiIcons.json",
                        "async": true,
                        "dataType": "json",
                        "success": function (res) {
                            res.parentElem = parentElem;
                            layuiIconsHtml = layTpIcon.chooseIconHtml(res);
                        }
                    })
                );

                defAjaxArr.push(
                    $.ajax({
                        "url": apiDomain + "/static/plugin/core/data/fontAwesomeIcons.json",
                        "async": true,
                        "dataType": "json",
                        "success": function (res) {
                            res.parentElem = parentElem;
                            fontAwesomeIconsHtml = layTpIcon.chooseIconHtml(res);
                        }
                    })
                );

                $.when.apply($, defAjaxArr).done(function () {
                    tabIndex = layer.tab({
                        area: [width, height]
                        , shade: 0.1
                        , offset: ["", left + "px"]
                        , tab: [{
                            title: "LayUI",
                            content: layuiIconsHtml
                        }, {
                            title: "font-awesome",
                            content: fontAwesomeIconsHtml
                        }]
                    });
                });
            });

            //监听点击某个图标事件，关闭弹出层，将值输入Input并把图标展示在input后面的i标签内
            $(document).off("click", ".pop-select-to-input").on("click", ".pop-select-to-input", function () {
                let value = $(this).data("inputvalue");
                let parentElem = $(this).data("parentelem");
                $("#" + options.name + "_i", parentElem).attr("class", value);
                $("#" + options.name, parentElem).val(value).focus();
                layer.close(tabIndex);
            });

            //监听图标input的input事件，修改图标Input后面的i标签class
            $(document).off("input", "#" + options.name).on("input", "#" + options.name, function () {
                let icon_val = $(this).val();
                $("#" + options.name + "_i").attr("class", icon_val);
            });
        },
    };

    //输出模块
    exports(MOD_NAME, layTpIcon);

    //注入layui组件中，供全局调用
    layui.layTpIcon = layTpIcon;

    //注入window全局对象中，供全局调用
    window.layTpIcon = layTpIcon;
}).addcss("extends/icon.css", "laytpiconcss");
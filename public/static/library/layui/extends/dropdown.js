layui.define(['jquery'], function(exports) {
    var $ = layui.jquery
    var CLS_DROPDOWN = 'layui-dropdown'
    var CLS_DROPDOWN_RIGHT = 'layui-dropdown-direright'
    var CLS_SELECT = 'layui-dropdown-select'
    var CLS_OPTION = 'layui-dropdown-option'
    var CLS_TITLE = 'layui-dropdown-title'
    var CLS_ARROW = 'layui-dropdown-arrow-up'
    var HTML_DROPDOWN = '<div class="' + CLS_DROPDOWN + '"><div>'
    var DEPTH = 0
    var INDEX = 0

    var Class = function(config) {
        this.config = $.extend({}, this.config, config)
        this.render(config)
    }
    Class.prototype.config = {
        width: 150,
        trigger: 'click'
    }
    Class.prototype.dropdownElem = ''
    Class.prototype.exists = false
    Class.prototype.depth = 0
    Class.prototype.index = 0
    Class.prototype.render = function(config) {
        var self = this
        if (typeof this.config.elem == 'string') {
            $(document).on('click', this.config.elem, event)
        } else {
            this.config.elem.click(event)
        }

        function event(e) {
            e.stopPropagation()

            if (self.dropdownElem == '') {
                INDEX += 1
                self.index = INDEX

                var dropdown = $(HTML_DROPDOWN).attr('lay-index', self.index)
                $('.' + CLS_DROPDOWN + '[lay-index="' + self.index + '"]').remove()

                dropdown.html(self.createOptionsHtml(config))
                $('body').prepend(dropdown)
                // dropdown.on('click', '.' + CLS_OPTION, function(e) {
                //     e.stopPropagation()
                //     if ($.isFunction(config.click)) {
                //         config.click($(this).attr('lay-action'), $(this), e)
                //         dropdown.hide()
                //     }
                // })
                self.dropdownElem = dropdown
                self.dropdownSelect = dropdown.find('.' + CLS_SELECT)
            }

            var dropdown = self.dropdownElem
            dropdown.css('z-index','999');

            var top = $(this).offset().top + $(this).height() + 12 - $(document).scrollTop()
            var left = $(this).offset().left
            dropdown.css({
                top: top - 10
            })
            var offsetWidth = (self.depth + 1) * self.config.width

            if (left + offsetWidth > $(window).width()) {
                dropdown
                    .addClass('layui-dropdown-right')
                    .css('left', left - dropdown.width() + $(this).width())
                self.dropdownSelect.css({ left: 'auto', right: self.config.width })
            } else {
                dropdown.removeClass('layui-dropdown-right').css('left', left)
                self.dropdownSelect.css({ right: 'auto', left: self.config.width })
            }

            $('body').one('click', function(e) {
                dropdown.css('z-index','-1');
                dropdown.stop().animate(
                    {
                        top: '-=10',
                        opacity: 0
                    },
                    250
                )
            });

            dropdown
                .show()
                .stop()
                .animate(
                    {
                        top: '+=10',
                        opacity: 1
                    },
                    250
                );
        }
    }
    Class.prototype.createOptionsHtml = function(data, depth) {
        depth = depth || 0
        var self = this
        var width = self.config.width + 'px;'
        var html =
            '<div class="' +
            CLS_SELECT +
            '" style="width:' +
            width +
            (depth > 0 ? 'left:' + width : '') +
            '">'
        if (depth == 0) {
            html += '<div class="' + CLS_ARROW + '"></div>'
        }
        let field = "";
        let field_val = "";
        let need_data = "";
        let twidth = "";
        let height = "";
        let need_refresh = "";
        layui.each(data.options, function(i, option) {
            var options = option.options || [];
            field = (typeof option.field != "undefined") ? option.field : "";
            field_val = (typeof option.field_val != "undefined") ? option.field_val : "";
            need_data = (typeof option.need_data != "undefined") ? option.need_data : "true";
            twidth = (typeof option.width != "undefined") ? option.width : "";
            height = (typeof option.height != "undefined") ? option.height : "";
            need_refresh = (typeof option.need_refresh != "undefined") ? option.need_refresh : "false";
            html +=
                '<div ' +
                    'lay-action=' + option.action + ' ' +
                    'class="' +CLS_OPTION +' batch-action"' +
                    'uri="' + option.uri +'"' +
                    'field="' + field +'"' +
                    'need_data="' + need_data +'"' +
                    'width="' + twidth +'"' +
                    'height="' + height +'"' +
                    'need_refresh="' + need_refresh +'"' +
                    'field_val="' + field_val +'"' +
                    'switch_type="' + option.switch_type +'"' +
                    'callback="' + option.callback +'"' +
                    'param=\'' + JSON.stringify(option.param) +'\'' +
                '>' +
                    '<p class="' + CLS_TITLE +' layui-elip">' +
                        '<span class="layui-icon ' + option.icon + '"></span>' + option.title +
                    '</p>' +
                    (options.length > 0
                        ? '<i class="layui-icon layui-icon-right"></i>'
                        : '');
            option.options = option.options || [];
            if (option.options.length > 0){
                html += self.createOptionsHtml(option, depth + 1);
            }
            html += '</div>';
            if (self.depth < depth) self.depth = depth
        })
        html += '</div>'
        return html
    }

    var self = {
        render: function(config) {
            new Class(config)
        }
    }
    exports('dropdown', self)
})

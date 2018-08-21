(function func_controller() {
    var controller = {};

    controller.table_render = function (where) {
        table.render({
            elem: '.layui-hide-sm'
            , url: window.location.href
            , where: where
            , method: 'POST'
            , cellMinWidth: 80 //全局定义常规单元格的最小宽度，layui 2.2.1 新增
            , page: true //开启分页
            , cols: [[ //表头
                {field: 'id', title: 'ID', sort: true, fixed: 'left'}
                , {field: 'name', title: '标题'}
                , {field: 'rule', title: '规则', sort: true}
                , {field: 'icon', title: '图标'}
                , {field: 'sort', title: '排序'}
            ]]
        });
    }

    controller.table_render();

    window.controller = controller;
})()
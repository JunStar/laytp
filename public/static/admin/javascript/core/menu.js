(function func_core_menu() {
    var core_menu = {};

    core_menu.table_render = function(url,where){
        console.log(url);
        console.log(where);
        // table.render({
        //     elem: '.layui-hide-sm'
        //     ,url:url
        //     ,where:where
        //     ,method:'POST'
        //     ,cellMinWidth: 80 //全局定义常规单元格的最小宽度，layui 2.2.1 新增
        //     ,page: true //开启分页
        //     ,cols: [[ //表头
        //         {field: 'id', title: 'ID', sort: true, fixed: 'left'}
        //         , {field: 'name', title: '标题'}
        //         , {field: 'rule', title: '规则', sort: true}
        //         , {field: 'icon', title: '图标'}
        //         , {field: 'sort', title: '排序'}
        //     ]]
        // });
    }

    core_menu.table_render(facade.url('admin/core.menu/index'));

    window.core_menu = core_menu;
})()
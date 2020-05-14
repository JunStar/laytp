layui.use(['layTp'],function() {
    const
        func_controller = {}
        ,layTp = layui.layTp
        ,$ = layui.jquery
    ;

    //批量操作下拉展示列表设置
    let batch_dropdown_list = [
        {
            action: "edit"
            ,title: "编辑"
            ,icon: "layui-icon-edit"
            ,node: module + "/" + controller + "/edit"
            ,switch_type: "popup_frame"
        }
        ,{
            action: 'del'
            ,title: '删除'
            ,icon: "layui-icon-delete"
            ,node: module + "/" + controller + "/del"
            ,switch_type: "confirm_action"
        }
    ];

    layTp.facade.dropdown_set(batch_dropdown_list,true);

    //表格渲染
    func_controller.table_render = function (where, page) {
        layui.table.render({
            elem: '.layui-hide-sm'
            , id: table_id
            , url: window.location.href
            , toolbar: '#default_toolbar'
            , where: where
            , even: true
            , method: 'GET'
            , autoSort: false
            , cellMinWidth: 180
            , page: {
                curr: page
            }
            , cols: [[
                {type:'checkbox'}
				,{field:'id',title:'ID',align:'center',width:80}
				,{field:'admin_id',title:'管理员',align:'center'}
				,{field:'title',title:'标题',align:'center'}
				,{field:'grade',title:'年级',align:'center',templet:function(d){
					return layTp.facade.formatter.status('grade',d.grade,{"1":"一年级","2":"二年级","3":"三年级"});
				}}
				,{field:'status',title:'状态',align:'center',templet:function(d){
					return layTp.facade.formatter.switch('status',d,{"open":{"value":1,"text":"打开"},"close":{"value":0,"text":"关闭"}});
				}}
				,{field:'hero',title:'英雄',align:'center',templet:function(d){
					return layTp.facade.formatter.status('hero',d.hero,["秀逗魔法师","受折磨的灵魂","船长","虚空假面","幻影刺客","谜团","全能骑士","敌法师"]);
				}}
				,{field:'hobby',title:'爱好',align:'center',templet:function(d){
					return layTp.facade.formatter.flag(d.hobby,["游泳","下棋","游戏","乒乓球","羽毛","跑步","爬山","美食"]);
				}}
				,{field:'sign',title:'标志',align:'center',templet:function(d){
					return layTp.facade.formatter.flag(d.sign,["热门","首页","顶级分类推荐","二级分类推荐","特定分类推荐","轮播图","置顶","新闻"]);
				}}
				,{field:'description',title:'描述',align:'center'}
				,{field:'category_id',title:'所属分类（单选）',align:'center'}
				,{field:'category_ids',title:'所属分类（多选）',align:'center'}
				,{field:'single_img',title:'单个图片',align:'center',templet:function(d){
					return layTp.facade.formatter.images(d.single_img);
				}}
				,{field:'multi_img',title:'多个图片',align:'center',templet:function(d){
					return layTp.facade.formatter.images(d.multi_img);
				}}
				,{field:'video',title:'视频文件地址',align:'center',templet:function(d){
					return layTp.facade.formatter.video(d.video);
				}}
				,{field:'audio',title:'音频文件地址',align:'center',templet:function(d){
					return layTp.facade.formatter.audio(d.audio);
				}}
				,{field:'file',title:'任意文件地址',align:'center',templet:function(d){
					return layTp.facade.formatter.file(d.file);
				}}
				,{field:'content',title:'文章内容',align:'center'}
				,{field:'province_id',title:'省份',align:'center'}
				,{field:'city_id',title:'城市',align:'center'}
				,{field:'area_id',title:'地区',align:'center'}
				,{field:'create_time',title:'创建时间',align:'center'}
				//,{field:'update_time',title:'更新时间',align:'center'}
				//,{field:'delete_time',title:'删除时间',align:'center'}
				,{field:'date',title:'日期',align:'center'}
				,{field:'operation',title:'操作',align:'center',toolbar:'#operation',width:100,fixed:'right'}
            ]]
        });

        //监听默认工具条
        layui.table.on('tool(default)', function(obj){
            if(default_table_tool.indexOf(obj.event) != -1){
                layTp.facade.table_tool(obj);
            }else{
                //新增的其他操作按钮在这里来写
                //switch(obj.event){
                //    case '':
                //
                //        break;
                //}
            }
        });
    }

    func_controller.table_render();

    window.func_controller = func_controller;

});
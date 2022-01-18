layui.use(['laytp'], function () {
    const funRecycleController = {};
    //静态页面地址前缀
    window.htmlPrefix = facade.compatibleHtmlPath("/admin/test/");
    //后端接口地址前缀
    window.apiPrefix  = facade.compatibleApiRoute("/admin.test/");

    //表格渲染
    funRecycleController.tableRender = function (where, page) {
        layui.table.render({
            elem: "#laytp-recycle-table"
            , id: "laytp-recycle-table"
            , url: facade.url("/admin.test/recycle")
            , toolbar: "#recycle-default-toolbar"
            , defaultToolbar: [{
                title: '刷新',
                layEvent: 'recycle-refresh',
                icon: 'layui-icon-refresh',
            }, 'filter', 'print', 'exports']
            , where: where
            , method: "GET"
            , cellMinWidth: 120
            , skin: 'line'
            , loading: false
            , page: {
                curr: page
            }
            , parseData: function (res) { //res 即为原始返回的数据
                return facade.parseTableData(res, true);
            }
            , cols: [[ //表头
                {type:'checkbox',fixed:'left'}
				,{field:'id',title:'ID',align:'center',width:80,fixed:'left'}
				,{field:'input',title:'单行文本输入框',align:'center',templet:function(d){
                        return layui.laytpl('{{=d.input}}').render({input:d.input});
                    }}
				,{field:'input_edit',title:'单行输入框（可编辑）',align:'center',templet:function(d){
                        return laytpForm.tableForm.recycleEditInput('input_edit',d,'/admin.test/setInputEdit');
                    }}
				,{field:'textarea',title:'文本域',align:'center',templet:function(d){
                    return layui.laytpl('{{=d.textarea}}').render({textarea:d.textarea});
                }}
				,{field:'hero',title:'选择英雄（单选）',align:'center',templet:function(d){
                    return laytp.tableFormatter.status('hero',d.hero,{"value":["1","2","3","4","5","6"],"text":["秀逗魔法师","德玛西亚之力","德玛西亚皇子","德邦总管赵信","蛮族之王","潘森"]});
                }}
				,{field:'status',title:'状态（开关单选）',align:'center',templet:function(d){
                    return laytpForm.tableForm.recycleSwitch("status", d, {
                        "open": {"value": 1, "text": "正常"},
                        "close": {"value": 2, "text": "锁定"}
                    });
                }}
				,{field:'hobby',title:'爱好（复选框）',align:'center',sort:true,templet:function(d){
					return laytp.tableFormatter.flag(d.hobby,{"value":["1","2","3","4","5","6"],"text":["游泳","唱歌","台球","桌游","象棋","朗诵"],"default":[]});
				}}
				,{field:'browser',title:'浏览器（单选下拉框）',align:'center',templet:function(d){
					return laytp.tableFormatter.status('browser',d.browser,{"value":["1","2","3","4","5"],"text":["Chrome","FireFox","IE","360安全浏览器","QQ浏览器"],"default":0});
				}}
				,{field:'category_id',title:'所属分类（单选）',align:'center',templet:'<div>{{# if(d.category){ }}{{d.category.name}}{{# }else{ }}-{{# } }}</div>'}
				,{field:'category_ids',title:'所属分类（多选）',align:'center'}
				,{field:'province_id',title:'省',align:'center',templet:'<div>{{# if(d.province){ }}{{d.province.short_name}}{{# }else{ }}-{{# } }}</div>'}
				,{field:'city_id',title:'市',align:'center',templet:'<div>{{# if(d.city){ }}{{d.city.short_name}}{{# }else{ }}-{{# } }}</div>'}
				,{field:'district_id',title:'区',align:'center',templet:'<div>{{# if(d.district){ }}{{d.district.short_name}}{{# }else{ }}-{{# } }}</div>'}
				,{field:'color',title:'颜色选择器',align:'center',templet:function(d){
					return laytp.tableFormatter.colorPicker(d.color);
				}}
				,{field:'img',title:'上传单个图片',align:'center',templet:function(d){
					return d.img_file ? laytp.tableFormatter.images(d.img_file.path) : "";
				}}
				,{field:'imgs',title:'上传多个图片',align:'center',templet:function(d){
					return laytp.tableFormatter.images(d.imgs_file.path);
				}}
				,{field:'video',title:'上传单个视频',align:'center',templet:function(d){
					return d.video_file ? laytp.tableFormatter.video(d.video_file.path) : "";
				}}
				,{field:'videos',title:'上传多个视频',align:'center',templet:function(d){
					return laytp.tableFormatter.video(d.videos_file.path);
				}}
				,{field:'music',title:'上传单个音频',align:'center',templet:function(d){
					return d.music_file ? laytp.tableFormatter.audio(d.music_file.path) : "";
				}}
				,{field:'musics',title:'上传多个音频',align:'center',templet:function(d){
					return laytp.tableFormatter.audio(d.musics_file.path);
				}}
				,{field:'file',title:'上传单个任意文件',align:'center',templet:function(d){
					return d.file_file ? laytp.tableFormatter.file(d.file_file.path) : "";
				}}
				,{field:'files',title:'上传多个任意文件',align:'center',templet:function(d){
					return laytp.tableFormatter.file(d.files_file.path);
				}}
				,{field:'ueditor',title:'ueditor编辑器',align:'center'}
				,{field:'create_time',title:'创建时间',align:'center'}
				,{field:'update_time',title:'更新时间',align:'center'}
				,{field:'delete_time',title:'删除时间',align:'center'}
				,{field:'sel_admin_user_id',title:'选择后台管理员ID',align:'center',templet:'<div>{{# if(d.selAdminUser){ }}{{d.selAdminUser.nickname}}{{# }else{ }}-{{# } }}</div>'}
				,{field:'meditor',title:'meditor编辑器',align:'center'}
                ,{field:'operation',title:'操作',align:'center',toolbar:'#recycle-default-bar',width:150,fixed:'right'}
            ]]
        });

        //监听数据表格顶部左侧按钮点击事件
        layui.table.on("toolbar(laytp-recycle-table)", function (obj) {
            var defaultTableToolbar = layui.context.get("defaultTableToolbar");
            if (defaultTableToolbar.indexOf(obj.event) !== -1) {
                //默认按钮点击事件
                laytp.tableToolbar(obj);
            } else {
                // //自定义按钮点击事件
                // switch(obj.event){
                // //自定义按钮点击事件
                // case "":
                //
                //     break;
                // }
            }
        });

        //监听数据表格[操作列]按钮点击事件
        layui.table.on('tool(laytp-recycle-table)', function (obj) {
            var defaultTableTool = layui.context.get("defaultTableTool");
            if (defaultTableTool.indexOf(obj.event) !== -1) {
                laytp.tableTool(obj);
            } else {
                // //自定义按钮
                // switch(obj.event){
                // //自定义按钮点击事件
                // case '':
                //
                //     break;
                // }
            }
        });
    };

    funRecycleController.tableRender();

    window.funRecycleController = funRecycleController;
});
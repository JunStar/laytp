<?php
return [
    [
        'name'=>'远程插件',
        'rule'=>'admin/dashboard/index',
        'is_menu'=>1,
        'icon'=>'layui-icon layui-icon-rate-solid',
        'delete_status'=>0,
        'children'=>[
            [
                'name'=>'一键生成',
                'rule'=>'admin/autocreate.curd/index',
                'is_menu'=>1,
                'icon'=>'layui-icon layui-icon-share',
                'delete_status'=>0,
                'children'=>[
                    [
                        'name'=>'Curd',
                        'rule'=>'admin/autocreate.curd/index',
                        'is_menu'=>1,
                        'icon'=>'layui-icon layui-icon-star-fill',
                        'delete_status'=>1,
                        'children'=>[
                            ['name'=>'查看', 'rule'=>'admin/autocreate.curd/index', 'is_menu'=>0,'delete_status'=>1],
                            ['name'=>'导入', 'rule'=>'admin/autocreate.curd/import', 'is_menu'=>0,'delete_status'=>1],
                            ['name'=>'删除', 'rule'=>'admin/autocreate.curd/del', 'is_menu'=>0,'delete_status'=>1]
                        ]
                    ],
                ]
            ],
        ]
    ]
];
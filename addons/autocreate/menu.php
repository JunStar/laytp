<?php
return [
    [
        'name'=>'一键生成',
        'rule'=>'addons/autocreate/admin/curd/index',
        'is_menu'=>1,
        'icon'=>'layui-icon layui-icon-share',
        'delete_status'=>0,
        'children'=>[
            [
                'name'=>'一键生成',
                'rule'=>'addons/autocreate/admin/curd/index',
                'is_menu'=>1,
                'icon'=>'layui-icon layui-icon-share',
                'delete_status'=>0,
                'children'=>[
                    [
                        'name'=>'Curd',
                        'rule'=>'addons/autocreate/admin/curd/index',
                        'is_menu'=>1,
                        'icon'=>'layui-icon layui-icon-star-fill',
                        'delete_status'=>1,
                        'children'=>[
                            ['name'=>'查看', 'rule'=>'addons/autocreate/admin/curd/index', 'is_menu'=>0,'delete_status'=>1],
                            ['name'=>'导入', 'rule'=>'addons/autocreate/admin/curd/import', 'is_menu'=>0,'delete_status'=>1],
                            ['name'=>'删除', 'rule'=>'addons/autocreate/admin/curd/del', 'is_menu'=>0,'delete_status'=>1]
                        ]
                    ],
                    [
                        'name'=>'菜单',
                        'rule'=>'addons/autocreate/admin/menu/index',
                        'is_menu'=>1,
                        'icon'=>'layui-icon layui-icon-templeate-1',
                        'delete_status'=>1,
                        'children'=>[
                            ['name'=>'导入', 'rule'=>'addons/autocreate/admin/menu/add', 'is_menu'=>0,'delete_status'=>1],
                            ['name'=>'删除', 'rule'=>'addons/autocreate/admin/menu/del', 'is_menu'=>0,'delete_status'=>1]
                        ]
                    ],
                    [
                        'name'=>'Api文档',
                        'rule'=>'addons/autocreate/admin/apidoc/index',
                        'is_menu'=>1,
                        'icon'=>'layui-icon layui-icon-tabs',
                        'delete_status'=>1
                    ],
                ]
            ],
        ]
    ]
];
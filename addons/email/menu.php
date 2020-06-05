<?php
return [
    [
        'name'=>'邮件',
        'rule'=>'addons/email/admin/email/index',
        'is_menu'=>1,
        'icon'=>'layui-icon layui-icon-file-b',
        'delete_status'=>0,
        'children'=>[
            [
                'name'=>'邮件管理',
                'rule'=>'addons/email/admin/email.template/index',
                'is_menu'=>1,
                'icon'=>'layui-icon layui-icon-file-b',
                'delete_status'=>0,
                'children'=>[
                    [
                        'name'=>'邮件模板管理',
                        'rule'=>'addons/email/admin/email.template/index',
                        'is_menu'=>1,
                        'icon'=>'layui-icon layui-icon-file-b',
                        'delete_status'=>1,
                        'children'=>[
                            ['name'=>'查看', 'rule'=>'addons/email/admin/email.template/index', 'is_menu'=>0],
                            ['name'=>'添加', 'rule'=>'addons/email/admin/email.template/add', 'is_menu'=>0],
                            ['name'=>'编辑', 'rule'=>'addons/email/admin/email.template/edit', 'is_menu'=>0],
                            ['name'=>'设置状态', 'rule'=>'addons/email/admin/email.template/set_status', 'is_menu'=>0],
                            ['name'=>'删除', 'rule'=>'addons/email/admin/email.template/del', 'is_menu'=>0],
                            ['name'=>'回收站', 'rule'=>'addons/email/admin/email.template/recycle', 'is_menu'=>0],
                            ['name'=>'还原', 'rule'=>'addons/email/admin/email.template/renew', 'is_menu'=>0],
                        ]
                    ],
                    [
                        'name'=>'邮件管理',
                        'rule'=>'addons/email/admin/email/index',
                        'is_menu'=>1,
                        'icon'=>'layui-icon layui-icon-file-b',
                        'delete_status'=>1,
                        'children'=>[
                            ['name'=>'查看', 'rule'=>'addons/email/admin/email/index', 'is_menu'=>0],
                            ['name'=>'添加', 'rule'=>'addons/email/admin/email/add', 'is_menu'=>0],
                            ['name'=>'编辑', 'rule'=>'addons/email/admin/email/edit', 'is_menu'=>0],
                            ['name'=>'设置状态', 'rule'=>'addons/email/admin/email/set_status', 'is_menu'=>0],
                            ['name'=>'删除', 'rule'=>'addons/email/admin/email/del', 'is_menu'=>0],
                            ['name'=>'回收站', 'rule'=>'addons/email/admin/email/recycle', 'is_menu'=>0],
                            ['name'=>'还原', 'rule'=>'addons/email/admin/email/renew', 'is_menu'=>0],
                        ]
                    ]
                ]
            ],
        ]
    ]
];
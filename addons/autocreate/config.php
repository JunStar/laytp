<?php
return [
    [
        'name'=>'特殊字段'
        ,'tip'=>'特殊字段，自动生成不需要详细设置的字段'
        ,'key'=>'special_fields'
        ,'type'=>'array'
        ,'content'=>[
            'id',
        ]
    ],
    [
        'name'=>'系统表名'
        ,'tip'=>'不能使用curd生成的系统表名'
        ,'key'=>'system_tables'
        ,'type'=>'array'
        ,'content'=>[
            'lt_admin_log',
            'lt_admin_menu',
            'lt_admin_role',
            'lt_admin_role_rel_menu',
            'lt_admin_role_rel_user',
            'lt_admin_user',
            'lt_autocreate_curd',
            'lt_sysconf',
            'lt_autocreate_menu',
            'lt_user_token',
        ]
    ]
];
<?php
return [
    //不能使用curd生成的系统表名
    'system_tables' => [
        'lt_admin_log',
        'lt_admin_menu',
        'lt_admin_role',
        'lt_admin_role_rel_menu',
        'lt_admin_role_rel_user',
        'lt_admin_user',
        'lt_autocreate_curd',
        'lt_sysconf',
    ],

    //特殊字段，自动生成不需要详细设置的字段
    'special_fields' => ['id'],
];
<?php
return [
    [
        'name'=>'手机短信',
        'rule'=>'addons/aliyun_mobilemsg/admin/mobilemsg/index',
        'is_menu'=>1,
        'icon'=>'layui-icon layui-icon-file-b',
        'delete_status'=>0,
        'children'=>[
            [
                'name'=>'手机短信管理',
                'rule'=>'addons/aliyun_mobilemsg/admin/mobilemsg/index',
                'is_menu'=>1,
                'icon'=>'layui-icon layui-icon-file-b',
                'delete_status'=>1,
                'children'=>[
                    ['name'=>'查看', 'rule'=>'addons/aliyun_mobilemsg/admin/mobilemsg/index', 'is_menu'=>0],
                    ['name'=>'添加', 'rule'=>'addons/aliyun_mobilemsg/admin/mobilemsg/add', 'is_menu'=>0],
                    ['name'=>'编辑', 'rule'=>'addons/aliyun_mobilemsg/admin/mobilemsg/edit', 'is_menu'=>0],
                    ['name'=>'设置状态', 'rule'=>'addons/aliyun_mobilemsg/admin/mobilemsg/set_status', 'is_menu'=>0],
                    ['name'=>'删除', 'rule'=>'addons/aliyun_mobilemsg/admin/mobilemsg/del', 'is_menu'=>0],
                    ['name'=>'回收站', 'rule'=>'addons/aliyun_mobilemsg/admin/mobilemsg/recycle', 'is_menu'=>0],
                    ['name'=>'还原', 'rule'=>'addons/aliyun_mobilemsg/admin/mobilemsg/renew', 'is_menu'=>0],
                ]
            ]
        ]
    ]
];
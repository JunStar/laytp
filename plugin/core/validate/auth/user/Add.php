<?php

namespace plugin\core\validate\auth\user;

use think\Validate;

class Add extends Validate
{
    //数组顺序就是检测的顺序
    protected $rule = [
        'username'         => 'require|length:2,30',
        'nickname'         => 'require|length:2,30',
        'password'         => 'require|length:6,30|confirm:re_password',
        'avatar'           => 'require',
        'is_super_manager' => 'require',
        'status'           => 'require',
    ];

    //定义内置方法检验失败后返回的字符
    protected $message = [
        'username.require'         => '用户名不能为空',
        'username.length'          => '用户名长度2-30',
        'nickname.require'         => '昵称不能为空',
        'nickname.length'          => '昵称长度2-30',
        'password.require'         => '密码不能为空',
        'password.length'          => '密码长度6-30',
        'password.confirm'         => '两次密码输入不相同',
        'avatar.require'           => '请上传头像',
        'is_super_manager.require' => '请设置用户是否为超级管理员',
        'status.require'           => '请设置账号的状态',
    ];
}
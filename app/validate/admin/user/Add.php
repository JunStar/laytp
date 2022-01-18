<?php

namespace app\validate\admin\user;

use app\model\admin\User;
use think\Validate;

class Add extends Validate
{
    //数组顺序就是检测的顺序
    protected $rule = [
        'username'         => 'require|length:2,30|checkUsername:',
        'nickname'         => 'require|length:2,30|checkNickname:',
        'password'         => 'require|length:6,30|confirm:re_password',
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
        'is_super_manager.require' => '请设置用户是否为超级管理员',
        'status.require'           => '请设置账号的状态',
    ];

    //验证用户名的唯一性
    protected function checkUsername($username, $rule, $data){
        $userId = User::getFieldByUsername($username, 'id');
        if($userId){
            return '用户名已存在';
        }
        return true;
    }

    //验证昵称的唯一性
    protected function checkNickname($nickname, $rule, $data){
        $userId = User::getFieldByUsername($nickname, 'id');
        if($userId){
            return '昵称已存在';
        }
        return true;
    }
}
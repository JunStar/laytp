<?php

namespace app\validate\admin\user;

use app\model\admin\User;
use think\Validate;

class singleEdit extends Validate
{
    //数组顺序就是检测的顺序
    protected $rule = [
        'id'               => 'require',
        'nickname'         => 'require|length:2,30',
        'old_password'     => 'length:6,30|checkOldPassword:',
        'password'         => 'length:6,30|confirm:re_password',
    ];

    //定义内置方法检验失败后返回的字符
    protected $message = [
        'id.require'            => 'ID不能为空',
        'nickname.require'      => '昵称不能为空',
        'nickname.length'       => '昵称长度2-30',
        'old_password.length'   => '旧密码长度6-30',
        'password.length'       => '新密码长度6-30',
        'password.confirm'      => '两次新密码输入不相同',
    ];

    //验证旧密码是否正确
    protected function checkOldPassword($oldPassword, $rule, $data){
        $passwordHash = User::getFieldById($data['id'], 'password');
        if(!password_verify(md5($oldPassword), $passwordHash)){
            return '旧密码错误';
        }
        return true;
    }
}
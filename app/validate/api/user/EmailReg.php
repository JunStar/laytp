<?php

namespace app\validate\api\user;

//邮箱密码注册验证器
use app\common\model\User;
use think\Validate;

class EmailReg extends Validate
{
    //数组顺序就是检测的顺序，比如这里，会先检测code验证码的正确性
    protected $rule = [
        'email'      => 'require|email|checkEmail:',
        'password'   => 'require|length:6,26|confirm:repassword',
        'repassword' => 'require|length:6,26',
    ];

    //定义内置方法检验失败后返回的字符
    protected $message = [
        'email.require'      => '邮箱不能为空',
        'email.email'        => '邮箱格式不正确',
        'password.require'   => '密码不能为空',
        'password.length'    => '密码长度需要6到26个字符',
        'password.confirm'   => '两次密码输入不相同',
        'repassword.require' => '重复密码不能为空',
        'repassword.length'  => '重复密码长度需要6到26个字符',
    ];

    protected function checkEmail($email, $rule, $data){
        $exist = User::getFieldByEmail($email,'id');
        if($exist){
            return '邮箱已存在';
        }
        return true;
    }
}
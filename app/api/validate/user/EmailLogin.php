<?php

namespace app\api\validate\user;

//邮箱密码登录验证器
use app\common\model\User;
use think\Validate;

class EmailLogin extends Validate
{
    //数组顺序就是检测的顺序，比如这里，会先检测code验证码的正确性
    protected $rule = [
        'email' => 'require|email',
        'password' => 'require|length:6,26|checkPassword:',
    ];

    //定义内置方法检验失败后返回的字符
    protected $message = [
        'email.require' => '邮箱不能为空',
        'email.email' => '邮箱格式不正确',
        'password.require' => '密码不能为空',
        'password.length' => '密码长度需要6到26个字符',
    ];

    //自定义密码检验方法
    protected function checkPassword($password, $rule, $data)
    {
        $email = $data['email'];
        $user = new User();
        $passwordHash = $user->getFieldByEmail($email, 'password');
        return (!password_verify(md5($password), $passwordHash)) ? '账户信息错误' : true;
    }
}
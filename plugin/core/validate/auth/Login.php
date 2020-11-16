<?php

namespace plugin\core\validate\auth;

use plugin\core\model\User;
use think\facade\Config;
use think\Validate;

class Login extends Validate
{
    //数组顺序就是检测的顺序，比如这里，会先检测code验证码的正确性
    protected $rule = [
        'verify_code' => 'checkVerifyCode:',
        'username' => 'require',
        'password' => 'require|checkPassword:',
    ];

    //定义内置方法检验失败后返回的字符
    protected $message = [
        'username.require' => '用户名不能为空',
        'password.require' => '密码不能为空',
    ];

    //自定义验证码检验方法
    protected function checkVerifyCode($verifyCode)
    {
        if (!Config::get('laytp.basic.loginNeedCaptcha')) {
            return true;
        }
        if (!captcha_check($verifyCode)) {
            return '验证码错误';
        } else {
            return true;
        }
    }

    //自定义密码检验方法
    protected function checkPassword($password, $rule, $data)
    {
        $username = $data['username'];
        $password_hash = User::where('username', '=', $username)->value('password');
        if (!password_verify(md5($password), $password_hash)) {
            return '用户名或密码错误';
        } else {
            return true;
        }
    }
}
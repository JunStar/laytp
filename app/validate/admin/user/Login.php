<?php

namespace app\validate\admin\user;

use app\model\admin\User;
use app\service\ConfServiceFacade;
use laytp\library\Str;
use think\facade\Config;
use think\Validate;

class Login extends Validate
{
    //数组顺序就是检测的顺序，比如这里，会先检测code验证码的正确性
    protected $rule = [
        'verify_code' => 'checkVerifyCode:',
        'username'    => 'require',
        'password'    => 'require|checkPassword:',
    ];

    //定义内置方法检验失败后返回的字符
    protected $message = [
        'username.require' => '用户名不能为空',
        'password.require' => '密码不能为空',
    ];

    //自定义验证码检验方法
    protected function checkVerifyCode($verifyCode)
    {
        if(ConfServiceFacade::get("system.basic.loginNeedCaptcha") == 1){
            if (!captcha_check($verifyCode)) {
                return '验证码错误';
            } else {
                return true;
            }
        }
        return true;
    }

    //自定义密码检验方法
    protected function checkPassword($password, $rule, $data)
    {
        $username = $data['username'];
        $user     = User::where('username', '=', $username)->find();
        if (!$user) {
            return '用户名或密码错误';
        }

        $passwordHash = $user->password;
        if (!Str::checkPassword($password, $passwordHash)) {
            return '用户名或密码错误';
        }

        $status = $user->status;
        if ($status == 2) {
            return '用户已被禁用，请联系管理员';
        }

        return true;
    }
}
<?php
namespace app\admin\validate\auth;

use think\captcha\Captcha;
use think\facade\Config;
use think\Validate;

class Login extends Validate
{
    //数组顺序就是检测的顺序，比如这里，会先检测code验证码的正确性
    protected $rule =   [
        'code'      =>  'require|checkCode:',
        'username'  =>  'require',
        'password'  =>  'require|checkPassword:',
    ];

    //定义内置方法检验失败后返回的字符
    protected $message  =   [
        'username.require'      => '用户名不能为空',
        'password.require'  => '密码不能为空',
    ];

    //自定义验证码检验方法
    protected function checkCode($code){
        if( !Config::get('laytp.basic.login_vercode') ){
            return true;
        }
        $captcha = new Captcha();
        if( !$captcha->check($code))
        {
            return '验证码错误';
        }
        else
        {
            return true;
        }
    }

    //自定义密码检验方法
    protected function checkPassword($password, $rule, $data){
        $username = $data['username'];
        $password_hash = model('auth.User')->getFieldByUsername($username, 'password');
        if( !password_verify( $password, $password_hash ) )
        {
            return '账户信息错误';
        }
        else
        {
            return true;
        }
    }
}
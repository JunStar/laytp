<?php
namespace app\admin\validate\auth;

use think\captcha\Captcha;
use think\Validate;

class Login extends Validate
{
    //数组顺序就是检测的顺序，比如这里，会先检测code验证码的正确性
    protected $rule =   [
        'code'      =>  'require|checkCode:',
        'name'      =>  'require',
        'password'  =>  'require|checkPassword:',
    ];

    //定义内置方法检验失败后返回的字符
    protected $message  =   [
        'code.require'      => '验证码不能为空',
        'name.require'      => '用户名不能为空',
        'password.require'  => '密码不能为空',
    ];

    //自定义验证码检验方法
    protected function checkCode($code){
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
        $name = $data['name'];
        $password_hash = model('auth.User')->getFieldByName($name, 'password');
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
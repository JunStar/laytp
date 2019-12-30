<?php
namespace app\api\validate\user;

//用户名密码登录验证器
use app\common\model\User;
use think\Validate;

class UsernameLogin extends Validate
{
    //数组顺序就是检测的顺序，比如这里，会先检测code验证码的正确性
    protected $rule =   [
        'username'  =>  'require|length:2,10|alphaDash',
        'password'  =>  'require|length:6,26|checkPassword:',
    ];

    //定义内置方法检验失败后返回的字符
    protected $message  =   [
        'username.require'  => '用户名不能为空',
        'username.length'  => '用户名长度2到10个字符',
        'username.alphaDash'  => '昵称只能包含字母和数字，下划线_及破折号-',
        'password.require'  => '密码不能为空',
        'password.length'  => '密码长度6到26个字符',
    ];

    //自定义密码检验方法
    protected function checkPassword($password, $rule, $data){
        $username = $data['username'];
        $user_model = new User();
        $password_hash = $user_model->getFieldByUsername($username, 'password');
        return ( !password_verify( $password, $password_hash ) ) ? '账户信息错误' : true;
    }
}
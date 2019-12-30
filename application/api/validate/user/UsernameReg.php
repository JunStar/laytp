<?php
//用户名密码注册验证器
namespace app\api\validate\user;

use app\common\model\User;
use think\Validate;

class UsernameReg extends Validate
{
    //数组顺序就是检测的顺序
    protected $rule =   [
        'username'      =>  'require|length:2,10|alphaDash|checkUsername:',
        'password'      =>  'require|length:6,26',
        'repassword'    =>  'require|length:6,26|confirm:password',
    ];

    //定义内置方法检验失败后返回的字符
    protected $message  =   [
        'username.require'  => '用户名不能为空',
        'username.length'  => '用户名长度2到10个字符',
        'username.alphaDash'  => '昵称只能包含字母和数字，下划线_及破折号-',
        'password.require'  => '密码不能为空',
        'password.length'  => '密码长度6到26个字符',
        'repassword.require'  => '重复密码不能为空',
        'repassword.length'  => '重复密码长度6到26个字符',
        'repassword.confirm'  => '两次密码输入不相同',
    ];

    protected function checkUsername($username){
        $user_model = new User();
        $id = $user_model->getFieldByUsername($username, 'id');
        return $id ? '用户名已存在' : true;
    }
}
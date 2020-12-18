<?php

namespace plugin\core\validate\general;

use plugin\core\model\User;
use plugin\core\service\UserServiceFacade;
use think\Validate;

class Profile extends Validate
{
    //数组顺序就是检测的顺序
    protected $rule = [
        'username' => 'require|length:2,30|checkUsername:',
        'nickname' => 'require|length:2,30|checkNickname:',
        'password' => 'length:6,30|confirm:re_password',
        'avatar'   => 'require',
    ];

    //定义内置方法检验失败后返回的字符
    protected $message = [
        'username.require' => '用户名不能为空',
        'username.length'  => '用户名长度2-30',
        'nickname.require' => '昵称不能为空',
        'nickname.length'  => '昵称长度2-30',
        'password.confirm' => '两次密码输入不相同',
        'avatar.require'   => '请上传头像',
    ];

    //检测用户名
    public function checkUsername($username)
    {
        $user = User::withTrashed()->where('username', '=', $username)->find();
        if ($user && $user->id != UserServiceFacade::getUser()->id) {
            return '用户名[' . $username . ']已存在';
        }
        return true;
    }

    //检测昵称
    public function checkNickname($nickname)
    {
        $user = User::withTrashed()->where('nickname', '=', $nickname)->find();
        if ($user && $user->id != UserServiceFacade::getUser()->id) {
            return '昵称[' . $nickname . ']已存在';
        }
        return true;
    }
}
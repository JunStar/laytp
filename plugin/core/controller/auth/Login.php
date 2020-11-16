<?php

namespace plugin\core\controller\auth;

use plugin\core\model\User;
use laytp\controller\Backend;
use laytp\library\Random;
use laytp\library\Token;

/**
 * 登录控制器
 */
class Login extends Backend
{
    protected $noNeedLogin = ['*'];

    //登录
    public function doLogin()
    {
        //获取表单提交数据
        $param = $this->request->param();
        //验证表单提交
        $validate = new \plugin\core\validate\auth\Login;
        if (!$validate->check($param)) {
            return $this->error($validate->getError());
        }
        //设置登录信息
        $user_id = User::where('username', '=', $param['username'])->value('id');
        $token = Random::uuid();
        Token::set($token, $user_id, 24 * 60 * 60 * 3);
        return $this->success('登录成功', ['laytp_admin_token' => $token]);
    }

    //退出登录
    public function logout()
    {
        $token = $this->request->header('laytp-admin-token');
        Token::delete($token);
        return $this->success('退出成功');
    }
}
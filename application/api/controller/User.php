<?php
namespace app\api\controller;

use controller\Api;

/**
 * 会员相关
 */
class User extends Api{
    /**
     * @ApiTitle    (会员登录)
     * @ApiSummary  (会员登录信息)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/user/login)
     * @ApiParams   (name="account", type="integer", required=true, description="账号")
     * @ApiParams   (name="password", type="string", required=true, description="密码")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="登录成功")
     * @ApiReturn
    ({
    'code':'1',
    'msg':'登录成功'
})
     */
    public function login()
    {
        $account = $this->request->request('account');
        $password = $this->request->request('password');
        if (!$account || !$password) {
            $this->error('账号或密码为空');
        }
        $res = $this->auth->login($account, $password);
        if ($res) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success('登录成功', $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * @ApiTitle    (注册会员)
     * @ApiSummary  (注册会员信息)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/user/register)
     * @ApiParams   (name="account", type="integer", required=true, description="账号")
     * @ApiParams   (name="password", type="string", required=true, description="密码")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="注册成功")
     * @ApiReturn
    ({
    'code':'1',
    'msg':'注册成功'
})
     */
    public function register()
    {
        $username = $this->request->request('username');
        $password = $this->request->request('password');
        if (!$username || !$password) {
            $this->error('用户名或密码为空');
        }
        $ret = $this->auth->register($username, $password);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success('注册成功', $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * @ApiTitle    (注销登录)
     * @ApiSummary  (注销登录信息)
     * @ApiMethod   (GET)
     * @ApiRoute    (/api/user/logout)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="注销成功")
     * @ApiReturn
    ({
    'code':'1',
    'msg':'注销成功'
})
     */
    public function logout()
    {
        $this->auth->logout();
        $this->success('注销成功');
    }
}
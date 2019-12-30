<?php
namespace app\api\controller;

use app\api\validate\user\UsernameLogin;
use app\api\validate\user\UsernameReg;
use controller\Api;

/**
 * 会员相关
 */
class User extends Api{

    public $no_need_login = ['username_login','username_reg'];

    /**
     * @ApiTitle    (用户名密码注册)
     * @ApiSummary  (用户名密码注册)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/user/username_reg)
     * @ApiParams   (name="username", type="integer", required=true, description="用户名")
     * @ApiParams   (name="password", type="string", required=true, description="密码")
     * @ApiParams   (name="repassword", type="string", required=true, description="重复密码")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="1")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="注册成功")
     * @ApiReturn
({
    'code':'1',
    'msg':'注册成功'
})
     */
    public function username_reg()
    {
        if(!$this->request->isPost()){
            $this->error('请使用POST请求');
        }

        $param['username'] = $this->request->request('username');
        $param['password'] = $this->request->request('password');
        $param['repassword'] = $this->request->request('repassword');

        $validate = new UsernameReg();
        if($validate->check($param)){
            if($this->service_user->usernameReg($param)){
                $this->success('注册成功', $this->service_user->getUserInfo());
            }else{
                $this->error('注册失败',$this->service_user->getError());
            }
        }else{
            $this->error('注册失败',$validate->getError());
        }
    }

    /**
     * @ApiTitle    (用户名密码登录)
     * @ApiSummary  (用户名密码登录)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/user/username_login)
     * @ApiParams   (name="username", type="string", required=true, description="用户名")
     * @ApiParams   (name="password", type="string", required=true, description="密码")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="登录成功")
     * @ApiReturn
    ({
    'code':'1',
    'msg':'登录成功'
    })
     */
    public function username_login()
    {
        if(!$this->request->isPost()){
            $this->error('请使用POST请求');
        }

        $param['username'] = $this->request->request('username');
        $param['password'] = $this->request->request('password');

        $validate = new UsernameLogin();
        if($validate->check($param)){
            if($this->service_user->usernameLogin($param)){
                $this->success('登录成功', $this->service_user->getUserInfo());
            }else{
                $this->error('登录失败',$this->service_user->getError());
            }
        }else{
            $this->error('登录失败',$validate->getError());
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
        if( $this->service_user->logout() ){
            $this->success('注销成功');
        }else{
            $this->error($this->auth->getError());
        }
    }
}
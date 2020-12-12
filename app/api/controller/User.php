<?php

namespace app\api\controller;

use app\api\service\UserServiceFacade;
use app\api\validate\user\EmailLogin;
use app\api\validate\user\EmailReg;
use laytp\controller\Api;

/**
 * 会员相关
 * @ApiWeigh (90)
 */
class User extends Api
{
    public $noNeedLogin = [
        'emailLogin'
    ];

    /**
     * @ApiTitle    (根据token获取用户信息)
     * @ApiSummary  (根据token获取用户信息)
     * @ApiMethod   (GET)
     * @ApiHeaders  (name="token", type="string", required="true", description="用户登录后得到的Token")
     * @ApiReturnParams   (name="err_code", type="integer", description="错误码.0=没有错误，表示操作成功；1=常规错误码，客户端仅需提示msg；其他错误码与具体业务相关，其他错误码举例：10401。前端需要跳转至登录界面。")
     * @ApiReturnParams   (name="msg", type="string", description="返回描述")
     * @ApiReturnParams   (name="time", type="integer", description="请求时间，Unix时间戳，单位秒")
     * @ApiReturnParams   (name="data.id", type="integer", description="用户主键ID")
     * @ApiReturnParams   (name="data.mobile", type="string", description="手机号")
     * @ApiReturnParams   (name="data.email", type="string", description="邮箱")
     * @ApiReturnParams   (name="data.username", type="string", description="用户名")
     * @ApiReturnParams   (name="data.nickname", type="string", description="昵称")
     * @ApiReturnParams   (name="data.avatar", type="string", description="头像")
     * @ApiReturnParams   (name="data.token", type="string", description="用户登录凭证,Token")
     * @ApiReturn
     * ({
     * "err_code": 0,
     * "msg": "获取成功",
     * "time": 1591149171,
     * "data": {
     * "id": 4,
     * "mobile": "17603005414",
     * "email": "",
     * "username": "",
     * "nickname": "",
     * "avatar": "http://local.laytp.com/static/index/image/default.png",
     * "token": "d32e5210-050d-4902-b4b2-0173da12e191"
    * }
* })
     */
    public function info()
    {
        $this->success('获取成功', UserServiceFacade::getUserInfo());
    }

    /**
     * @ApiTitle    (邮箱注册)
     * @ApiSummary  (邮箱注册)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/user/emailReg)
     * @ApiParams   (name="email", type="string", required="true", description="邮箱")
     * @ApiParams   (name="password", type="string", required="true", description="密码")
     * @ApiParams   (name="repassword", type="string", required="true", description="重复密码")
     * @ApiReturnParams   (name="code", type="integer", description="错误码.0=没有错误，表示操作成功；1=常规错误码，客户端仅需提示msg；其他错误码与具体业务相关，其他错误码举例：10401。前端需要跳转至登录界面。")
     * @ApiReturnParams   (name="msg", type="string", description="返回描述")
     * @ApiReturnParams   (name="time", type="integer", description="请求时间，Unix时间戳，单位秒")
     * @ApiReturnParams   (name="data.token", type="string", description="用户登录后得到的凭证，token")
     * @ApiReturn
     * ({
     * "err_code": 0,
     * "msg": "操作成功",
     * "time": 1584513627,
     * "data": {
     * "token": "b58ea1f0-e856-4ec4-b2b3-d852b9af86b5"
    * }
* })
     */
    public function emailReg()
    {
        $param['email'] = $this->request->param('email');
        $param['password'] = $this->request->param('password');
        $param['repassword'] = $this->request->param('repassword');

        $validate = new EmailReg();
        if ($validate->check($param)) {
            if (UserServiceFacade::emailRegLogin($param)) {
                $this->success('注册成功', ['token' => UserServiceFacade::getToken()]);
            } else {
                $this->error('注册失败', UserServiceFacade::getError());
            }
        } else {
            $this->error('注册失败',$validate->getError());
        }
    }

    /**
     * @ApiTitle    (邮箱密码登录)
     * @ApiSummary  (邮箱密码登录)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/user/emailLogin)
     * @ApiParams   (name="email", type="string", required="true", description="邮箱")
     * @ApiParams   (name="password", type="string", required="true", description="密码")
     * @ApiReturnParams   (name="code", type="integer", description="错误码.0=没有错误，表示操作成功；1=常规错误码，客户端仅需提示msg；其他错误码与具体业务相关，其他错误码举例：10401。前端需要跳转至登录界面。")
     * @ApiReturnParams   (name="msg", type="string", description="返回描述")
     * @ApiReturnParams   (name="time", type="integer", description="请求时间，Unix时间戳，单位秒")
     * @ApiReturnParams   (name="data.token", type="string", description="用户登录后得到的凭证，token")
     * @ApiReturn
     * ({
     * "err_code": 0,
     * "msg": "操作成功",
     * "time": 1584513627,
     * "data": {
     * "token": "b58ea1f0-e856-4ec4-b2b3-d852b9af86b5"
    * }
* })
     */
    public function emailLogin(){
        $param['email'] = $this->request->param('email');
        $param['password'] = $this->request->param('password');

        $validate = new EmailLogin();
        if ($validate->check($param)) {
            if (UserServiceFacade::emailRegLogin($param)) {
                $this->success('登录成功', ['token' => UserServiceFacade::getToken()]);
            } else {
                $this->error('登录失败', UserServiceFacade::getError());
            }
        } else{
            $this->error('登录失败',$validate->getError());
        }
    }

    /**
     * @ApiTitle    (注销登录)
     * @ApiSummary  (注销登录信息)
     * @ApiMethod   (GET)
     * @ApiRoute    (/api/user/logout)
     * @ApiHeaders  (name="token", type="string", required="true", description="用户登录后得到的Token")
     * @ApiReturnParams   (name="err_code", type="integer", description="错误码.0=没有错误，表示操作成功；1=常规错误码，客户端仅需提示msg；其他错误码与具体业务相关，其他错误码举例：10401。前端需要跳转至登录界面。")
     * @ApiReturnParams   (name="msg", type="string", description="返回描述")
     * @ApiReturnParams   (name="time", type="integer", description="请求时间，Unix时间戳，单位秒")
     * @ApiReturnParams   (name="data", type="null", description="只会返回null")
     * @ApiReturn
     * ({
     * "err_code": 0,
     * "msg": "注销成功",
    * "time": 1584513627,
    * "data": null
* })
     */
    public function logout()
    {
        if ($this->service_user->logout()) {
            $this->success('注销成功');
        } else {
            $this->error($this->auth->getError());
        }
    }
}
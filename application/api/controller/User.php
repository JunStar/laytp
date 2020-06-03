<?php
namespace app\api\controller;

use app\api\validate\user\EmailLogin;
use app\api\validate\user\MobileCodeRegLogin;
use app\api\validate\user\MobileOneClickLogin;
use app\api\validate\user\UsernameLogin;
use app\api\validate\user\UsernameReg;
use app\common\service\Mobile;
use controller\Api;

/**
 * 会员相关
 * @ApiWeigh (90)
 */
class User extends Api{

    public $no_need_login = [
        'username_login'
        ,'username_reg'
        ,'mobile_code_reg_login'
        ,'mobile_one_click_login'
    ];

    /**
     * 根据token获取用户信息
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
({
    "err_code": 0,
    "msg": "获取成功",
    "time": 1591149171,
    "data": {
        "id": 4,
        "mobile": "17603005414",
        "email": "",
        "username": "",
        "nickname": "",
        "avatar": "http://local.laytp.com/static/index/image/default.png",
        "token": "d32e5210-050d-4902-b4b2-0173da12e191"
    }
})
     */
    public function info(){
        $this->success('获取成功', $this->service_user->getUserInfo());
    }

    /**
     * @ApiTitle    (手机号+手机验证码 注册+登录)
     * @ApiSummary  (手机号+手机验证码 注册+登录)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/user/mobile_code_reg_login)
     * @ApiParams   (name="mobile", type="string", required="true", description="手机号")
     * @ApiParams   (name="code", type="string", required="true", description="手机验证码。配置信息app_debug=true时，可以任意输入；app_debug=false时，需要先使用公用接口的发送手机验证码接口，然后输入手机短信收到的验证码")
     * @ApiReturnParams   (name="err_code", type="integer", description="错误码.0=没有错误，表示操作成功；1=常规错误码，客户端仅需提示msg；其他错误码与具体业务相关，其他错误码举例：10401。前端需要跳转至登录界面。")
     * @ApiReturnParams   (name="msg", type="string", description="返回描述")
     * @ApiReturnParams   (name="time", type="integer", description="请求时间，Unix时间戳，单位秒")
     * @ApiReturnParams   (name="data.token", type="string", description="用户登录后得到的凭证，token")
     * @ApiReturn
({
    "err_code": 0,
    "msg": "登录成功",
    "time": 1591151771,
    "data": {
        "token": "b58ea1f0-e856-4ec4-b2b3-d852b9af86b5"
    }
})
     */
    public function mobile_code_reg_login()
    {
        if(!$this->request->isPost()){
            $this->error('请使用POST请求');
        }

        $param['mobile'] = $this->request->request('mobile');
        $param['code'] = $this->request->request('code');

        $validate = new MobileCodeRegLogin();
        if($validate->check($param)){
            if($this->service_user->mobileCodeRegLogin($param)){
                $this->success('登录成功', ['token'=>$this->service_user->getToken()]);
            }else{
                $this->error('登录失败,'.$this->service_user->getError());
            }
        }else{
            $this->error('登录失败,'.$validate->getError());
        }
    }

    /**
     * @ApiTitle    (手机号码免密一键登录)
     * @ApiSummary  (手机号码免密一键登录)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/user/mobile_one_click_login)
     * @ApiParams   (name="access_token", type="string", required="true", description="app端SDK获取的登录token")
     * @ApiReturnParams   (name="err_code", type="integer", description="错误码.0=没有错误，表示操作成功；1=常规错误码，客户端仅需提示msg；其他错误码与具体业务相关，其他错误码举例：10401。前端需要跳转至登录界面。")
     * @ApiReturnParams   (name="msg", type="string", description="返回描述")
     * @ApiReturnParams   (name="time", type="integer", description="请求时间，Unix时间戳，单位秒")
     * @ApiReturnParams   (name="data.token", type="string", description="用户登录后得到的凭证，token")
     * @ApiReturn
({
    "err_code": 0,
    "msg": "登录成功",
    "time": 1591151771,
    "data": {
        "token": "b58ea1f0-e856-4ec4-b2b3-d852b9af86b5"
    }
})
     */
    public function mobile_one_click_login()
    {
        if(!$this->request->isPost()){
            $this->error('请使用POST请求');
        }

        $params['access_token'] = $this->request->request('access_token');

        $validate = new MobileOneClickLogin();
        if($validate->check($params)){
            $mobile_service = new Mobile();
            $service_res = $mobile_service->getMobileByToken($params['access_token']);
            if($service_res){
                $params['mobile'] = $service_res;
            }else{
                $this->error('登录失败,'.$mobile_service->getError());
            }
            if($this->service_user->mobileCodeRegLogin($params)){
                $this->success('登录成功', ['token'=>$this->service_user->getToken()]);
            }else{
                $this->error('登录失败,'.$this->service_user->getError());
            }
        }else{
            $this->error('登录失败,'.$validate->getError());
        }
    }

    /**
     * @ApiTitle    (用户名密码注册)
     * @ApiSummary  (用户名密码注册)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/user/username_reg)
     * @ApiParams   (name="username", type="integer", required="true", description="用户名")
     * @ApiParams   (name="password", type="string", required="true", description="密码")
     * @ApiParams   (name="repassword", type="string", required="true", description="重复密码")
     * @ApiReturnParams   (name="err_code", type="integer", description="错误码.0=没有错误，表示操作成功；1=常规错误码，客户端仅需提示msg；其他错误码与具体业务相关，其他错误码举例：10401。前端需要跳转至登录界面。")
     * @ApiReturnParams   (name="msg", type="string", description="返回描述")
     * @ApiReturnParams   (name="time", type="integer", description="请求时间，Unix时间戳，单位秒")
     * @ApiReturnParams   (name="data.token", type="string", description="用户登录后得到的凭证，token")
     * @ApiReturn
({
    "err_code": 0,
    "msg": "操作成功",
    "time": 1584513627,
    "data": {
        "token": "b58ea1f0-e856-4ec4-b2b3-d852b9af86b5"
    }
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
                $this->success('注册成功', ['token'=>$this->service_user->getToken()]);
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
     * @ApiParams   (name="username", type="string", required="true", description="用户名")
     * @ApiParams   (name="password", type="string", required="true", description="密码")
     * @ApiReturnParams   (name="err_code", type="integer", description="错误码.0=没有错误，表示操作成功；1=常规错误码，客户端仅需提示msg；其他错误码与具体业务相关，其他错误码举例：10401。前端需要跳转至登录界面。")
     * @ApiReturnParams   (name="msg", type="string", description="返回描述")
     * @ApiReturnParams   (name="time", type="integer", description="请求时间，Unix时间戳，单位秒")
     * @ApiReturnParams   (name="data.token", type="string", description="用户登录后得到的凭证，token")
     * @ApiReturn
({
    "err_code": 0,
    "msg": "操作成功",
    "time": 1584513627,
    "data": {
        "token": "b58ea1f0-e856-4ec4-b2b3-d852b9af86b5"
    }
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
                $this->success('登录成功', ['token'=>$this->service_user->getToken()]);
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
     * @ApiHeaders  (name="token", type="string", required="true", description="用户登录后得到的Token")
     * @ApiReturnParams   (name="err_code", type="integer", description="错误码.0=没有错误，表示操作成功；1=常规错误码，客户端仅需提示msg；其他错误码与具体业务相关，其他错误码举例：10401。前端需要跳转至登录界面。")
     * @ApiReturnParams   (name="msg", type="string", description="返回描述")
     * @ApiReturnParams   (name="time", type="integer", description="请求时间，Unix时间戳，单位秒")
     * @ApiReturnParams   (name="data", type="null", description="只会返回null")
     * @ApiReturn
({
    "err_code": 0,
    "msg": "注销成功",
    "time": 1584513627,
    "data": null
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
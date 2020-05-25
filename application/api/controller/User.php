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
     * @ApiHeaders        (name=token, type=string, required=true, description="用户登录后得到的Token")
     * @ApiReturnParams   (name="code", type="integer", description="返回状态码.0=失败,1=成功")
     * @ApiReturnParams   (name="msg", type="string", description="返回描述")
     * @ApiReturnParams   (name="time", type="integer", description="请求时间，Unix时间戳，单位秒")
     * @ApiReturnParams   (name="data.id", type="integer", description="用户主键ID")
     * @ApiReturnParams   (name="data.mobile", type="string", description="手机号")
     * @ApiReturnParams   (name="data.email", type="string", description="Email")
     * @ApiReturnParams   (name="data.username", type="string", description="用户名")
     * @ApiReturnParams   (name="data.nickname", type="string", description="昵称")
     * @ApiReturnParams   (name="data.avatar", type="string", description="头像")
     * @ApiReturnParams   (name="data.token", type="string", description="用户登录凭证,Token")
     * @ApiReturnParams   (name="data.user_id", type="integer", description="用户主键ID")
     * @ApiReturnParams   (name="data.createtime", type="integer", description="创建时间，Unix时间戳，单位秒")
     * @ApiReturnParams   (name="data.expiretime", type="integer", description="Token有效至，Unix时间戳，单位秒")
     * @ApiReturnParams   (name="data.expires_in", type="integer", description="Token有效时长，单位秒")
     * @ApiReturn
({
    "code": 1,
    "msg": "获取成功",
    "time": 1590206649,
    "data": {
        "id": 3,
        "mobile": "17603005415",
        "email": null,
        "username": null,
        "nickname": null,
        "avatar": "http://local.sjdw.com/static/index/image/default.png",
        "token": "920df4ba-368c-4b6c-aa5d-bc61c6e9f17e",
        "user_id": 3,
        "createtime": 1590206642,
        "expiretime": 1590208442,
        "expires_in": 1793
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
     * @ApiParams   (name="mobile", type="string", required=true, description="手机号")
     * @ApiParams   (name="code", type="string", required=true, description="手机验证码")
     * @ApiReturnParams   (name="code", type="integer", description="返回状态码.0=失败,1=成功")
     * @ApiReturnParams   (name="msg", type="string", description="返回描述")
     * @ApiReturnParams   (name="time", type="integer", description="请求时间，Unix时间戳，单位秒")
     * @ApiReturnParams   (name="data.id", type="integer", description="用户主键ID")
     * @ApiReturnParams   (name="data.mobile", type="string", description="手机号")
     * @ApiReturnParams   (name="data.email", type="string", description="Email")
     * @ApiReturnParams   (name="data.username", type="string", description="用户名")
     * @ApiReturnParams   (name="data.nickname", type="string", description="昵称")
     * @ApiReturnParams   (name="data.avatar", type="string", description="头像")
     * @ApiReturnParams   (name="data.token", type="string", description="用户登录凭证,Token")
     * @ApiReturnParams   (name="data.user_id", type="integer", description="用户主键ID")
     * @ApiReturnParams   (name="data.createtime", type="integer", description="创建时间，Unix时间戳，单位秒")
     * @ApiReturnParams   (name="data.expiretime", type="integer", description="Token有效至，Unix时间戳，单位秒")
     * @ApiReturnParams   (name="data.expires_in", type="integer", description="Token有效时长，单位秒")
     * @ApiReturn
    ({
    "code": 1,
    "msg": "操作成功",
    "time": 1584330277,
    "data": {
    "id": 3,
    "mobile": "13800000000",
    "email": null,
    "username": null,
    "nickname": null,
    "avatar": null,
    "token": "c96599f3-4708-418f-906e-b4d62f2bd323",
    "user_id": 3,
    "createtime": 1584330099,
    "expiretime": 1584416499,
    "expires_in": 86222
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
                $this->success('操作成功', $this->service_user->getUserInfo());
            }else{
                $this->error('操作失败,'.$this->service_user->getError());
            }
        }else{
            $this->error('操作失败,'.$validate->getError());
        }
    }

    /**
     * @ApiTitle    (手机号码免密一键登录)
     * @ApiSummary  (手机号码免密一键登录)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/user/mobile_one_click_login)
     * @ApiParams   (name="access_token", type="string", required=true, description="app端SDK获取的登录token")
     * @ApiReturnParams   (name="code", type="integer", description="返回状态码.0=失败,1=成功")
     * @ApiReturnParams   (name="msg", type="string", description="返回描述")
     * @ApiReturnParams   (name="time", type="integer", description="请求时间，Unix时间戳，单位秒")
     * @ApiReturnParams   (name="data.id", type="integer", description="用户主键ID")
     * @ApiReturnParams   (name="data.mobile", type="string", description="手机号")
     * @ApiReturnParams   (name="data.email", type="string", description="Email")
     * @ApiReturnParams   (name="data.username", type="string", description="用户名")
     * @ApiReturnParams   (name="data.nickname", type="string", description="昵称")
     * @ApiReturnParams   (name="data.avatar", type="string", description="头像")
     * @ApiReturnParams   (name="data.token", type="string", description="用户登录凭证,Token")
     * @ApiReturnParams   (name="data.user_id", type="integer", description="用户主键ID")
     * @ApiReturnParams   (name="data.createtime", type="integer", description="创建时间，Unix时间戳，单位秒")
     * @ApiReturnParams   (name="data.expiretime", type="integer", description="Token有效至，Unix时间戳，单位秒")
     * @ApiReturnParams   (name="data.expires_in", type="integer", description="Token有效时长，单位秒")
     * @ApiReturn
    ({
    "code": 1,
    "msg": "操作成功",
    "time": 1584513627,
    "data": {
    "id": 4,
    "mobile": null,
    "email": null,
    "username": null,
    "nickname": null,
    "avatar": null,
    "token": "3a526ba6-5c39-4c5e-bf75-4365c8f85f4e",
    "user_id": 4,
    "createtime": 1584513627,
    "expiretime": 1899873627,
    "expires_in": 315360000
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
                $this->error('操作失败,'.$mobile_service->getError());
            }
            if($this->service_user->mobileCodeRegLogin($params)){
                $this->success('操作成功', $this->service_user->getUserInfo());
            }else{
                $this->error('操作失败,'.$this->service_user->getError());
            }
        }else{
            $this->error('操作失败,'.$validate->getError());
        }
    }

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
     * @ApiTitle    (邮箱密码登录)
     * @ApiSummary  (邮箱密码登录)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/user/email_login)
     * @ApiParams   (name="email", type="string", required=true, description="邮箱")
     * @ApiParams   (name="password", type="string", required=true, description="密码")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="登录成功")
     * @ApiReturn
    ({
    'code':'1',
    'msg':'登录成功'
    })
     */
    public function email_login()
    {
        if(!$this->request->isPost()){
            $this->error('请使用POST请求');
        }

        $param['email'] = $this->request->request('email');
        $param['password'] = $this->request->request('password');

        $validate = new EmailLogin();
        if($validate->check($param)){
            if($this->service_user->emailLogin($param)){
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
     * @ApiHeaders  (name=token, type=string, required=true, description="用户登录后得到的Token")
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
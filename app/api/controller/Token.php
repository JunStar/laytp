<?php

namespace app\api\controller;

use laytp\controller\Api;
use laytp\library\Random;

/**
 * Token相关
 */
class Token extends Api
{
    public $no_need_login = [];

    /**
     * @ApiTitle    (检测Token是否过期)
     * @ApiSummary  (检测Token是否过期)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/token/check)
     * @ApiHeaders  (name="token", type="string", required="true", description="用户登录后得到的Token")
     * @ApiReturnParams   (name="err_code", type="integer", description="错误码.0=没有错误，表示操作成功；1=常规错误码，客户端仅需提示msg；其他错误码与具体业务相关，其他错误码举例：10401。前端需要跳转至登录界面。")
     * @ApiReturnParams   (name="msg", type="string", description="返回描述")
     * @ApiReturnParams   (name="time", type="integer", description="请求时间，Unix时间戳，单位秒")
     * @ApiReturnParams   (name="data", type="object", description="返回的数据对象")
     * @ApiReturnParams   (name="data.token", type="string", description="用户登录凭证，token")
     * @ApiReturnParams   (name="data.expires_in", type="integer", description="token有效时间，单位秒")
     * @ApiReturn
     * ({
     * "err_code": 0,
     * "msg": "Token有效",
     * "time": 1591167181,
     * "data": {
     * "token": "827fb87e-2064-45c8-839a-128e195a7411",
     * "expires_in": 1789
     * }
     * })
     */
    public function check()
    {
        $token = $this->service_user->getToken();
        $tokenInfo = \library\Token::get($token);
        $this->success('Token有效', ['token' => $tokenInfo['token'], 'expires_in' => $tokenInfo['expires_in']]);
    }

    /**
     * @ApiTitle    (刷新Token)
     * @ApiSummary  (刷新Token)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/token/refresh)
     * @ApiHeaders  (name="token", type="string", required="true", description="用户登录后得到的Token")
     * @ApiReturnParams   (name="err_code", type="integer", description="错误码.0=没有错误，表示操作成功；1=常规错误码，客户端仅需提示msg；其他错误码与具体业务相关，其他错误码举例：10401。前端需要跳转至登录界面。")
     * @ApiReturnParams   (name="msg", type="string", description="返回描述")
     * @ApiReturnParams   (name="time", type="integer", description="请求时间，Unix时间戳，单位秒")
     * @ApiReturnParams   (name="data", type="object", description="返回的数据对象")
     * @ApiReturnParams   (name="data.token", type="string", description="用户登录凭证，token")
     * @ApiReturnParams   (name="data.expires_in", type="integer", description="token有效时间，单位秒")
     * @ApiReturn
     * ({
     * "err_code": 0,
     * "msg": "成功刷新Token",
     * "time": 1591167423,
     * "data": {
     * "token": "e356df60-ff03-4f15-bb66-c0e3ef37f335",
     * "expires_in": 1800
    * }
* })
     */
    public function refresh()
    {
        //删除源Token
        $token = $this->service_user->getToken();
        \library\Token::delete($token);
        //创建新Token
        $token = Random::uuid();
        \library\Token::set($token, $this->service_user->id, $this->service_user->token_keep_time);
        $this->success('成功刷新Token', ['token' => $token, 'expires_in' => $this->service_user->token_keep_time]);
    }
}

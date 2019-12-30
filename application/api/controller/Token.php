<?php

namespace app\api\controller;

use controller\Api;
use library\Random;

/**
 * Token接口
 */
class Token extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = '*';

    /**
     * 检测Token是否过期
     * @ApiRoute    (/api/token/check)
     */
    public function check()
    {
        $token = $this->service_user->getToken();
        $tokenInfo = \library\Token::get($token);
        $this->success('', ['token' => $tokenInfo['token'], 'expires_in' => $tokenInfo['expires_in']]);
    }

    /**
     * 刷新Token
     * @ApiRoute    (/api/token/refresh)
     */
    public function refresh()
    {
        //删除源Token
        $token = $this->service_user->getToken();
        \library\Token::delete($token);
        //创建新Token
        $token = Random::uuid();
        \library\Token::set($token, $this->service_user->id, $this->service_user->token_keep_time);
        $this->success('', ['token' => $token, 'expires_in' => $this->service_user->token_keep_time]);
    }
}

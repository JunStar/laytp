<?php

namespace app\api\middleware;

use app\api\service\AuthServiceFacade;
use app\api\service\UserServiceFacade;
use laytp\BaseMiddleware;
use think\Request;

class Auth extends BaseMiddleware
{
    /**
     * 执行中间件
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        if (AuthServiceFacade::needLogin()) {
            $initUser = UserServiceFacade::init($request->header('token'));
            if (!$initUser) return $this->error(UserServiceFacade::getError(), 10401);
            if (!UserServiceFacade::isLogin()) {
                return $this->error('登录信息已过期', 10401);
            }
        }
        return $next($request);
    }
}
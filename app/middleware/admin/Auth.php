<?php

namespace app\middleware\admin;

use app\service\admin\AuthServiceFacade;
use app\service\admin\UserServiceFacade;
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
            $initUser = UserServiceFacade::init($request->header('laytp-admin-token', $request->cookie('laytpAdminToken')));
            if (!$initUser) return $this->error(UserServiceFacade::getError(), 10401);
            if (!UserServiceFacade::isLogin()) {
                if ($request->isAjax()) {
                    return $this->error('登录信息已过期', 10401);
                } else {
                    return redirect('/admin/login.html');
                }
            }
            if (AuthServiceFacade::needAuth()) {
                $user = UserServiceFacade::getUser();
                if ($user->is_super_manager !== 1) {
                    $userId     = $user->id;
                    $plugin     = defined('LT_PLUGIN') ? LT_PLUGIN : '';
                    $controller = strtolower(str_replace("\\", ".", $request->controller()));
                    if ($plugin) {
                        $node = 'plugin/' . $plugin . '/' . $controller . '/' . $request->action();
                    } else {
                        $node = trim(app('http')->getName() . '/' . $controller . '/' . $request->action(),'/');
                    }

                    if (!AuthServiceFacade::hasAuth($userId, $node)) {
                        return $this->error('无权请求：/' . $node);
                    }
                }
            }
        }
        return $next($request);
    }
}
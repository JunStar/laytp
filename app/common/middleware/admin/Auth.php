<?php

namespace app\common\middleware\admin;

use app\common\service\admin\AuthServiceFacade;
use app\common\service\admin\UserServiceFacade;
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
        if(AuthServiceFacade::needLogin()){
            $initUser = UserServiceFacade::init($request->header('laytp-admin-token'));
            if(!$initUser) return $this->error(UserServiceFacade::getError(),10401);
            if(!UserServiceFacade::isLogin()){
                if($request->isAjax()){
                    return $this->error('登录信息已过期',10401);
                }else{
                    return redirect('/a/login.html');
                }
            }
//            if(AuthServiceFacade::needAuth()){
//                $user_id = UserServiceFacade::getUser()->id;
//                $node = app('http')->getName() . '/' . $request->controller() . '/' . $request->action();
//                if(!AuthServiceFacade::hasAuth($user_id,$node)){
//                    return $this->error('无权进行此操作',0);
//                }
//            }
        }
        return $next($request);
    }
}
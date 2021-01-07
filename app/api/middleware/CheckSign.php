<?php

namespace app\api\middleware;

use app\api\service\CheckSignServiceFacade;
use laytp\BaseMiddleware;
use laytp\traits\JsonReturn;
use think\Request;

/**
 * 检测签名中间件
 * @package app\api\middleware
 */
class CheckSign extends BaseMiddleware
{
    use JsonReturn;

    /**
     * 执行中间件
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        if (CheckSignServiceFacade::needCheckSign()) {
            if (CheckSignServiceFacade::check()) {
                return $next($request);
            } else {
                return $this->error(CheckSignServiceFacade::getError());
            }
        } else {
            return $next($request);
        }
    }
}
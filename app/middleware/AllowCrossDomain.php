<?php

namespace app\middleware;

use laytp\BaseMiddleware;
use think\Request;

/**
 * 允许跨域中间件
 * Class Auth
 * @package app\middleware
 */
class AllowCrossDomain extends BaseMiddleware
{
    /**
     * 执行中间件
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $header = [
            'Access-Control-Allow-Origin'   => '*',
            'Access-Control-Allow-Methods'  => 'GET, POST, PATCH, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers'  => '*',
        ];

        return $next($request)->header($header);
    }
}
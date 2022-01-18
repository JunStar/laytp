<?php

namespace app\middleware\api;

use app\service\api\CheckSignServiceFacade;
use app\service\ConfServiceFacade;
use laytp\BaseMiddleware;
use laytp\traits\JsonReturn;
use think\Request;

/**
 * 签名验证中间件
 *  签名生成方式：strtoupper(md5(md5($hearder['request-time']).md5(Config::get('laytp.api.signKey'))))
 *  签名验证方式：
 *      请求的header头中需要有两个参数：
 *          - request-time = 当前请求的时间，这个单位并不要求固定，甚至传的都可以不是当前请求的时间，只要sign的值是使用这个参数的值按照规定的算法生成即可
 *          - sign = md5(md5($hearder['request-time']).md5(Config::get('laytp.api.signKey')))
 *      此中间件，使用header头中request-time按照签名算法生成签名，然后与header中传递的sign参数比对，看是否一致，如果一致就通过验证，如果不一致就验证失败
 *  需要设计，有些方法无需验签。比如获取配置的接口，需要得到后台配置的Config::get('laytp.api.signKey')
 * Class CheckSign
 * @package app\middleware
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
        if (intval(ConfServiceFacade::get('system.basic.checkSign')) === 1) {
            if (CheckSignServiceFacade::needCheckSign()) {
                if (CheckSignServiceFacade::check()) {
                    return $next($request);
                } else {
                    if(env('APP_DEBUG')){
                        return $this->error('签名验证失败',0, [
                            '签名验证的规则描述' => '1. 后台系统配置->基础配置，Api签名开关需要打开，Api签名的Key需要设置（允许为空）;2. 客户端在请求Api接口时，需要在header里面传递两个参数，request-time和sign，sign的值要等于strtoupper(md5(md5(header:request-time).md5(conf:signKey)))',
                            '注意点' => '如果是使用的生成Api文档在线测试接口，请点击右上角Api请求配置将签名验证开关打开',
                            '生成签名的规则' => 'strtoupper(md5(md5(header:request-time).md5(conf:signKey)))',
                            '系统配置的生成签名的Key' => ConfServiceFacade::get('system.basic.signKey'),
                            'header部分传递的request-time值' => $request->header('request-time'),
                            'header部分传递的sign值' => $request->header('sign'),
                            '后端计算的sign值' => CheckSignServiceFacade::getError()
                        ]);
                    }
                    return $this->error(CheckSignServiceFacade::getError());
                }
            } else {
                return $next($request);
            }
        } else {
            return $next($request);
        }
    }
}
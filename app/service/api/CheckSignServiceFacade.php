<?php

namespace app\service\api;

use think\Facade;

/**
 * Api验证签名服务门面
 * @package app\api\service
 * @method static mixed setNoNeedCheckSign($noNeedCheckSign)        设置不需要验证签名的方法名数组
 * @method static mixed getNoNeedCheckSign()        获取无需验证签名的方法名数组
 * @method static mixed needCheckSign()             获取当前节点是否需要验证签名
 * @method static mixed check()             验证签名
 * @method static mixed getError()             获取错误信息
 */
class CheckSignServiceFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return CheckSign::class;
    }
}

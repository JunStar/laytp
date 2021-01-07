<?php

namespace app\api\service;

use think\Facade;

/**
 * Api权限服务门面
 * @package app\api\service
 * @method static mixed setNoNeedLogin($noNeedLogin)        设置不需要登录的方法名数组
 * @method static mixed getNoNeedLogin()        获取无需登录的方法名数组
 * @method static mixed needLogin()             获取当前节点是否需要登录
 */
class AuthServiceFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return Auth::class;
    }
}

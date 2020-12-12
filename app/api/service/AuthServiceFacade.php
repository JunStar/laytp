<?php

namespace app\api\service;

use think\Facade;

/**
 * 后台权限服务门面
 * @package app\api\service
 * @method static mixed setNoNeedLogin()        设置不需要登录的方法名数组
 * @method static mixed setNoNeedAuth()         设置不需要鉴权的方法名数组
 * @method static mixed needLogin()             当前节点是否需要登录
 * @method static mixed needAuth()              当前节点是否需要鉴权
 * @method static mixed getAuthList()           获取当前登录者拥有的权限列表
 * @method static mixed hasAuth($node)          当前登录者是否拥有某个节点的权限
 */
class AuthServiceFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return Auth::class;
    }
}

<?php

namespace app\common\service\admin;

use think\Facade;

/**
 * 后台权限服务门面
 * @package app\common\model_service\admin
 * @method static mixed setNoNeedLogin()            设置不需要登录的方法名数组
 * @method static mixed setNoNeedAuth()             设置不需要鉴权的方法名数组
 * @method static mixed needLogin()                 当前节点是否需要登录
 * @method static mixed needAuth()                  当前节点是否需要鉴权
 * @method static mixed hasAuth($user_id, $node)    某用户是否拥有某个节点的权限
 */
class AuthServiceFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return Auth::class;
    }
}

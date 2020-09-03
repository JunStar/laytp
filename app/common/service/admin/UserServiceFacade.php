<?php

namespace app\common\service\admin;

use think\Facade;

/**
 * 后台用户服务门面
 * @package app\common\model_service\admin
 * @method static mixed init() 初始化
 * @method static mixed getError() 获取错误信息
 * @method static mixed logout() 退出登录
 * @method static mixed getUserInfo() 获取登录用户信息
 * @method static mixed getUser() 获取User模型
 * @method static mixed isLogin() 获取登录状态
 * @method static mixed getToken() 获取token
 */
class UserServiceFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return User::class;
    }
}

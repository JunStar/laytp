<?php
declare (strict_types=1);

namespace plugin\core\service;

use think\Service;

/**
 * 后台用户服务提供者
 * Class AuthService
 * @package app\common\model_service\admin
 */
class UserService extends Service
{
    /**
     * 注册服务
     *
     * @return mixed
     */
    public function register()
    {
        $this->app->bind('PluginCoreUser', User::class);
    }


    /**
     * 执行服务
     *
     * @return mixed
     */
    public function boot()
    {
        //
    }
}
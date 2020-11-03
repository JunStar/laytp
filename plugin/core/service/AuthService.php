<?php
declare (strict_types=1);

namespace plugin\core\service;

use think\Service;

/**
 * 后台权限服务提供者
 * Class AuthService
 * @package plugin\core\service
 */
class AuthService extends Service
{

    /**
     * 注册服务
     *
     * @return mixed
     */
    public function register()
    {
        $this->app->bind('PluginCoreAuth', Auth::class);
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

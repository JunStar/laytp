<?php
declare (strict_types = 1);

namespace app\common\service\admin;

use think\Service;

/**
 * 后台权限服务提供者
 * Class AuthService
 * @package app\common\model_service\admin
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
        $this->app->bind('AdminAuth', Auth::class);
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

<?php
declare (strict_types=1);

namespace app\api\service;

use think\Service;

/**
 * Api用户服务提供者
 * Class UserService
 * @package app\api\service
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
        $this->app->bind('ApiUser', User::class);
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

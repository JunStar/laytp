<?php

namespace laytp\controller;

use app\middleware\api\Auth;
use app\middleware\api\CheckSign;
use app\service\api\AuthServiceFacade;
use app\service\api\CheckSignServiceFacade;
use laytp\BaseController;
use laytp\traits\JsonReturn;

/**
 * Api应用控制器基类
 * @package laytp\controller
 */
class Api extends BaseController
{
    use JsonReturn;

    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $noNeedLogin = [];

    /**
     * 无需验证签名的方法
     * @var array
     */
    protected $noNeedCheckSign = [];

    /**
     * 中间件
     * @var array
     */
    protected $middleware = [
        Auth::class,
        CheckSign::class,
    ];

    protected function initialize()
    {
        //将无需登录的方法名数组设置到权限服务中，以方便中间件获取
        AuthServiceFacade::setNoNeedLogin($this->noNeedLogin);
        //将无需验证签名的方法名数组设置到Api验证签名服务中，以方便中间件获取
        CheckSignServiceFacade::setNoNeedCheckSign($this->noNeedCheckSign);
        $this->_initialize();
    }

    // 初始化
    protected function _initialize()
    {
    }
}
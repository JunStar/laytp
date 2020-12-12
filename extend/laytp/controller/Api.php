<?php
/**
 * Api基类
 */

namespace laytp\controller;

use app\api\middleware\Auth;
use app\api\service\AuthServiceFacade;
use laytp\BaseController;
use laytp\traits\JsonReturn;

class Api extends BaseController
{
    use JsonReturn;

    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $noNeedLogin = [];

    /**
     * 中间件
     * @var array
     */
    protected $middleware = [
        Auth::class
    ];

    protected function initialize()
    {
        //将无需登录的方法名数组设置到权限服务中
        AuthServiceFacade::setNoNeedLogin($this->noNeedLogin);
        $this->_initialize();
    }

    // 初始化
    protected function _initialize(){}
}
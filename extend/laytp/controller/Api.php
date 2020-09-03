<?php
/**
 * Api基类
 */

namespace laytp\controller;

use laytp\BaseController;
use laytp\traits\JsonReturn;
use think\facade\Cookie;

class Api extends BaseController
{
    use JsonReturn;

    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $noNeedLogin = [];

    /**
     * 用户数据逻辑层
     * @var User
     */
    protected $logicUser = null;

    protected function initialize()
    {
        $this->logicUser = User::instance();
        if ($this->logicUser->isNeedLogin($this->noNeedLogin)) {
            $token = $this->request->server('HTTP_TOKEN', $this->request->request('token', Cookie::get('token')));
            $this->logicUser->init($token);
            if (!$this->logicUser->isLogin()) {
                return $this->error('请先登录', 10401);
            }
        }

        $this->_initialize();
    }

    // 初始化
    protected function _initialize(){}

    //获取请求参数,兼容application/json和application/x-www-form-urlencoded的content-type
    protected function getRequestParams(){
        if($this->request->isJson()){
            return $this->request->put();
        }else{
            return $this->request->param();
        }
    }
}
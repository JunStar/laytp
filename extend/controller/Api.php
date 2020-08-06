<?php
/**
 * Api基类
 */

namespace controller;

use app\common\service\User;
use think\Controller;
use think\exception\HttpResponseException;
use think\facade\Cookie;
use think\Request;
use think\Response;

class Api extends Controller
{
    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $no_need_login = [];

    /**
     * 用户相关服务
     * @var User
     */
    protected $service_user = null;

    /**
     * 默认响应输出类型,支持json/xml
     * @var string
     */
    protected $response_type = 'json';


    public function initialize()
    {
        // 控制器初始化
        $this->_initialize();
    }

    public function _initialize(){
        $this->service_user = User::instance();
        if ($this->service_user->isNeedLogin($this->no_need_login)) {
            $token = $this->request->header('token', $this->request->param('token', Cookie::get('token')));
            $this->service_user->init($token);
            if (!$this->service_user->isLogin()) {
                $this->error('请先登录', 10401);
            }
        }
    }

    public function success($err_msg = '', $data = null, $err_code = 0, $status_code = 200, array $header = []){
        $result = [
            'err_code' => $err_code,
            'err_msg'  => $err_msg,
            'time' => $this->request->server('REQUEST_TIME'),
            'data' => $data,
        ];
        $response = Response::create($result, $this->response_type, $status_code)->header($header);
        throw new HttpResponseException($response);
    }

    public function error($err_msg = '', $err_code = 1, $data = null,  $status_code = 200, array $header = []){
        $result = [
            'err_code' => $err_code,
            'err_msg'  => $err_msg,
            'time' => $this->request->server('REQUEST_TIME'),
            'data' => $data,
        ];
        $response = Response::create($result, $this->response_type, $status_code)->header($header);
        throw new HttpResponseException($response);
    }
}
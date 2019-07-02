<?php
/**
 * Api基类
 */

namespace controller;

use app\api\library\Auth;
use think\Controller;
use think\exception\HttpResponseException;
use think\facade\Cookie;
use think\Request;
use think\Response;

class Api extends Controller
{
    /**
     * @var Request Request 实例
     */
    protected $request;

    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $no_need_login = [];

    /**
     * 权限Auth
     * @var Auth
     */
    protected $auth = null;

    /**
     * 默认响应输出类型,支持json/xml
     * @var string
     */
    protected $response_type = 'json';

    /**
     * 构造方法
     * @access public
     * @param Request $request Request 对象
     */
    public function __construct(Request $request = null)
    {
        $this->request = is_null($request) ? Request::instance() : $request;

        // 控制器初始化
        $this->_initialize();
    }

    public function _initialize(){
        $this->auth = Auth::instance();
        if ($this->auth->is_need_login($this->no_need_login)) {
            $token = $this->request->server('HTTP_TOKEN', $this->request->request('token', Cookie::get('token')));
            $this->auth->init($token);
            if (!$this->auth->isLogin()) {
                $this->error('请先登录', null, 401);
            }
        }
    }

    public function success($msg = '', $data = null, $status_code = 200, $code = 1,  array $header = []){
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'time' => $this->request->server('REQUEST_TIME'),
            'data' => $data,
        ];
        $response = Response::create($result, $this->response_type, $status_code)->header($header);
        throw new HttpResponseException($response);
    }

    public function error($msg = '', $data = null, $status_code = 200, $code = 0, array $header = []){
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'time' => $this->request->server('REQUEST_TIME'),
            'data' => $data,
        ];
        $response = Response::create($result, $this->response_type, $status_code)->header($header);
        throw new HttpResponseException($response);
    }
}
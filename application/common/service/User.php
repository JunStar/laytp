<?php
/**
 * 用户服务
 *  注册登录以及获取登录用户信息
 */
namespace app\common\service;
use library\Token;
use library\Random;
use service\Service;
use think\Db;
use think\Exception;
use think\facade\Request;

class User extends Service
{
    protected static $instance;
    protected $_user = null;
    protected $_token = null;
    protected $_logined = null;
    public $token_keep_time = 1800;//Token默认有效时长,单位秒，30分钟

    protected $allowFields = ['id', 'username', 'nickname', 'email', 'mobile', 'avatar', 'exp'];


    /**
     * 单例
     * @param array $options
     * @return static
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * 初始化
     * @param $token
     * @return bool
     */
    public function init($token)
    {
        if(!$token){
            $this->setError('token不能为空');
            return false;
        }

        $data = Token::get($token);
        if (!$data) {
            $this->setError('token无效');
            return false;
        }

        $user_id = intval($data['user_id']);
        if ($user_id > 0) {
            $user = \app\common\model\User::get($user_id);
            if (!$user) {
                $this->setError('账号不存在');
                return false;
            }
            //用户状态 1正常 2锁定
            if ($user['status'] != 1) {
                $this->setError('账号被锁定');
                return false;
            }
            $this->_user = $user;
            $this->_logined = true;
            $this->_token = $token;

            return true;
        } else {
            $this->setError('你没有登录');
            return false;
        }
    }

    /**
     * 检测是否需要登录
     *
     * @param array $no_need_login 需要验证权限的数组
     * @return boolean
     */
    public function is_need_login($no_need_login = [])
    {
        $no_need_login = is_array($no_need_login) ? $no_need_login : explode(',', $no_need_login);
        if (!$no_need_login) {
            return true;
        }
        $no_need_login = array_map('strtolower', $no_need_login);
        $request = Request::instance();
        // 是否存在
        if (in_array(strtolower($request->action()), $no_need_login) || in_array('*', $no_need_login)) {
            return false;
        }

        // 没找到匹配
        return true;
    }

    /**
     * 用户名密码注册
     * @param $params
     * @return bool
     */
    public function usernameReg($params){
        $data = [
            'username' => $params['username'],
            'password' => password_hash($params['password'], PASSWORD_DEFAULT),
            'nickname'  => $params['username'],
            'login_time' => date('Y-m-d H:i:s'),
            'login_ip'   => request()->ip()
        ];

        Db::startTrans();
        try{
            $user = \app\common\model\User::create($data, true);
            $this->_user = \app\common\model\User::get($user->id);
            //设置Token
            $this->_token = Random::uuid();
            Token::set($this->_token, $user->id, $this->token_keep_time);
            Db::commit();
            return true;
        }catch (Exception $e){
            $this->setError($e->getFile().$e->getLine().$e->getMessage());
            Db::rollback();
            return false;
        }
    }

    /**
     * 用户名密码登录
     * @param $params
     * @return bool
     */
    public function usernameLogin($params){
        $username = $params['username'];
        $this->_user = \app\common\model\User::where('username','=', $username)->find();
        $this->_token = Random::uuid();
        $this->_logined = true;
        Token::set($this->_token, $this->_user->id, $this->token_keep_time);
        return true;
    }

    /**
     * 邮箱密码注册
     */
    public function emailReg(){

    }

    /**
     * 邮箱密码登录
     * @param $params
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function emailLogin($params){
        $email = $params['email'];
        $this->_user = \app\common\model\User::where('email','=', $email)->find();
        $this->_token = Random::uuid();
        $this->_logined = true;
        Token::set($this->_token, $this->_user->id, $this->token_keep_time);
        return true;
    }

    /**
     * 手机号+验证码 注册+登录
     * @param $params
     * @return bool
     */
    public function mobileCodeRegLogin($params){
        try{
            $user = \app\common\model\User::get(['mobile' => $params['mobile']]);
            if(!$user){
                $data = [
                    'mobile' => $params['mobile'],
                    'login_time' => date('Y-m-d H:i:s'),
                    'login_ip'   => request()->ip()
                ];

                $user = \app\common\model\User::create($data, true);
                $this->_user = \app\common\model\User::get($user->id);
            }else{
                $this->_user = $user;
            }

            //设置Token
            $this->_token = Random::uuid();
            Token::set($this->_token, $user->id, $this->token_keep_time);

        }catch (Exception $e){
            $this->setError($e->getFile().$e->getLine().$e->getMessage());
            return false;
        }
    }

    /**
     * 退出登录
     */
    public function logout(){
        if (!$this->_logined) {
            $this->setError('你没有登录');
            return false;
        }
        //设置登录标识
        $this->_logined = false;
        //删除Token
        Token::delete($this->_token);
        return true;
    }

    /**
     * 兼容调用user模型的属性
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->_user ? $this->_user->$name : null;
    }

    /**
     * 获取登录用户信息
     */
    public function getUserInfo()
    {
        $data = $this->_user->toArray();
        $allowFields = $this->getAllowFields();
        $userinfo = array_intersect_key($data, array_flip($allowFields));
        $userinfo = array_merge($userinfo, Token::get($this->_token));
        return $userinfo;
    }

    /**
     * 获取允许输出的字段
     * @return array
     */
    public function getAllowFields()
    {
        return $this->allowFields;
    }

    /**
     * 获取User模型
     * @return User
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * 判断是否登录
     * @return boolean
     */
    public function isLogin()
    {
        if ($this->_logined) {
            return true;
        }
        return false;
    }

    /**
     * 获取当前Token
     * @return string
     */
    public function getToken()
    {
        return $this->_token;
    }
}
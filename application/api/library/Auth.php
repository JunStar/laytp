<?php

namespace app\api\library;

use app\api\model\User;
use library\Date;
use library\Random;
use think\Db;
use think\Exception;
use think\facade\Hook;
use think\facade\Request;
use think\facade\Validate;

class Auth
{
    protected static $instance = null;
    protected $_error = '';
    protected $_logined = false;//登录状态
    protected $_user = null;
    protected $_token = '';
    //Token默认有效时长,单位秒，30分钟
    protected $keep_time = 1800;
    //默认配置
    protected $config = [];
    protected $options = [];
    protected $allowFields = ['id', 'username', 'nickname'];

    public function __construct($options = [])
    {
        $this->options = array_merge($this->config, $options);
    }

    /**
     *
     * @param array $options 参数
     * @return Auth
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }

        return self::$instance;
    }

    /**
     * 根据Token初始化，其实就是有Token就设置登录状态和登录信息
     *
     * @param string $token Token
     * @return boolean
     */
    public function init($token)
    {
        if ($this->_logined) {
            return true;
        }
        if ($this->_error) {
            return false;
        }
        $data = Token::get($token);
        if (!$data) {
            return false;
        }
        $user_id = intval($data['user_id']);
        if ($user_id > 0) {
            $user = User::get($user_id);
            if (!$user) {
                $this->setError('账号不存在');
                return false;
            }
            if ($user['status'] != 'normal') {
                $this->setError('账号被锁定');
                return false;
            }
            $this->_user = $user;
            $this->_logined = true;
            $this->_token = $token;

            //初始化成功的事件
            Hook::listen("user_init_success", $this->_user);

            return true;
        } else {
            $this->setError('你没有登录');
            return false;
        }
    }

    /**
     * 注册用户
     *
     * @param string $username 用户名
     * @param string $password 密码
     * @param array  $extend   扩展参数
     * @return boolean
     */
    public function register($username, $password, $extend = [])
    {
        // 检测用户名或邮箱、手机号是否存在
        if (User::getByUsername($username)) {
            $this->setError('用户名已经存在');
            return false;
        }

        $ip = request()->ip();

        $data = [
            'username' => $username,
            'password' => $password
        ];
        $params = array_merge($data, [
            'nickname'  => $username,
            'login_time' => date('Y-m-d H:i:s'),
            'login_ip'   => $ip
        ]);
        $params['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $params = array_merge($params, $extend);

        //账号注册时需要开启事务,避免出现垃圾数据
        Db::startTrans();
        try {
            $user = User::create($params, true);

            $this->_user = User::get($user->id);

            //设置Token
            $this->_token = Random::uuid();
            Token::set($this->_token, $user->id, $this->keep_time);

            //注册成功的事件
            Hook::listen("user_register_success", $this->_user, $data);
            Db::commit();
        } catch (Exception $e) {
            $this->setError($e->getFile().$e->getLine().$e->getMessage());
            Db::rollback();
            return false;
        }
        return true;
    }

    /**
     * 用户登录
     *
     * @param string $account  用户名、邮箱、手机号
     * @param string $password 密码
     * @return boolean
     */
    public function login($account, $password)
    {
        $field = Validate::is($account, 'email') ? 'email' : (Validate::regex($account, '/^1\d{10}$/') ? 'mobile' : 'username');
        $user = User::get([$field => $account]);
        if (!$user) {
            $this->setError('账号不正确');
            return false;
        }

        if ($user->status != 'normal') {
            $this->setError('账号被锁定');
            return false;
        }
        if (!password_verify($password,$user->password)) {
            $this->setError('密码错误');
            return false;
        }

        //直接登录会员
        $this->direct($user->id);

        return true;
    }

    /**
     * 直接登录账号
     * @param int $user_id
     * @return boolean
     */
    public function direct($user_id)
    {
        $user = User::get($user_id);
        if ($user) {
            Db::startTrans();
            try {
                $ip = request()->ip();

                //判断连续登录和最大连续登录
                if ($user->login_time < Date::unixtime('day')) {
                    $user->successions = $user->login_time < Date::unixtime('day', -1) ? 1 : $user->successions + 1;
                    $user->maxsuccessions = max($user->successions, $user->maxsuccessions);
                }

                //记录本次登录的IP和时间
                $user->login_ip = $ip;
                $user->login_time = date('Y-m-d H:i:s');

                $user->save();

                $this->_user = $user;

                $this->_token = Random::uuid();
                Token::set($this->_token, $user->id, $this->keep_time);

                $this->_logined = true;

                //登录成功的事件
                Hook::listen("user_login_success", $this->_user);
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                $this->setError($e->getMessage());
                return false;
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * 注销
     *
     * @return boolean
     */
    public function logout()
    {
        if (!$this->_logined) {
            $this->setError('你没有登录');
            return false;
        }
        //设置登录标识
        $this->_logined = false;
        //删除Token
        Token::delete($this->_token);
        //注销成功的事件
        Hook::listen("user_logout_success", $this->_user);
        return true;
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
     * 获取当前Token
     * @return string
     */
    public function getToken()
    {
        return $this->_token;
    }

    /**
     * 获取会员基本信息
     */
    public function getUserInfo()
    {
        $data = $this->_user->toArray();
        $allowFields = $this->getAllowFields();
        $user_info = array_intersect_key($data, array_flip($allowFields));
        $user_info = array_merge($user_info, Token::get($this->_token));
        return $user_info;
    }

    /**
     * 设置会话有效时间
     * @param int $keep_time 默认为永久
     */
    public function setKeepTime($keep_time = 0)
    {
        $this->keep_time = $keep_time;
    }

    /**
     * 设置错误信息
     *
     * @param $error 错误信息
     * @return Auth
     */
    public function setError($error)
    {
        $this->_error = $error;
        return $this;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        return $this->_error ? $this->_error : '';
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
}
<?php

namespace app\api\library;

use app\api\model\User;
use library\Random;
use think\Db;
use think\Exception;
use think\Hook;

class Auth
{
    protected $_user = null;
    protected $_token = '';
    //Token默认有效时长,单位秒，30分钟
    protected $keep_time = 1800;

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
            $this->setError($e->getMessage());
            Db::rollback();
            return false;
        }
        return true;
    }
}
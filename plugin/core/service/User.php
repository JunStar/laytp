<?php

namespace plugin\core\service;

use laytp\library\Token;
use laytp\traits\Error;

/**
 * 后台用户服务实现者
 * @package plugin\core\service
 */
class User
{
    use Error;
    protected $_user = null;//实例化的用户对象
    protected $_token = null;//用户登录凭证,token
    protected $_isLogin = null;//当前用户是否登录
    protected $userModel = null;//用户数据模型
    protected $allowFields = ['id', 'username', 'nickname', 'avatar', 'is_super_manager'];
    protected $tokenKeepTime = 365 * 24 * 60 * 60;//Token默认有效时长,单位秒,365天

    /**
     * 初始化
     * @param $token
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function init($token)
    {
        if (!$token) {
            $this->setError('token不能为空，请重新登录');
            return false;
        }

        $data = Token::get($token);
        if (!$data) {
            $this->setError('token无效，请重新登录');
            return false;
        }

        $userId = intval($data['user_id']);
        if ($userId > 0) {
            $user = \plugin\core\model\User::find($userId);
            if (!$user) {
                $this->setError('账号不存在，请重新登录');
                return false;
            }
            //用户状态 1正常 2禁用
            if ($user['status'] != 1) {
                $this->setError('账号被禁用，请联系管理员');
                return false;
            }
            $this->_user    = $user;
            $this->_isLogin = true;
            $this->_token   = $token;
            return true;
        } else {
            $this->setError('账号不存在，请重新登录');
            return false;
        }
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        if (!$this->_isLogin) {
            $this->setError('你没有登录');
            return false;
        }
        //设置登录标识
        $this->_isLogin = false;
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
        $data        = $this->_user->toArray();
        $allowFields = $this->getAllowFields();
        $userInfo    = array_intersect_key($data, array_flip($allowFields));
        $userInfo    = array_merge($userInfo, ['token' => $this->_token]);
        return $userInfo;
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
        if ($this->_isLogin) {
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
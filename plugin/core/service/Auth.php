<?php

namespace plugin\core\service;

use plugin\core\model\Menu;
use plugin\core\model\user\Role;
use laytp\traits\Error;
use think\facade\Request;

/**
 * 后台权限服务实现者
 * Class Auth
 * @package plugin\core\service
 */
class Auth
{
    use Error;
    protected $_noNeedLogin = [];//无需登录的方法名数组
    protected $_noNeedAuth = [];//无需鉴权的方法名数组

    /**
     * 设置无需登录的方法名数组
     * @param array $noNeedLogin
     */
    public function setNoNeedLogin($noNeedLogin = [])
    {
        $this->_noNeedLogin = $noNeedLogin;
    }

    /**
     * 获取无需登录的方法名数组
     * @return array
     */
    public function getNoNeedLogin()
    {
        return $this->_noNeedLogin;
    }

    /**
     * 当前节点是否需要登录
     * @param bool $noNeedLogin
     * @return bool true:需要登录,false:不需要登录
     */
    public function needLogin($noNeedLogin = false)
    {
        $noNeedLogin === false && $noNeedLogin = $this->getNoNeedLogin();
        $noNeedLogin = is_array($noNeedLogin) ? $noNeedLogin : explode(',', $noNeedLogin);
        //为空表示所有方法都需要登录，返回true
        if (!$noNeedLogin) {
            return true;
        }
        $noNeedLogin = array_map('strtolower', $noNeedLogin);
        $request     = Request::instance();
        //判断当前请求的操作名是否存在于不需要登录的方法名数组中，如果存在，表明不需要登录，返回false
        if (in_array(strtolower($request->action()), $noNeedLogin) || in_array('*', $noNeedLogin)) {
            return false;
        }

        //默认为需要登录
        return true;
    }

    /**
     * 设置无需鉴权的方法名数组
     * @param array $noNeedAuth
     */
    public function setNoNeedAuth($noNeedAuth = [])
    {
        $this->_noNeedAuth = $noNeedAuth;
    }

    /**
     * 获取无需鉴权的方法名数组
     * @return array
     */
    public function getNoNeedAuth()
    {
        return $this->_noNeedAuth;
    }

    /**
     * 当前节点是否需要鉴权
     * @param bool $noNeedAuth
     * @return bool true:需要登录,false:不需要登录
     */
    public function needAuth($noNeedAuth = false)
    {
        $noNeedAuth === false && $noNeedAuth = $this->getNoNeedAuth();
        $noNeedAuth = is_array($noNeedAuth) ? $noNeedAuth : explode(',', $noNeedAuth);
        //为空表示所有方法都需要鉴权，返回true
        if (!$noNeedAuth) {
            return true;
        }
        $noNeedAuth = array_map('strtolower', $noNeedAuth);

        //判断当前请求的操作名是否存在于不需要鉴权的方法名数组中，如果存在，表明不需要鉴权，返回false
        if (in_array(strtolower(Request::action()), $noNeedAuth) || in_array('*', $noNeedAuth)) {
            return false;
        }

        //默认为需要鉴权
        return true;
    }

    /**
     * 获取当前登录者拥有权限列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAuthList()
    {
//        $where[] = ['is_menu', '=', 1];
        $where[] = ['is_show', '=', 1];
        $user    = UserServiceFacade::getUser();
        //当前登录者如果是超级管理员，则拥有所有的权限列表
        if ($user->is_super_manager === 1) {
            $result = Menu::where($where)->select()->toArray();
        } else {
            //当前登录者如果不是超级管理员，先查询拥有哪些角色，通过角色查询出权限节点列表
            $plugin_core_user_id = UserServiceFacade::getUser()->id;
            $role_ids            = Role::where('plugin_core_user_id', '=', $plugin_core_user_id)->column('plugin_core_role_id');
            $menu_ids            = \plugin\core\model\role\Menu::where('plugin_core_role_id', 'in', $role_ids)->column('plugin_core_menu_id');
            $where[]             = ['id', 'in', $menu_ids];
            $result              = Menu::where($where)->select()->toArray();
        }
        return $result;
    }

    /**
     * 获取某用户是否有某节点的权限
     * @param integer $user_id 登录用户ID
     * @param string $node 节点字符串
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function hasAuth($user_id, $node)
    {
        if (!$user_id || !$node) return false;
        $authList = $this->getAuthList($user_id);
        $authArr  = [];
        foreach ($authList as $k => $v) {
            $authArr[] = trim($v['rule'], '/');
        }

        $authArr = array_filter(array_unique($authArr));
        sort($authArr);
        if (in_array($node, $authArr)) {
            return true;
        } else {
            return false;
        }
    }
}
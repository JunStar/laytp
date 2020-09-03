<?php
namespace app\admin\controller;

use laytp\controller\Backend;

/**
 * 管理员管理
 * Class User
 * @package app\admin\controller
 */
class User extends Backend
{
//    protected $noNeedLogin = ['info'];
    /**
     * 根据token获取登录用户信息
     * @param \app\common\service\admin\User $user
     * @return \think\response\Json
     */
    public function info(\app\common\service\admin\User $user){
        return $this->success('获取成功',$user->getUserInfo());
    }
}

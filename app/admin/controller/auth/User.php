<?php
namespace app\admin\controller\auth;

use laytp\controller\Backend;

/**
 * 后台管理员控制器
 */
class User extends Backend
{
    protected $model;//当前模型对象

    protected function _initialize(){
        $this->model = new \app\common\model\admin\User();
    }

    /**
     * 根据token获取登录用户信息
     * @param \app\common\service\admin\User $user
     * @return \think\response\Json
     */
    public function info(\app\common\service\admin\User $user){
        return $this->success('获取成功',$user->getUserInfo());
    }
}
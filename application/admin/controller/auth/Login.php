<?php
/**
 * 菜单
 */
namespace app\admin\controller\auth;

use think\Controller;
use think\facade\Session;

class Login extends Controller
{
    //首页
    public function index()
    {
        $session = Session::get('admin_user_id');
        if( $session )
        {
            return $this->redirect(url('/admin/'));
        }
        return $this->fetch();
    }

    //登录
    public function do_login(){
        $validate = new \app\admin\validate\auth\Login;
        $param = $this->request->param();
        if (!$validate->check($param))
            return $this->error($validate->getError());
        //设置SESSION
        $session = model('auth.User')->where('username','=',$param['username'])->value('id');
        Session::set('admin_user_id', $session);
        return $this->success('登录成功');
    }

    //退出登录
    public function logout(){
        Session::clear();
        return $this->redirect(url('/admin/auth.login/index'));
    }
}
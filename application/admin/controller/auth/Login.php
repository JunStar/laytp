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
        $session = Session::get('user');
        if( isset( $session['id'] ) && isset( $session['name'] ) )
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
        $session = model('auth.AdminUser')->field('id,name')->getByName($param['name']);
        Session::set('user', $session);
        return $this->success('登录成功');
    }

    //退出登录
    public function logout(){
        Session::clear();
        return $this->redirect(url('/admin/auth.login/index'));
    }
}
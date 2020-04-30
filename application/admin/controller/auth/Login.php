<?php
/**
 * 菜单
 */
namespace app\admin\controller\auth;

use library\Random;
use library\Token;
use think\Controller;
use think\facade\Cookie;

class Login extends Controller
{
    //登录界面
    public function index(){
        $token = $this->request->server('HTTP_TOKEN', $this->request->request('token', Cookie::get('token')));
        if( $token ){
            $data = Token::get($token);
            if(isset($data['user_id'])){
                return $this->redirect(url('/admin?ref=1'));
            }
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
        $user_id = model('auth.User')->where('username','=',$param['username'])->value('id');
        $token = Random::uuid();
        Token::set($token, $user_id, 24 * 60 * 60 * 3);
        return $this->success('登录成功','',['token'=>$token]);
    }

    //退出登录
    public function logout(){
        $token = $this->request->server('HTTP_TOKEN', $this->request->request('token', Cookie::get('token')));
        Token::delete($token);
        return $this->redirect(url('/admin/auth.login/index'));
    }
}
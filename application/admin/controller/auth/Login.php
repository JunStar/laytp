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
        $token = $this->request->server('HTTP_ADMIN_TOKEN', $this->request->request('admin_token', Cookie::get('admin_token')));
        if( $token ){
            $data = Token::get($token);
            if(isset($data['user_id'])){
                return $this->redirect(url('/admin?ref=1'));
            }
        }
        $referer = $this->request->server('HTTP_REFERER');
        $host = $this->request->server('HTTP_HOST');
        if("http://".$host.'/' == $referer || "https://".$host.'/' == $referer){
            $referer = $referer.'admin';
        }
        $normal_logout = Cookie::get('normal_logout');
        if(!$normal_logout && $referer){
            $parse_url = parse_url($referer);
            $query = '';
            if(isset($parse_url['query'])){
                $query = str_replace('=','/',$parse_url['query']);
                $query = str_replace('ref','laytp_menu_id',$query);
            }
            $this->assign('referer',$parse_url['scheme'].'://'.$parse_url['host'].$parse_url['path'].'/'.$query);
        }else{
            $this->assign('referer','');
        }
        Cookie::set('normal_logout',0);
        return $this->fetch();
    }

    //登录
    public function do_login(){
        $validate = new \app\admin\validate\auth\Login;
        $param = $this->request->param();
        if (!$validate->check($param))
            return $this->error($validate->getError());
        //设置登录信息
        $user_id = model('auth.User')->where('username','=',$param['username'])->value('id');
        $token = Random::uuid();
        Token::set($token, $user_id, 24 * 60 * 60 * 3);
        return $this->success('登录成功',$param['referer'],['admin_token'=>$token]);
    }

    //退出登录
    public function logout(){
        $token = $this->request->server('HTTP_ADMIN_TOKEN', $this->request->request('admin_token', Cookie::get('admin_token')));
        Token::delete($token);
        Cookie::set('normal_logout','1');
        return $this->redirect(url('/admin/auth.login/index'));
    }
}
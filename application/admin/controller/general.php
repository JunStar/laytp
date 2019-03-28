<?php
namespace app\admin\controller;

use controller\Backend;
use think\Exception;

class general extends Backend
{
    public function profile(){
        if( $this->request->isAjax() && $this->request->isPost() ){
            $post = filterPostData($this->request->post("row/a"));
            if( $post['password'] != $post['re_password']){
                return $this->error('两次密码输入不相同');
            }
            $post['password'] = password_hash($post['password'], PASSWORD_DEFAULT);
            unset($post['re_password']);
            try{
                $update_res = model('auth.User')->where('id','=',$this->admin_user->id)->update($post);
                if( $update_res || $update_res === 0 ){
                    return $this->success('操作成功');
                }else if( $update_res === null ){
                    return $this->error('操作失败');
                }
            }catch (Exception $e){
                return $this->error($e->getMessage());
            }
        }
        return $this->fetch();
    }
}
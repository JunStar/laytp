<?php

namespace app\admin\controller\auth;

use controller\Backend;

/**
 * 后台管理员表
 */
class User extends Backend
{

    /**
     * admin_user模型对象
     * @var app\admin\model\admin\User
     */
    protected $model;

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\auth\User();
    }

    //添加
    public function add()
    {
        if( $this->request->isAjax() && $this->request->isPost() ){
            $post = filterPostData($this->request->post("row/a"));
            $post['password'] = password_hash($post['password'], PASSWORD_DEFAULT);
            $post['avatar'] = $post['avatar'] ? $post['avatar'] : '/static/admin/image/default_avatar.png';
            $post['create_time'] = date('Y-m-d H:i:s');
            if( $this->model->insert($post) ){
                return $this->success('操作成功');
            }else{
                return $this->error('操作失败');
            }
        }
        return $this->fetch();
    }

    //编辑
    public function edit()
    {
        $edit_where['id'] = $this->request->param('id');

        if( $this->request->isAjax() && $this->request->isPost() ){
            $post = filterPostData($this->request->post("row/a"));
            if($post['password']){
                $post['password'] = password_hash($post['password'], PASSWORD_DEFAULT);
            }else{
                unset($post['password']);
            }
            $update_res = $this->model->where($edit_where)->update($post);
            if( $update_res ){
                return $this->success('操作成功');
            }else if( $update_res === 0 ){
                return $this->success('未做修改');
            }else if( $update_res === null ){
                return $this->error('操作失败');
            }
        }

        $assign = $this->model->where($edit_where)->find()->toArray();
        $this->assign($assign);
        return $this->fetch();
    }
}

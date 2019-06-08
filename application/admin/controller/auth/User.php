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
    protected $upload_field;

    public function initialize(){
        parent::initialize();
        $this->model = new \app\admin\model\auth\User();

        $this->upload_field = [
            'avatar'=>'images'
        ];

        $this->assign('has_soft_del',0);//是否拥有回收站功能
        $this->assign('has_del',1);//是否拥有删除功能
    }

    //添加
    public function add(){
        if( $this->request->isAjax() && $this->request->isPost() ){
            $post = filterPostData($this->request->post("row/a"));
            if( $post['password'] != $post['re_password']){
                return $this->error('两次密码输入不相同');
            }
            $post['password'] = password_hash($post['password'], PASSWORD_DEFAULT);
            $post['avatar'] = $post['avatar'] ? $post['avatar'] : '/static/admin/image/default_avatar.png';
            if( $this->model->save($post) ){
                $role_ids = explode( ',', $post['role_ids'] );
                $data = [];
                foreach( $role_ids as $k=>$v ){
                    $data[] = ['role_id' => $v, 'admin_id' => $this->model->id];
                }
                model('auth.RoleRelUser')->saveAll($data);
                return $this->success('操作成功');
            }else{
                return $this->error('操作失败');
            }
        }
        return $this->fetch();
    }

    //编辑
    public function edit(){
        $edit_where['id'] = $this->request->param('id');
        $rel_model = model('auth.RoleRelUser');

        if( $this->request->isAjax() && $this->request->isPost() ){
            $post = filterPostData($this->request->post("row/a"));
            if( $post['password'] != $post['re_password']){
                return $this->error('两次密码输入不相同');
            }
            $role_ids = explode( ',', $post['role_ids'] );
            unset($post['role_ids']);
            if($post['password']){
                $post['password'] = password_hash($post['password'], PASSWORD_DEFAULT);
                unset($post['re_password']);
            }else{
                unset($post['password']);
                unset($post['re_password']);
            }
            $update_res = $this->model->where($edit_where)->update($post);
            if( $update_res || $update_res === 0 ){
                $rel_model->where('admin_id','=',$edit_where['id'])->delete();
                $data = [];
                foreach( $role_ids as $k=>$v ){
                    $data[] = ['role_id' => $v, 'admin_id' => $edit_where['id']];
                }
                $rel_model->saveAll($data);
                return $this->success('操作成功');
            }else if( $update_res === null ){
                return $this->error('操作失败');
            }
        }

        $assign = $this->model->where($edit_where)->find()->toArray();
        $assign['role_ids'] = implode( ',', $rel_model->where('admin_id','=',$edit_where['id'])->column('role_id') );
        $this->assign($assign);
        return $this->fetch();
    }
}

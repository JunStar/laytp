<?php

namespace library\traits;

use app\admin\model\auth\User;
use think\Exception;

trait Backend
{
    public function select_page(){
        if( $this->request->isAjax() ){
            $where = $this->select_page_build_params();
            $limit = $this->request->param('pageSize');
            $limit = $limit ? $limit : 20;
            $data = $this->model->where($where)->order('id desc')->paginate($limit)->toArray();
            return select_page_data($data);
        }
    }

    //查看
    public function index(){
        if( $this->request->isAjax() ){
            $where = $this->build_params();
            $select_page = $this->request->param('select_page');
            $limit = $select_page ? $this->request->param('pageSize') : $this->request->param('limit');
            $data = $this->model->where($where)->order('id desc')->paginate($limit)->toArray();
            return $select_page ? select_page_data($data) : layui_table_page_data($data);
        }
        return $this->fetch();
    }

    //添加
    public function add(){
        if( $this->request->isAjax() && $this->request->isPost() ){
            $post = filterPostData($this->request->post("row/a"));
            if( $this->model->create($post) ){
                return $this->success('操作成功');
            }else{
                return $this->error('操作失败');
            }
        }
        return $this->fetch();
    }

    //编辑
    public function edit(){
        $id = $this->request->param('id');
        $info = $this->model->get($id);
        if( $this->request->isAjax() && $this->request->isPost() ){
            $post = filterPostData($this->request->post("row/a"));
            foreach($post as $k=>$v){
                $info->$k = $v;
            }
            $update_res = $info->save();
            if( $update_res ){
                return $this->success('操作成功');
            }else if( $update_res === 0 ){
                return $this->success('未做修改');
            }else if( $update_res === null ){
                return $this->error('操作失败');
            }
        }

        $this->assign($info->toArray());
        return $this->fetch();
    }

    //设置状态
    public function set_status(){
        $field = $this->request->param('field');
        $field_val = $this->request->param('field_val');
        $save[$field] = $field_val;
        try{
            if( $this->model->where('id','in',$this->request->param('ids'))->update($save) ){
                return $this->success('操作成功');
            }else{
                return $this->error('操作失败');
            }
        }catch (Exception $e){
            return $this->error($e->getMessage());
        }
    }

    //删除
    public function del(){
        $ids = $this->request->param('ids');
        if(!$this->has_del){
            return $this->error('控制器没有删除功能');
        }else{
            if($this->has_soft_del){
                if( $this->model->destroy($ids) ){
                    return $this->success('操作成功');
                }else{
                    return $this->error('操作失败');
                }
            }else{
                if( $this->model->where('id','in',$ids)->delete() ){
                    return $this->success('操作成功');
                }else{
                    return $this->error('操作失败2');
                }
            }
        }
    }

    //回收站
    public function recycle(){
        if( $this->request->isAjax() ){
            $where = $this->build_params();
            $limit = $this->request->param('limit');
            $data = $this->model->onlyTrashed()->where($where)->order('id desc')->paginate($limit)->toArray();
            return layui_table_page_data($data);
        }
        return $this->fetch();
    }

    //还原
    public function renew(){
        $where[] = ['id','in',$this->request->param('ids')];
        if( $this->model->restore($where) ){
            return $this->success('操作成功');
        }else{
            return $this->error('操作失败');
        }
    }

    //彻底删除
    public function true_del(){
        $ids = $this->request->param('ids');
        $res = $this->model->onlyTrashed()->where('id','in',$ids)->all();
        foreach($res as $key=>$item){
            $item->delete(true);
        }
        return $this->success('操作成功');
    }
}
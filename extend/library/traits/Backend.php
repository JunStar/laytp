<?php

namespace library\traits;

use think\Exception;

trait Backend
{
    //查看
    public function index(){
        if( $this->request->isAjax() ){
            $where = $this->build_params();
            $limit = $this->request->param('limit');
            $data = $this->model->where($where)->order('id desc')->paginate($limit)->toArray();
            return layui_table_page_data($data);
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
            if( $this->model->where('id','in',$this->request->param('id'))->update($save) ){
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
        if( $this->model->destroy($ids) ){
            return $this->success('操作成功');
        }else{
            return $this->error('操作失败');
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
        if( $this->model->destroy($ids,true) ){
            return $this->success('操作成功');
        }else{
            return $this->error('操作失败');
        }
    }

    //前端select_page的js插件调用的ajax方法
    public function select_page(){
        if( $this->request->isAjax() ) {
            $where = $this->build_select_page_params();
            $limit = $this->request->param('pageSize');
            $data = $this->model->where($where)->order('id desc')->paginate($limit)->toArray();
            return select_page_data($data);
        }
    }
}
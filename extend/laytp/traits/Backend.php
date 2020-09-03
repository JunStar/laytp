<?php

namespace laytp\traits;

use think\Exception;
use think\facade\Validate;

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
        $where = $this->buildSearchParams();
        $order = $this->buildOrder();
        $select_page = $this->request->param('select_page');
        $limit = $select_page ? $this->request->param('pageSize') : $this->request->param('limit');
        $data = $this->model->where($where)->order($order)->paginate($limit);
        return $this->success('数据获取成功',$data);
    }

    //添加
    public function add(){
        $post = filter_post_data($this->request->post());
        if(isset($post['__token__']) && !Validate::token($post['__token__'],'__token__',$post)){
            $this->error('请勿重复提交');
        }
        if( $this->model->create($post) ){
            return $this->success('添加成功',$post);
        }else{
            return $this->error('操作失败');
        }
    }

    //编辑
    public function edit(){
        $id = $this->request->param('id');
        $info = $this->model->get($id);
        $post = filter_post_data($this->request->post());
        if(isset($post['__token__']) && !Validate::token($post['__token__'],'__token__',$post)){
            $this->error('请勿重复提交');
        }
        foreach($post as $k=>$v){
            $info->$k = $v;
        }
        $update_res = $info->save();
        if( $update_res ){
            return $this->success('编辑成功');
        }else if( $update_res === 0 ){
            return $this->success('未做修改');
        }else if( $update_res === null ){
            return $this->error('操作失败');
        }
    }

    //设置正常数据状态
    public function setStatus(){
        $ids = explode(',',$this->request->param('ids'));
        $field = $this->request->param('field');
        $field_val = $this->request->param('field_val');
        $save[$field] = $field_val;
        try{
            $res = $this->model->where('id','in',$ids)->update($save);
            if( $res !== false ){
                return $this->success('操作成功');
            }else{
                return $this->error('操作失败');
            }
        }catch (Exception $e){
            return $this->error($e->getMessage());
        }
    }

    //设置回收站数据状态
    public function setRecycleStatus(){
        $ids = explode(',',$this->request->param('ids'));
        $field = $this->request->param('field');
        $field_val = $this->request->param('field_val');
        $save[$field] = $field_val;
        try{
            $res = $this->model->onlyTrashed()->where('id','in',$ids)->update($save);
            if( $res !== false ){
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
        $ids = array_filter(explode(',',$this->request->param('ids')));
        if(!$ids){
            return $this->error('参数ids不能为空');
        }
        if( $this->model->destroy($ids) ){
            return $this->success('数据删除成功');
        }else{
            return $this->error('数据删除失败');
        }
    }

    //回收站
    public function recycle(){
//        $where = $this->build_params();
//        $order = $this->build_order();
        $limit = $this->request->param('limit');
        $data = $this->model->onlyTrashed()->paginate($limit);
        return $this->success('回收站数据获取成功',$data);
    }

    //还原
    public function restore(){
        $where[] = ['id','in',$this->request->param('ids')];
        if( $this->model->restore($where) ){
            return $this->success('数据成功还原');
        }else{
            return $this->error('操作失败');
        }
    }

    //彻底删除
    public function trueDel(){
        $ids = $this->request->param('ids');
        $res = $this->model->onlyTrashed()->where('id','in',$ids)->select();
        foreach($res as $key=>$item){
            $item->force()->delete();
        }
        return $this->success('数据已经彻底删除');
    }
}
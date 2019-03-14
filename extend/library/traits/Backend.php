<?php

namespace library\traits;

trait Backend
{
    //查看
    public function index()
    {
        if( $this->request->isAjax() ){
            $where = $this->build_params();
            $limit = $this->request->param('limit');
            $data = $this->model->where($where)->order('id desc')->paginate($limit)->toArray();
            foreach($data['data'] as $k=>$v){
                foreach($v as $field_name => $field_val){
                    if(isset($this->model->const[$field_name])){
                        $data['data'][$k][$field_name] = get_const_val($field_name, $field_val, $this->model->const);
                    }elseif (isset($this->relation[$field_name]) && $field_val){
                        $data['data'][$k][$field_name] = join(',', $this->relation[$field_name]['model']->where('id in ('. $field_val .')')->column($this->relation[$field_name]['show_field']));
                    }
                }
            }
            return layui_table_page_data($data);
        }
        return $this->fetch();
    }

    //添加
    public function add()
    {
        if( $this->request->isAjax() && $this->request->isPost() ){
            $post = filterPostData($this->request->post("row/a"));
            if( $this->model->insert($post) ){
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

        if( $this->request->isAjax() && $this->request->isPost() ){
            $post = filterPostData($this->request->post("row/a"));
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

    //删除
    public function del(){
        $del_where['id'] = $this->request->param('id');
        if( $this->model->where($del_where)->delete() ){
            return $this->success('操作成功');
        }else{
            return $this->error('操作失败');
        }
    }
}
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
//            $data['data'] = $this->prettifyList($data['data']);
            return layui_table_page_data($data);
        }
        return $this->fetch();
    }

    /**
     * 美化列表
     * @param $data
     * @return mixed
     */
    public function prettifyList($data){
        if(!count($data)){
            return $data;
        }

        foreach($data as $k=>$v){
            foreach($v as $field_name => $field_val){
                if(isset($this->model->const[$field_name])){
                    $data[$k][$field_name] = get_const_val($field_name, $field_val, $this->model->const);
                }elseif (isset($this->upload_field) && in_array( $field_name, array_keys($this->upload_field) )){
                    switch($this->upload_field[$field_name]){
                        case 'images':
                            $temp = '';
                            if($field_val){
                                foreach(explode(',', $field_val ) as $kk=>$vv ){
                                    $temp .= '<a target="_blank" href="'.$vv.'"><img src="'.$vv.'" style="width:30px;height:30px;" /></a> ';
                                }
                            }
                            $data[$k][$field_name] = $temp;
                            break;
                        case 'video':
                            $temp = '';
                            if($field_val) {
                                $i = 1;
                                foreach (explode(',', $field_val) as $kk => $vv) {
                                    $temp .= '<a href="javascript:void(0);" class="popup-frame" data-name="查看视频" data-open="'.url('admin/ajax/show_video',['path'=>base64_encode($vv)]).'">视频'.$i.'</a> ';
                                    $i++;
                                }
                            }
                            $data[$k][$field_name] = $temp;
                            break;
                        case 'audio':
                            $temp = '';
                            if($field_val) {
                                foreach (explode(',', $field_val) as $kk => $vv) {
                                    $temp .= '<audio src="' . $vv . '" width="200px" height="30px" controls="controls"></audio>';
                                }
                            }
                            $data[$k][$field_name] = $temp;
                            break;
                        case 'file':
                            $temp = [];
                            if($field_val) {
                                $i = 1;
                                foreach (explode(',', $field_val) as $kk => $vv) {
                                    $temp[] = '<a href="javascript:void(0);" download="' . $vv . '" title="点击下载">文件'.$i.'</a> ';
                                    $i++;
                                }
                            }
                            $data[$k][$field_name] = implode(' ', $temp );
                            break;
                    }
                }
            }
        }
        return $data;
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
            $data = $this->model->onlyTrashed()->with(['province','category'])->where($where)->order('id desc')->paginate($limit)->toArray();
            $data['data'] = $this->prettifyList($data['data']);
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
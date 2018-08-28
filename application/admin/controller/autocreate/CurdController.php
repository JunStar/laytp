<?php
/**
 * 一键生成Curd
 */
namespace app\admin\controller\autocreate;

use controller\BasicAdmin;

class CurdController extends BasicAdmin
{
    public function initialize(){
        parent::initialize();
        $this->model = model('Curd');
        $where = $this->build_params();
        $limit = $this->request->param('limit');
        $this->data = $this->model->where($where)->paginate($limit)->toArray();
    }

    public function index(){
        if( $this->request->isAjax() ){
            return layui_table_page_data($this->data);
        }
        return $this->fetch();
    }

    public function add()
    {
        if( $this->request->isAjax() && $this->request->isPost() ){
            $post = $this->request->post("row/a");
            if( $this->model->addData($post) ){
                return $this->success('操作成功');
            }else{
                return $this->error('操作失败');
            }
        }
        return $this->fetch();
    }

    //设置字段信息
    public function set_fields(){
        if( $this->request->isAjax() && $this->request->isPost() ){
            $post = $this->request->post("row/a");
            if( $this->model->setFields($post) ){
                return $this->success('操作成功');
            }else{
                return $this->error('操作失败');
            }
        }
        return $this->fetch();
    }
}
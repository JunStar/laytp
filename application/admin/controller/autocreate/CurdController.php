<?php
/**
 * 一键生成Curd
 */
namespace app\admin\controller\autocreate;

use app\admin\validate\autocreate\import;
use controller\BasicAdmin;

class CurdController extends BasicAdmin
{
    public function initialize(){
        parent::initialize();
        $this->model = model('AutocreateCurd');
    }

    public function index(){
        if( $this->request->isAjax() ){
            $where = $this->build_params();
            $limit = $this->request->param('limit');
            $data = $this->model->where($where)->paginate($limit)->toArray();
            return layui_table_page_data($data);
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

    public function import()
    {
        if( $this->request->isAjax() && $this->request->isPost() ){
            $post = $this->request->post("row/a");
            $post_data = explode('：', $post['table_name']);
            $add_data['table_comment'] = $post_data[0];
            $add_data['table_name'] = $post_data[1];
            $validate = new \app\admin\validate\autocreate\curd\import();
            if (!$validate->check($add_data))
            {
                return $this->error($validate->getError());
            }
            if( $this->model->addData($add_data) ){
                return $this->success('操作成功');
            }else{
                return $this->error('操作失败');
            }
        }
        //获取所有的数据库名称
        $assign['table_list'] = model('InformationSchema')->getTableList();
        $this->assign($assign);
        return $this->fetch();
    }

    //页面设置
    public function set_page(){
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

    public function del(){
        $del_where['id'] = $this->request->param('id');
        if( $this->model->where($del_where)->delete() ){
            return $this->success('操作成功');
        }else{
            return $this->error('操作失败');
        }
    }
}
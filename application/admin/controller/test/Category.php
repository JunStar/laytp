<?php

namespace app\admin\controller\test;

use controller\Backend;
use library\Tree;

/**
 * 一键生成Curd测试分类模型
 */
class Category extends Backend
{
    /**
     * test_category模型对象
     * @var app\admin\model\test\Category
     */
    protected $model;
    public $has_del=1;
	public $has_soft_del=1;//是否拥有软删除功能

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\test\Category();
		$order['sort'] = 'desc';
        $order['id'] = 'asc';
        $data = $this->model->order($order)->select()->toArray();
        $tree_obj = Tree::instance();
        $tree_obj->init($data);
        $this->parent_list = $tree_obj->getTreeList($tree_obj->getTreeArray(0));
        $this->assign('parent_list', $this->parent_list);
    }

    public function select_page(){
        if( $this->request->isAjax() ){
            $limit = 10000;
		$order['sort'] = 'desc';
            $order['id'] = 'asc';
            $data = $this->model->order($order)->paginate($limit)->toArray();
            $tree_obj = Tree::instance();
            $tree_obj->init($data['data']);
            $data['data'] = $tree_obj->getTreeList($tree_obj->getTreeArray(0));
            return select_page_data($data);
        }
    }

    public function index()
    {
        if( $this->request->isAjax() ){
            return layui_table_data($this->parent_list);
        }
        return $this->fetch();
    }

    //编辑
    public function edit(){
        $id = $this->request->param('id');
        $info = $this->model->get($id);
        if( $this->request->isAjax() && $this->request->isPost() ){
            $post = filterPostData($this->request->post("row/a"));
            if($id == $post['pid']){
                return $this->error('不能将上级改成自己');
            }
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

    //回收站
    public function recycle()
    {
        if( $this->request->isAjax() ){
		$order['sort'] = 'desc';
            $order['id'] = 'asc';
            $data = $this->model->onlyTrashed()->order($order)->select()->toArray();
            return layui_table_data($data);
        }
        return $this->fetch();
    }
}

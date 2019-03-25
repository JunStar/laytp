<?php
/**
 * 菜单
 */
namespace app\admin\controller\auth;

use controller\Backend;
use library\Tree;

class Menu extends Backend
{
    public $menu_list;
    public $model;

    public function initialize(){
        parent::initialize();
        $this->model = model('auth.Menu');
        $where = $this->build_params();
        $sort = "sort desc";
        $data = $this->model->where($where)->order($sort)->select()->toArray();
        $menu_tree_obj = Tree::instance();
        $menu_tree_obj->init($data);
        $this->menu_list = $menu_tree_obj->getTreeList($menu_tree_obj->getTreeArray(0));
        $this->assign('menu_list', $this->menu_list);
    }

    public function index()
    {
        if( $this->request->isAjax() ){
            return layui_table_data($this->menu_list);
        }
        return $this->fetch();
    }

    //设置状态
    public function set_status(){
        $where['id'] = $this->request->param('id');
        $field = $this->request->param('field');
        $field_val = $this->request->param('field_val');
        $save[$field] = $field_val;
        if( model('Menu')->where($where)->update($save) ){
            return $this->success('操作成功');
        }else{
            return $this->error('操作失败');
        }
    }
}

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
    public $has_del=1;

    public function initialize(){
        parent::initialize();
        $this->model = model('auth.Menu');
        $is_menu = $this->request->param('is_menu');
        $where = [];
        if($is_menu != 'all'){
            $where['is_menu'] = 1;
        }
        $data = $this->model->where($where)->order('sort','desc')->select()->toArray();
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

    //设置状态
    public function set_status(){
        $where['id'] = $this->request->param('id');
        $field = $this->request->param('field');
        $field_val = $this->request->param('field_val');
        $save[$field] = $field_val;
        if( model('auth.Menu')->where($where)->update($save) ){
            return $this->success('操作成功');
        }else{
            return $this->error('操作失败');
        }
    }
}

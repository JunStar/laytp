<?php
/**
 * 菜单
 */
namespace app\admin\controller\auth;

use controller\Backend;
use library\Tree;

class Role extends Backend
{
    public $role_list;
    public $model;

    public function initialize(){
        parent::initialize();
        $this->model = model('auth.Role');
        $where = $this->build_params();
        $data = model('auth.Role')->where($where)->order('id desc')->select()->toArray();
        $role_tree_obj = Tree::instance();
        $role_tree_obj->init($data);
        $this->role_list = $role_tree_obj->getTreeList($role_tree_obj->getTreeArray(0));
        $this->assign('role_list', $this->role_list);
    }

    public function index()
    {
        if( $this->request->isAjax() ){
            return layui_table_data($this->role_list);
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

        $menu_list = model('auth.Menu')->field('id,pid,name')->order('id desc')->select()->toArray();
        $node_list = [];
        foreach($menu_list as $k=>$v){
            $parent = $v['pid'] ? $v['pid'] : '#';
            $node_list[] = ['id'=>$v['id'],'parent'=>$parent,'text'=>$v['name'],'type'=>'menu','state'=>['selected'=>false]];
        }
        $this->assign('node_list', $node_list);
        return $this->fetch();
    }
}

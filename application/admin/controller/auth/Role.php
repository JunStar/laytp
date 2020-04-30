<?php
/**
 * 菜单
 */
namespace app\admin\controller\auth;

use controller\Backend;
use library\Tree;
use think\Db;
use think\Exception;

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

    public function select_page(){
        if( $this->request->isAjax() ){
            $limit = 10000;
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
            return layui_table_data($this->role_list);
        }
        return $this->fetch();
    }

    //添加
    public function add()
    {
        if( $this->request->isAjax() && $this->request->isPost() ){
            Db::startTrans();
            try{
                $post = filterPostData($this->request->post("row/a"));
                $role_info = $this->model->getByName($post['name']);
                if($role_info){
                    return $this->error('角色名已存在');
                }
                $menu_ids = explode( ',', $post['menu_ids'] );
                unset($post['menu_ids']);
                $result[] = $this->model->save($post);
                $data = [];
                $menu_list = model('auth.Menu')->field('id,pid,name as title')->order('sort','desc')->select()->toArray();
                $tree_obj = Tree::instance();
                $menu_tree_obj = $tree_obj->init($menu_list);
                foreach( $menu_ids as $k=>$v ){
                    if( $v != 0 ){
                        if(!$menu_tree_obj->getChildren($v)){
                            $data[] = ['menu_id' => $v, 'role_id' => $this->model->id];
                        }
                    }
                }
                if($data){
                    $result[] = model('auth.RoleRelMenu')->saveAll($data);
                }
                if( check_res($result) ){
                    Db::commit();
                    return $this->success('操作成功');
                }else{
                    Db::rollback();
                    return $this->error('操作失败');
                }
            }catch (Exception $e){
                Db::rollback();
                return $this->error($e->getMessage());
            }
        }

        $menu_list = model('auth.Menu')->field('id,pid,name as title')->order('sort',' desc')->select()->toArray();
        $tree_obj = Tree::instance();
        $menu_tree_obj = $tree_obj->init($menu_list);
        $node_list = $menu_tree_obj->getTreeArray(0);
        $this->assign('node_list', $node_list);
        return $this->fetch();
    }

    //编辑
    public function edit(){
        $edit_where['id'] = $this->request->param('id');

        if( $this->request->isAjax() && $this->request->isPost() ){
            $post = filterPostData($this->request->post("row/a"));
            if($edit_where['id'] == $post['pid']){
                return $this->error('父级不能是自己');
            }

            $role_info = $this->model->getByName($post['name']);
            if($role_info && ($role_info['id'] != $edit_where['id'])){
                return $this->error('角色名已存在');
            }

            Db::startTrans();
            try{
                $menu_ids = explode( ',', $post['menu_ids'] );
                unset($post['menu_ids']);
                $update_res = $this->model->where($edit_where)->update($post);
                if( $update_res || $update_res === 0 ){
                    $result[] = true;
                }else if( $update_res === null ){
                    return $result[] = false;
                }
                $data = [];
                $menu_list = model('auth.Menu')->field('id,pid,name as title')->order('sort','desc')->select()->toArray();
                $tree_obj = Tree::instance();
                $menu_tree_obj = $tree_obj->init($menu_list);
                foreach( $menu_ids as $k=>$v ){
                    if( $v != 0 ){
                        if(!$menu_tree_obj->getChildren($v)){
                            $data[] = ['menu_id' => $v, 'role_id' => $edit_where['id']];
                        }
                    }
                }
                $res = model('auth.RoleRelMenu')->where('role_id','=',$edit_where['id'])->delete();
                $result[] = ($res === false) ? false : true;
                $res = model('auth.RoleRelMenu')->saveAll($data);
                $result[] = ($res === false) ? false : true;
                if(check_res($result)){
                    Db::commit();
                    return $this->success('操作成功');
                }else{
                    Db::rollback();
                    return $this->error('操作失败');
                }
            }catch (Exception $e){
                Db::rollback();
                return $this->error($e->getMessage());
            }
        }

        $assign = $this->model->where($edit_where)->find()->toArray();
        $this->assign($assign);

        $menu_list = model('auth.Menu')->field('id,pid,name as title')->order('sort','desc')->select()->toArray();
        $now_node_list = model('auth.RoleRelMenu')->where('role_id','=', $edit_where['id'])->column('menu_id');
        $this->assign('now_node_list', json_encode($now_node_list));
        $tree_obj = Tree::instance();
        $menu_tree_obj = $tree_obj->init($menu_list);
        $node_list = $menu_tree_obj->getTreeArray(0);
        $this->assign('node_list', $node_list);

        return $this->fetch();
    }

    //删除
    public function del(){
        $ids = $this->request->param('ids');
        Db::startTrans();
        try{
            $data = model('auth.Role')->order('id desc')->select()->toArray();
            $role_tree_obj = Tree::instance();
            $role_tree_obj->init($data);

            $ids_arr = explode(',', $ids);
            $result = [];
            foreach($ids_arr as $k=>$v){
                $child_ids = $role_tree_obj->getChildrenIds($v,true);
                $delete_status = $this->model->where('id','in',$child_ids)->delete();
                $result[] = ($delete_status === null) ? false : true;
                $menu = model('auth.RoleRelMenu')->where('role_id','in',$child_ids)->delete();
                $result[] = ($menu === null) ? false : true;
            }
            if(check_res($result)){
                Db::commit();
                return $this->success('操作成功');
            }else{
                Db::rollback();
                return $this->error('操作失败');
            }
        }catch (Exception $e){
            Db::rollback();
            return $this->error($e->getMessage() . $e->getFile() . $e->getLine());
        }
    }
}

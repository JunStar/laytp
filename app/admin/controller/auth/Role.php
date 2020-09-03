<?php
namespace app\admin\controller\auth;

use app\common\model\admin\role\Menu;
use laytp\controller\Backend;
use laytp\library\Tree;
use think\facade\Db;
use think\facade\Request;

/**
 * 角色控制器
 */
class Role extends Backend
{
    public $model;

    public function _initialize()
    {
        $this->model = new \app\common\model\admin\Role();
    }

    /**
     * 添加，同时添加lt_admin_role和lt_admin_role_menu两个表的数据
     * @return \think\response\Json
     */
    public function add()
    {
        Db::startTrans();
        try {
            $post = filter_post_data($this->request->post());
            $role_info = $this->model->getByName($post['name']);
            if ($role_info) {
                return $this->error('角色名已存在');
            }
            $menu_ids = explode(',', $post['menu_ids']);
            unset($post['menu_ids']);
            $result[] = $this->model->save($post);
            $saveAllData = [];
            foreach($menu_ids as $menu_id){
                $saveAllData[] = [
                    'role_id' => $this->model->id,
                    'menu_id' => $menu_id
                ];
            }
            $menu = new Menu();
            $result[] = $menu->saveAll($saveAllData);
            if (check_res($result)) {
                Db::commit();
                return $this->success('操作成功');
            } else {
                Db::rollback();
                return $this->error('操作失败');
            }
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error($e->getMessage());
        }
    }

    /**
     * 编辑，同时编辑lt_admin_role和lt_admin_role_menu两个表的数据
     * @return bool|\think\response\Json
     */
    public function edit()
    {
        $id = $this->request->param('id');

        $postData = Request::only(['id','name','menu_ids']);
        $post = filter_post_data($postData);

        $role_info = $this->model->getByName($post['name']);
        if ($role_info && ($role_info['id'] != $id)) {
            return $this->error('角色名已存在');
        }

        Db::startTrans();
        try {
            $menu_ids = explode(',', $post['menu_ids']);
            unset($post['menu_ids']);
            $update_res = $this->model->where('id','=',$id)->update($post);
            if ($update_res || $update_res === 0) {
                $result[] = true;
            } else if ($update_res === null) {
                return $result[] = false;
            }

            $result[] = Menu::where('role_id','=',$id)->delete();
            $saveAllData = [];
            foreach($menu_ids as $menu_id){
                $saveAllData[] = [
                    'role_id' => $id,
                    'menu_id' => $menu_id
                ];
            }
            $menu = new Menu();
            $result[] = $menu->saveAll($saveAllData);

            if (check_res($result)) {
                Db::commit();
                return $this->success('操作成功');
            } else {
                Db::rollback();
                return $this->error('操作失败');
            }
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error($e->getMessage());
        }
    }

    /**
     * 真实删除
     *  lt_admin_role、lt_admin_role_menu、lt_admin_role_user三表数据都要真实删除
     * @return \think\response\Json
     */
    public function trueDel()
    {
        $ids = $this->request->param('ids');
        $roleMenu = new Menu();
        Db::startTrans();
        try {
            $data = $this->model->order('id desc')->select()->toArray();
            $role_tree_obj = Tree::instance();
            $role_tree_obj->init($data);

            $ids_arr = explode(',', $ids);
            $result = [];
            foreach ($ids_arr as $k => $v) {
                $child_ids = $role_tree_obj->getChildrenIds($v, true);
                $delete_status = $this->model->where('id', 'in', $child_ids)->delete();
                $result[] = ($delete_status === null) ? false : true;
                $menu = $roleMenu->where('role_id', 'in', $child_ids)->delete();
                $result[] = ($menu === null) ? false : true;
            }
            if (check_res($result)) {
                Db::commit();
                return $this->success('操作成功');
            } else {
                Db::rollback();
                return $this->error('操作失败');
            }
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error($e->getMessage() . $e->getFile() . $e->getLine());
        }
    }

    /**
     * 获取编辑页面权限设置应该选中的菜单id
     *  只能获取最低级别的菜单id，有子菜单的菜单id不能返回
     *  原因：比如tree.setChecked('auth_node',1);会选中整棵树，因为这是模拟点击了树的首节点
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getMenuIds()
    {
        $id = $this->request->param('id');
        $menuIds = Menu::where('role_id', '=', $id)->column('menu_id');
        $auth = [];
        foreach($menuIds as $menuId){
            $hasChild = \app\common\model\admin\Menu::where('pid','=',$menuId)->find() ? true : false;
            if(!$hasChild){
                $auth[] = $menuId;
            }
        }
        return $this->success('获取成功', $auth);
    }
}

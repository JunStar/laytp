<?php

namespace plugin\core\controller\auth;

use plugin\core\model\role\Menu;
use laytp\controller\Backend;
use laytp\library\Tree;
use think\facade\Db;
use think\facade\Request;

/**
 * 角色控制器
 */
class Role extends Backend
{
    protected $noNeedAuth = ['getMenuIds'];

    public $model;

    public function _initialize()
    {
        $this->model = new \plugin\core\model\Role();
    }

    /**
     * 添加，同时添加lt_plugin_core_role和lt_plugin_core_role_menu两个表的数据
     * @return \think\response\Json
     */
    public function add()
    {
        Db::startTrans();
        try {
            $post     = filter_post_data($this->request->post());
            $roleInfo = $this->model->getByName($post['name']);
            if ($roleInfo) {
                return $this->error('角色名已存在');
            }
            $menuIds = explode(',', $post['menu_ids']);
            unset($post['menu_ids']);
            $result[]    = $this->model->save($post);
            $saveAllData = [];
            foreach ($menuIds as $menu_id) {
                $saveAllData[] = [
                    'plugin_core_role_id' => $this->model->id,
                    'plugin_core_menu_id' => $menu_id,
                ];
            }
            $menu     = new Menu();
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
     * 编辑，同时编辑lt_plugin_core_role和lt_plugin_core_role_menu两个表的数据
     * @return bool|\think\response\Json
     */
    public function edit()
    {
        $id = $this->request->param('id');

        $postData = Request::only(['id', 'name', 'menu_ids']);
        $post     = filter_post_data($postData);

        $roleInfo = $this->model->getByName($post['name']);
        if ($roleInfo && ($roleInfo['id'] != $id)) {
            return $this->error('角色名已存在');
        }

        Db::startTrans();
        try {
            $menuIds = explode(',', $post['menu_ids']);
            unset($post['menu_ids']);
            $updateRes = $this->model->where('id', '=', $id)->update($post);
            if ($updateRes || $updateRes === 0) {
                $result[] = true;
            } else if ($updateRes === null) {
                $result[] = false;
            }

            $result[]    = Menu::where('plugin_core_role_id', '=', $id)->delete();
            $saveAllData = [];
            foreach ($menuIds as $menu_id) {
                $saveAllData[] = [
                    'plugin_core_role_id' => $id,
                    'plugin_core_menu_id' => $menu_id,
                ];
            }
            $menu     = new Menu();
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
     *  lt_plugin_core_role、lt_plugin_core_role_menu、lt_plugin_core_user_role三表数据都要真实删除
     * @return \think\response\Json
     */
    public function trueDel()
    {
        $ids = $this->request->param('ids');
        Db::startTrans();
        try {
            $idsArr = explode(',', $ids);
            $result = [];
            $roles  = $this->model->onlyTrashed()->where('id', 'in', $ids)->select();
            foreach ($roles as $key => $item) {
                $result[] = $item->force()->delete();
            }
            $result[] = Menu::where('plugin_core_role_id', 'in', $idsArr)->delete() !== null;
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
     * 获取编辑页面权限设置应该选中的菜单id
     *  只能获取最低级别的菜单id，有子菜单的菜单id不能返回
     *  原因：比如tree.setChecked('auth_node',1);会选中整棵树，因为layui的树组件，模拟点击了树的首节点
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getMenuIds()
    {
        $id      = $this->request->param('id');
        $menuIds = Menu::where('plugin_core_role_id', '=', $id)->column('plugin_core_menu_id');
        $auth    = [];
        foreach ($menuIds as $menuId) {
            $hasChild = \plugin\core\model\Menu::where('pid', '=', $menuId)->find() ? true : false;
            if (!$hasChild) {
                $auth[] = $menuId;
            }
        }
        return $this->success('获取成功', $auth);
    }
}

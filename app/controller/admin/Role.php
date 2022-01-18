<?php

namespace app\controller\admin;

use laytp\controller\Backend;
use laytp\library\CommonFun;
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
        $this->model = new \app\model\admin\Role();
    }

    /**
     * 添加，同时添加lt_plugin_core_role和lt_plugin_core_role_menu两个表的数据
     * @return \think\response\Json
     */
    public function add()
    {
        Db::startTrans();
        try {
            $post     = CommonFun::filterPostData($this->request->post());
            $roleInfo = $this->model->getByName($post['name']);
            if ($roleInfo) throw new \Exception('角色名已存在');

            $menuIds = explode(',', $post['menu_ids']);
            unset($post['menu_ids']);
            $saveMenu    = $this->model->save($post);
            if (!$saveMenu) throw new \Exception('保存角色基本信息失败');

            $saveAllData = [];
            foreach ($menuIds as $menu_id) {
                $saveAllData[] = [
                    'admin_role_id' => $this->model->id,
                    'admin_menu_id' => $menu_id,
                ];
            }
            $menu     = new \app\model\admin\menu\Role();
            $saveAllMenu = $menu->saveAll($saveAllData);
            if (!$saveAllMenu) throw new \Exception('保存角色权限失败');

            Db::commit();
            return $this->success('操作成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('数据库异常，操作失败');
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
        $post     = CommonFun::filterPostData($postData);

        $roleInfo = $this->model->getByName($post['name']);
        if ($roleInfo && ($roleInfo['id'] != $id)) {
            return $this->error('角色名已存在');
        }

        Db::startTrans();
        try {
            $menuIds = explode(',', $post['menu_ids']);
            unset($post['menu_ids']);
            $updateRes = $this->model->where('id', '=', $id)->update($post);
            if(!is_numeric($updateRes)) throw new \Exception('保存角色基本信息失败');

            $delRes    = \app\model\admin\menu\Role::where('admin_role_id', '=', $id)->delete();
            if(!is_numeric($delRes)) throw new \Exception('删除角色权限失败');

            $saveAllData = [];
            foreach ($menuIds as $menu_id) {
                $saveAllData[] = [
                    'admin_role_id' => $id,
                    'admin_menu_id' => $menu_id,
                ];
            }
            $menu     = new \app\model\admin\menu\Role();
            $result[] = $menu->saveAll($saveAllData);

            Db::commit();
            return $this->success('操作成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('数据库异常，操作失败');
        }
    }

    /**
     * 真实删除
     *  lt_admin_role、lt_admin_menu_role、lt_admin_role_user三表数据都要真实删除
     * @return \think\response\Json
     */
    public function trueDel()
    {
        $ids = (string)$this->request->param('ids');
        Db::startTrans();
        try {
            $idsArr = explode(',', $ids);
            $roles  = $this->model->onlyTrashed()->where('id', 'in', $ids)->select();
            foreach ($roles as $key => $item) {
                $delRes = $item->force()->delete();
                if(!$delRes) throw new \Exception('角色删除失败');
            }
            $delRes = \app\model\admin\menu\Role::where('admin_role_id', 'in', $idsArr)->delete();
            if(!is_numeric($delRes)) throw new \Exception('角色权限删除失败');

            $delRes = \app\model\admin\role\User::where('admin_role_id', 'in', $idsArr)->delete();
            if(!is_numeric($delRes)) throw new \Exception('角色用户删除失败');

            Db::commit();
            return $this->success('操作成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('数据库异常，操作失败');
        }
    }

    /**
     * 获取编辑页面权限设置应该选中的菜单id
     *  只能获取最低级别的菜单id，有子菜单的菜单id不能返回
     *  原因：比如tree.setChecked('auth_node',1);会选中整棵树，因为layui的树组件，模拟点击了树的首节点
     * @return false|string|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getMenuIds()
    {
        $id      = $this->request->param('id');
        $menuIds = \app\model\admin\menu\Role::where('admin_role_id', '=', $id)->column('admin_menu_id');
        $auth    = [];
        foreach ($menuIds as $menuId) {
            $hasChild = \app\model\admin\Menu::where('pid', '=', $menuId)->find() ? true : false;
            if (!$hasChild) {
                $auth[] = $menuId;
            }
        }
        return $this->success('获取成功', $auth);
    }
}

<?php

namespace app\controller\admin;

use app\service\admin\UserServiceFacade;
use laytp\controller\Backend;
use laytp\library\CommonFun;
use laytp\library\Tree;

/**
 * 菜单控制器
 */
class Menu extends Backend
{
    public $noNeedAuth = ['getMenuTree','getTree'];
    public $menuList;
    public $model;
    public $orderRule = ['sort' => 'desc', 'id' => 'asc'];

    public function _initialize()
    {
        $this->model = new \app\model\admin\Menu();
    }

    public function index()
    {
        $where = $this->buildSearchParams();
        $order = $this->buildOrder();
        $sourceData = $this->model->where($where)->order($order)->select()->toArray();
        $isTree = $this->request->param('is_tree');
        if($isTree){
            $menuTreeObj = Tree::instance();
            $menuTreeObj->init($sourceData);
            $data = $menuTreeObj->getRootTrees();
        }else{
            $data = $sourceData;
        }
        return $this->success('数据获取成功', $data);
    }

    //获取当前登录者的权限列表，返回树形数据，角色管理赋予权限时用到
    public function getTree(){
        $user = UserServiceFacade::getUser();
        if($user->is_super_manager === 1){
            $sourceData  = $this->model->order($this->orderRule)->select()->toArray();
        }else{
            $roleIds = \app\model\admin\role\User::where('admin_user_id','=', $user->id)
                ->column('admin_role_id');
            $menuIds = \app\model\admin\menu\Role::where('admin_role_id','in',$roleIds)
                ->column('admin_menu_id');
            $where[] = ['id', 'in', $menuIds];
            $sourceData  = $this->model->order($this->orderRule)->where($where)->select()->toArray();
        }
        $menuTreeObj = Tree::instance();
        $menuTreeObj->init($sourceData);
        //由列表数据转化成树形结构数据
        $data = $menuTreeObj->getRootTrees();
        return $this->success('获取成功', $data);
    }

    //获取当前登录者的菜单列表，返回树形数据，仅返回is_menu=1的列表，后台菜单列表展示使用
    public function getMenuTree(){
        $user = UserServiceFacade::getUser();
        $where[] = ['is_show', '=', 1];
        $where[] = ['is_menu', '=', 1];
        if($user->is_super_manager === 1){
            $sourceData  = $this->model->order($this->orderRule)->where($where)->select()->toArray();
        }else{
            $roleIds = \app\model\admin\role\User::where('admin_user_id','=', $user->id)
                ->column('admin_role_id');
            $menuIds = \app\model\admin\menu\Role::where('admin_role_id','in',$roleIds)
                ->column('admin_menu_id');
            $where[] = ['id','in',$menuIds];
            $sourceData  = $this->model->order($this->orderRule)->where($where)->select()->toArray();
        }
        $menuTreeObj = Tree::instance();
        $menuTreeObj->init($sourceData);
        //由列表数据转化成树形结构数据
        $data = $menuTreeObj->getRootTrees();
        return $this->success('获取成功', $data);
    }

    //添加
    public function add()
    {
        $post = CommonFun::filterPostData($this->request->post());
        if ($post['rule'] && substr($post['rule'], 0, 1) != '/') {
            $post['rule'] = '/' . $post['rule'];
        }
        if ($this->model->create($post)) {
            return $this->success('添加成功', $post);
        } else {
            return $this->error('操作失败');
        }
    }

    //编辑
    public function edit()
    {
        $id   = $this->request->param('id');
        $info = $this->model->find($id);
        $post = CommonFun::filterPostData($this->request->post());
        if ($post['rule'] && substr($post['rule'], 0, 1) != '/') {
            $post['rule'] = '/' . $post['rule'];
        }
        if ($id == $post['pid']) {
            return $this->error('不能将上级改成自己');
        }
        foreach ($post as $k => $v) {
            $info->$k = $v;
        }
        $update_res = $info->save();
        if ($update_res) {
            return $this->success('操作成功');
        } else if ($update_res === 0) {
            return $this->success('未做修改');
        } else {
            return $this->error('操作失败');
        }
    }

    //删除
    public function del()
    {
        $ids = $this->request->post('ids');
        if (!$ids) {
            return $this->error('参数ids不能为空');
        }

        $sourceData = $this->model->select()->toArray();
        $treeLib = Tree::instance();
        $treeLib->init($sourceData);
        $childIds = $treeLib->getChildIds($ids);

        if ($this->model->destroy($childIds)) {
            return $this->success('数据删除成功', $childIds);
        } else {
            return $this->error('数据删除失败');
        }
    }

    //设置排序
    public function setSort()
    {
        $id       = $this->request->post('id');
        $fieldVal = $this->request->post('field_val');
        $isRecycle = $this->request->post('is_recycle');
        $update['sort'] = $fieldVal;
        try {
            if($isRecycle) {
                $updateRes = $this->model->onlyTrashed()->where('id', '=', $id)->update($update);
            } else {
                $updateRes = $this->model->where('id', '=', $id)->update($update);
            }
            if ($updateRes) {
                return $this->success('操作成功');
            } else if ($updateRes === 0) {
                return $this->success('未作修改');
            } else {
                return $this->error('操作失败');
            }
        } catch (\Exception $e) {
            return $this->exceptionError($e);
        }
    }

    //设置是否为菜单
    public function setIsMenu()
    {
        $id       = $this->request->post('id');
        $fieldVal = $this->request->post('field_val');
        $isRecycle = $this->request->post('is_recycle');
        $update['is_menu'] = $fieldVal;
        try {
            if($isRecycle) {
                $updateRes = $this->model->onlyTrashed()->where('id', '=', $id)->update($update);
            } else {
                $updateRes = $this->model->where('id', '=', $id)->update($update);
            }
            if ($updateRes) {
                return $this->success('操作成功');
            } else if ($updateRes === 0) {
                return $this->success('未作修改');
            } else {
                return $this->error('操作失败');
            }
        } catch (\Exception $e) {
            return $this->exceptionError($e);
        }
    }

    //设置是否显示
    public function setIsShow()
    {
        $id       = $this->request->post('id');
        $fieldVal = $this->request->post('field_val');
        $isRecycle = $this->request->post('is_recycle');
        $update['is_show'] = $fieldVal;
        try {
            if($isRecycle) {
                $updateRes = $this->model->onlyTrashed()->where('id', '=', $id)->update($update);
            } else {
                $updateRes = $this->model->where('id', '=', $id)->update($update);
            }
            if ($updateRes) {
                return $this->success('操作成功');
            } else if ($updateRes === 0) {
                return $this->success('未作修改');
            } else {
                return $this->error('操作失败');
            }
        } catch (\Exception $e) {
            return $this->exceptionError($e);
        }
    }

    // 复制菜单
    public function copy()
    {
        $pid = (int)$this->request->post('pid');
        $ids = $this->request->post('ids');
        if (!$ids) {
            return $this->error('参数ids不能为空');
        }

        $data = \app\model\admin\Menu::where('id','in', $ids)
            ->withoutField('id')->select()
            ->each(function($item) use ($pid){
                $item->pid = $pid;
            })->toArray();

        $insert = $this->model->insertAll($data);
        if($insert){
            return $this->success('复制成功');
        }else{
            return $this->error('复制失败');
        }
    }

    // 复制菜单
    public function move()
    {
        $pid = (int)$this->request->post('pid');
        $ids = $this->request->post('ids');
        if (!$ids) {
            return $this->error('参数ids不能为空');
        }

        $save = \app\model\admin\Menu::where('id','in', $ids)
            ->save(['pid'=>$pid]);

        if($save){
            return $this->success('移动成功');
        }else{
            return $this->error('移动失败');
        }
    }
}

<?php

namespace plugin\core\controller\auth;

use laytp\controller\Backend;
use laytp\library\Tree;

/**
 * 菜单控制器
 */
class Menu extends Backend
{
    public $menu_list;
    public $model;
    public $order;

    public function _initialize()
    {
        $this->model = new \plugin\core\model\Menu();
        $this->order = ['sort' => 'desc', 'id' => 'asc'];
    }

    /**
     * 获取所有的无限极分类数据，返回带children的树形结构
     *  treeTable和xmSelect组件均使用此方法获取树形结构数据
     */
    public function index()
    {
        $sourceData = $this->model->order($this->order)->select()->toArray();
        $menuTreeObj = Tree::instance();
        $menuTreeObj->init($sourceData);
        $data = $menuTreeObj->getTreeArray(0);
        $layuiSelect = $this->request->param('layui_select');
        if ($layuiSelect) {
            $data = $menuTreeObj->getTreeList($data);
        }
        return $this->success('获取成功', $data);
    }

    /**
     * 获取菜单数据
     */
    public function getData()
    {
        //当前登录者拥有的权限节点列表数据
        $sourceData = $this->model->order($this->order)->select()->toArray();
        $menuTreeObj = Tree::instance();
        $menuTreeObj->init($sourceData);
        //由列表数据转化成树形结构数据
        $data = $menuTreeObj->getTreeArray(0);
        return $this->success('获取成功', ['treeData' => $data, 'listData' => $sourceData]);
    }

    //编辑
    public function edit()
    {
        $id = $this->request->param('id');
        $info = $this->model->find($id);
        if ($this->request->isAjax() && $this->request->isPost()) {
            $post = filter_post_data($this->request->post());
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
    }
}

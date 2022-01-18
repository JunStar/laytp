<?php

namespace app\controller\admin\files;

use laytp\controller\Backend;
use laytp\library\Tree;

/**
 * 附件分类管理
 */
class Category extends Backend
{
    /**
     * files_category模型对象
     * @var \app\model\files\Category
     */
    protected $model;
	public $hasSoftDel=1;//是否拥有软删除功能
	public $orderRule=['sort' => 'DESC','id'=>'ASC'];

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\model\files\Category();
    }
    
    //查看
    public function index(){
        $where = $this->buildSearchParams();
        $order = $this->buildOrder();
        $sourceData = $this->model->order($order)->where($where)->select()->toArray();
        $isTree = $this->request->param('is_tree');
        if($isTree){
            $menuTreeObj = Tree::instance();
            $menuTreeObj->init($sourceData);
            $data = $menuTreeObj->getRootTrees();
        }else{
            $data = $sourceData;
        }
        return $this->success('获取成功', $data);
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
            return $this->success('数据删除成功');
        } else {
            return $this->error('数据删除失败');
        }
    }
    
    //回收站
    public function recycle(){
        $where = $this->buildSearchParams();
        $order = $this->buildOrder();
        $limit = $this->request->param('limit', 10);
        $data  = $this->model->onlyTrashed()
                 ->with(['parent'])
                 ->order($order)->where($where)->paginate($limit)->toArray();
        return $this->success('回收站数据获取成功', $data);
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
            return $this->error('数据库异常，操作失败');
        }
    }
}

<?php

namespace plugin\core\controller\autocreate;

use laytp\controller\Backend;
use plugin\core\model\autocreate\curd\Field;
use plugin\core\model\autocreate\curd\Table;
use think\facade\Config;

class Curd extends Backend
{
    protected $noNeedAuth = ['getConnections', 'getTableList', 'getFieldList'];

    public function getTreeTableList()
    {
        $tables = Table::field('id,`table` as title')->order('id', 'desc')->select()->toArray();
        return $this->success('数据获取成功', $tables);
    }

    //获取数据表列表
    public function getTableList()
    {
        $where = $this->buildSearchParams();
        $order = $this->buildOrder();
        $limit = $this->request->param('limit');
        $data = Table::where($where)->order($order)->paginate($limit)->toArray();
        return $this->success('数据获取成功', $data);
    }

    //获取字段列表
    public function getFieldList()
    {
        $where = $this->buildSearchParams();
        $order = $this->buildOrder();
        $select_page = $this->request->param('select_page');
        $limit = $select_page ? $this->request->param('pageSize') : $this->request->param('limit');
        $data = Field::where($where)->order($order)->paginate($limit)->toArray();
        return $this->success('数据获取成功', $data);
    }

    //生成常规CURD
    public function createNormalCurd()
    {
        $tableId = $this->request->param('table_id');
        $curd = new \plugin\core\library\autocreate\Curd($tableId);
        if ($curd->execute()) {
            return $this->success('生成成功');
        } else {
            return $this->error($curd->getError());
        }
    }
}

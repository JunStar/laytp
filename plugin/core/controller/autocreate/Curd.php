<?php

namespace plugin\core\controller\autocreate;

use laytp\controller\Backend;
use plugin\core\model\autocreate\curd\Field;
use plugin\core\model\autocreate\curd\Table;
use think\facade\Config;

class Curd extends Backend
{
    protected $noNeedAuth = ['getDatabaseList', 'getTableList', 'getFieldList'];

    public function getTreeTableList()
    {
        $databaseConf = Config::get('database.connections');
        $result = [];
        foreach ($databaseConf as $conf) {
            $temp = [
                'title' => $conf['database'],
                'id' => $conf['database'],
                'children' => Table::where('database', '=', $conf['database'])->field('id,`table` as title')->select()
            ];
            $result[] = $temp;
        }
        return $this->success('数据获取成功', $result);
    }

    //获取数据库列表
    public function getDatabaseList()
    {
        $databaseConf = Config::get('database.connections');
        $databaseList = [];
        foreach ($databaseConf as $conf) {
            $databaseList[]['database'] = $conf['database'];
        }
        return $this->success('数据获取成功', $databaseList);
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
}

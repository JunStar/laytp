<?php
/**
 * 数据表模型
 */
namespace app\admin\model;

use think\facade\Config;
use think\Model;

class InformationSchemaModel extends Model
{
    // 设置当前模型的数据库连接
    protected $connection = [
        // 数据库名
        'database'    => 'information_schema',
    ];

    //获取所有表名
    public function getTableList(){
        $where['table_schema'] = Config::get('database.database');
        $where['table_type'] = 'base table';
        $list = $this->table('tables')->where($where)->select()->toArray();
        return $list;
    }
}
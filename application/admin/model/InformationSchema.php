<?php
/**
 * 数据表模型
 */
namespace app\admin\model;

use model\Backend;
use think\facade\Config;

class InformationSchema extends Backend
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
        $list = $this->table('tables')->where($where)->select();
        return $list;
    }

    //获取某个表所有字段名和注释
    public function getFieldsComment($table_name){
        $where['table_schema'] = Config::get('database.database');
        $where['table_name'] = $table_name;
        $field = 'COLUMN_NAME,COLUMN_COMMENT';
        $list = $this->table('columns')->where($where)->field($field)->select();
        return $list;
    }
}
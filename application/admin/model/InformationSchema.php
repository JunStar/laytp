<?php
/**
 * 数据表模型
 */
namespace app\admin\model;

use model\Backend;
use think\Db;
use think\facade\Config;

class InformationSchema extends Backend
{
    // 设置当前模型的数据库连接
    protected $connection = [
        // 数据库名
        'database'    => 'information_schema',
    ];

    /**
     * 获取表名列表
     * @param bool $can_curd 是否可以生成curd，默认只取能自动生成curd的表名列表
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTableList($can_curd=true){
        $where[] = ['table_schema','=',Config::get('database.database')];
        $where[] = ['table_type','=','base table'];
        if($can_curd)
            $where[] = ['table_name','not in',Config::get('curd.system_tables')];

        $list = $this
            ->table('tables')
            ->where($where)
            ->select();
        return $list;
    }

    //获取某个表所有字段名和注释
    public function getFieldsComment($table_name){
        $where['table_schema'] = Config::get('database.database');
        $where['table_name'] = $table_name;
        $field = 'COLUMN_NAME,COLUMN_COMMENT,COLUMN_DEFAULT';
        $list = $this->table('columns')->where($where)->field($field)->select();
        return $list;
    }

    //获取某个表的主键名称和注释
    public function getPkInfo($table){
        $model = Db::table($table);
        $fields = $model->getTableFields();
        $pk = $model->getPk();
        $comment = $this->getFieldsComment($table)->toArray();
        $comment_map = arr_to_map($comment,'COLUMN_NAME');
        $field_comment = 'ID';
        foreach($fields as $k=>$v){
            if( $v == $pk ){
                $field_comment = $comment_map[$v]['COLUMN_COMMENT'];
                break;
            }
        }
        return ['pk'=>$pk,'field_comment'=>$field_comment];
    }

    //获取某个表的注释
    public function getTableComment($table_name){
        $table_list = $this->getTableList()->toArray();
        foreach($table_list as $k=>$v){
            if($v['TABLE_NAME'] == $table_name){
                return $v['TABLE_COMMENT'];
            }
        }
        return '';
    }
}
<?php
/**
 * 后台菜单模型
 */
namespace app\admin\model\autocreate;

use model\Backend;
use think\Db;
use think\Exception;

class Curd extends Backend
{
    // 表名
    protected $name = 'autocreate_curd';

    public function addData($data){
        return $this->field(true)->insert($data);
    }

    public function editData($data,$where){
        return $this->field(true)->where($where)->update($data);
    }

    //导入功能
    public function import($post_data){
        try{
            $table_name = $post_data['global']['table_name'];
            $is_exist = $this->where(['table_name'=>$table_name])->find();
            $post_data['global']['show_fields'] = $post_data['global']['fields_name'];

            $model = Db::table($table_name);
            $fields = $model->getTableFields();
            $pk = $model->getPk();
            $comment = model('admin/InformationSchema')->getFieldsComment($table_name)->toArray();
            $comment_map = arr_to_map($comment,'COLUMN_NAME');
            $all_fields = [];
            foreach($fields as $k=>$v){
                if( $v != $pk ){
                    $temp['field_name'] = $v;
                    $temp['field_comment'] = $comment_map[$v]['COLUMN_COMMENT'];
                    $all_fields[] = $temp;
                }
            }
            $post_data['global']['all_fields'] = $all_fields;

            $data = [
                'field_list' => json_encode($post_data['field_list']),
                'global' => json_encode($post_data['global'])
            ];
            if($is_exist){
                $data['update_time'] = time();
                $result = $this->field(true)->where(['table_name'=>$table_name])->update($data);
                if( $result ){
                    $id = $this->where(['table_name'=>$table_name])->value('id');
                    return $this->success('更新成功',$id);
                }else{
                    return $this->error('更新失败');
                }
            }else{
                $data['create_time'] = time();
                $data['update_time'] = time();
                $result = $this->field(true)->insert($data);
                return $this->success('添加成功',$result);
            }
        }catch (Exception $e){
            return $this->error($e->getMessage());
        }
    }
}
<?php
/**
 * 自动生成Curd模型
 */
namespace addons\autocreate\admin\model;

use model\Base;
use think\Db;
use think\Exception;

class Curd extends Base
{
    // 表名
    protected $name = 'autocreate_curd';

    public function addData($data){
        return $this->field(true)->insert($data);
    }

    public function editData($data,$where){
        return $this->field(true)->where($where)->update($data);
    }

    //生成无限级分类curd
    public function import_category($post_data){
        $is_exist = $this->where(['table_name'=>$post_data['table_name']])->value('id');
        $data['table_name'] = $post_data['table_name'];
        $informationSchema = new InformationSchema();
        $data['table_comment'] = $informationSchema->getTableComment($post_data['table_name']);
        $data['field_list'] = json_encode([],JSON_UNESCAPED_UNICODE);
        $data['global'] = json_encode($post_data,JSON_UNESCAPED_UNICODE);
        $data['relation_model'] = json_encode([],JSON_UNESCAPED_UNICODE);
        if($is_exist){
            $data['update_time'] = date('Y-m-d H:i:s');
            $result = $this->field(true)->where(['table_name'=>$post_data['table_name']])->update($data);
            if( $result ){
                return $this->success('更新成功',$is_exist);
            }else{
                return $this->error('更新失败');
            }
        }else{
            $data['create_time'] = date('Y-m-d H:i:s');
            $data['update_time'] = date('Y-m-d H:i:s');
            $id = $this->field(true)->insertGetId($data);
            return $this->success('添加成功',$id);
        }
    }

    //生成常规Curd
    public function import($post_data){
        try{
            $table_name = $post_data['global']['table_name'];
            $is_exist = $this->where(['table_name'=>$table_name])->find();
            $post_data['global']['show_fields'] = $post_data['global']['fields_name'];

            $model = Db::table($table_name);
            $fields = $model->getTableFields();
            $pk = $model->getPk();
            $informationSchema = new InformationSchema();
            $comment = $informationSchema->getFieldsComment($table_name)->toArray();
            $comment_map = arr_to_map($comment,'COLUMN_NAME');
            $all_fields = [];
            foreach($fields as $k=>$v){
                if( $v != $pk ){
                    $temp['field_name'] = $v;
                    $temp['field_comment'] = $comment_map[$v]['COLUMN_COMMENT'];
                    $temp['column_default'] = $comment_map[$v]['COLUMN_DEFAULT'];
                    $all_fields[] = $temp;
                }
            }
            $post_data['global']['all_fields'] = $all_fields;
            $post_data['relation_model'] = isset($post_data['relation_model']) ? $post_data['relation_model'] : [];

            $data = [
                'field_list' => json_encode($post_data['field_list'],JSON_UNESCAPED_UNICODE),
                'global' => json_encode($post_data['global'],JSON_UNESCAPED_UNICODE),
                'relation_model' => json_encode($post_data['relation_model'],JSON_UNESCAPED_UNICODE)
            ];
            if($is_exist){
                $data['update_time'] = date('Y-m-d H:i:s');
                $result = $this->field(true)->where(['table_name'=>$table_name])->update($data);
                if( $result ){
                    $id = $this->where(['table_name'=>$table_name])->value('id');
                    return $this->success('更新成功',$id);
                }else{
                    return $this->error('更新失败');
                }
            }else{
                $data['table_name'] = $table_name;
                $informationSchema = new InformationSchema();
                $data['table_comment'] = $informationSchema->getTableComment($table_name);
                $data['create_time'] = date('Y-m-d H:i:s');
                $data['update_time'] = date('Y-m-d H:i:s');
                $this->field(true)->insert($data);
                return $this->success('添加成功',$this->getLastInsID());
            }
        }catch (Exception $e){
            return $this->error($e->getMessage());
        }
    }
}
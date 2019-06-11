<?php
/**
 * 一键生成Curd
 */
namespace app\admin\controller\autocreate;

use controller\Backend;
use think\Db;
use think\facade\Config;

class Curd extends Backend
{
    public $model;

    protected $special_fields;

    public function initialize(){
        $this->special_fields = Config::get('curd.special_fields');
        parent::initialize();
        $this->model = model('autocreate.Curd');
    }

    //首页
    public function index(){
        if( $this->request->isAjax() ){
            $where = $this->build_params();
            $limit = $this->request->param('limit');
            $data = $this->model
                ->where($where)
                ->order('exec_update_time','desc')
                ->paginate($limit)
                ->toArray();
            return layui_table_page_data($data);
        }
        return $this->fetch();
    }

    //添加
    public function add()
    {
        if( $this->request->isAjax() && $this->request->isPost() ){
            $post = $this->request->post("row/a");
            if( $this->model->addData($post) ){
                return $this->success('操作成功');
            }else{
                return $this->error('操作失败');
            }
        }
        return $this->fetch();
    }

    //导入
    public function import(){
        if( $this->request->isAjax() ){
            if( $this->request->isPost() ){
                //这里要将数据存入数据库
                $post_data = $this->request->post();
                $result = $this->model->import($post_data);
                if( $result['code'] ){
                    $exec_res = exec_command('app\admin\command\Curd',['--id='.$result['data']]);
                    if($exec_res['code']){
                        $this->success($exec_res['msg']);
                    }else{
                        $this->error($exec_res['msg']);
                    }
                }else{
                    $this->error($result['msg']);
                }
            }
        }

        //获取所有的表名称
        $assign['table_list'] = model('InformationSchema')->getTableList();
        $this->assign($assign);
        return $this->fetch();
    }

    //获取所有表名
    public function get_table_list(){
        $result = model('InformationSchema')->getTableList();
        $this->success('获取成功', $result);
    }

    //根据表名获取字段列表
    public function get_curd_info(){
        $table = $this->request->param('table_name');
        if(!$table){
            $this->error('请选择表名',[]);
        }

        //查找是否已经生成过，生成过用生成过的数据渲染默认的详细设置，没有生成过使用数据库配置渲染详细设置
        $curd_info = $this->model->where('table_name','=',$table)->find();
        $model = Db::table($table);
        $pk = $model->getPk();
        $fields = $model->getTableFields();
        $comment = model('InformationSchema')->getFieldsComment($table)->toArray();
        $comment_map = arr_to_map($comment,'COLUMN_NAME');
        //生成过用生成过的数据渲染默认的详细设置
        if($curd_info){
            $result['selected_list'] = json_decode($curd_info['field_list'], true);
            $result['relation_model'] = json_decode($curd_info['relation_model'], true);
            $result['global'] = json_decode($curd_info['global'], true);
            if($result['relation_model']){
                foreach($result['relation_model'] as $k=>$v){
                    if(!isset($result['fields_list'][$v['table_name']])) {
                        $result['fields_list'][$v['table_name']] = Db::table($v['table_name'])->getTableFields($v['table_name']);
                    }
                }
            }
            $result['fields_list'][$table] = $fields;
            foreach($result['selected_list'] as $k=>$v){
                if($v['form_type'] == 'select_page'){
                    if(!isset($result['fields_list'][$v['form_additional']['table_name']])){
                        $result['fields_list'][$v['form_additional']['table_name']] = Db::table($v['form_additional']['table_name'])->getTableFields($v['form_additional']['table_name']);
                    }
                }
            }
            $selected_list = arr_to_map(json_decode($curd_info['field_list'], true), 'field_name');
            foreach($fields as $k=>$v){
                if( !in_array($v, array_merge([$pk],$this->special_fields)) ) {
                    if(isset($selected_list[$v])){
                        $result['all_fields'][] = $selected_list[$v];
                    }else{
                        $result['all_fields'][] = [
                            'field_name' => $v
                            ,'field_comment' => $comment_map[$v]['COLUMN_COMMENT']
                        ];
                    }
                }
            }
            $this->success('获取成功', $result);
            //没有生成过使用数据库配置渲染详细设置
        }else{
            $result = [];
            $i = 0;
            foreach($fields as $k=>$v){
                if( $v != $pk ){
                    $result['all_fields'][$i]['field_name'] = $v;
                    $result['all_fields'][$i]['field_comment'] = $comment_map[$v]['COLUMN_COMMENT'];
                    $result['all_fields'][$i]['field_show_index'] = 1;
                    $result['all_fields'][$i]['field_show_add'] = 1;
                    $result['all_fields'][$i]['field_show_edit'] = 1;
                    if($v != 'delete_time' && $v != 'update_time'){
                        $result['selected_list'][$i]['field_name'] = $v;
                        $result['selected_list'][$i]['field_comment'] = $comment_map[$v]['COLUMN_COMMENT'];
                        $result['selected_list'][$i]['field_show_index'] = 1;
                        $result['selected_list'][$i]['field_show_add'] = 1;
                        $result['selected_list'][$i]['field_show_edit'] = 1;
                    }
                    $i++;
                }
            }
            $result['fields_list'][$table] = $fields;
            $this->success('获取成功', $result);
        }
    }

    public function get_fields_by_table_name(){
        $table = $this->request->param('table_name');
        if(!$table){
            $this->error('请选择表名',[]);
        }
        $model = Db::table($table);
        $fields = $model->getTableFields();
        $this->success('获取成功', $fields);
    }

    //重新生成Curd
    public function re_create(){
        $id = $this->request->param('id');
        $exec_res = exec_command('app\admin\command\Curd',['--id='.$id]);
        if($exec_res['code']){
            $this->success($exec_res['msg']);
        }else{
            $this->error($exec_res['msg']);
        }
    }

    //设置字段信息
    public function set_fields(){
        if( $this->request->isAjax() && $this->request->isPost() ){
            $post = $this->request->post("row/a");
            if( $this->model->setFields($post) ){
                return $this->success('操作成功');
            }else{
                return $this->error('操作失败');
            }
        }
        return $this->fetch();
    }

    //删除
    public function del(){
        $del_where['id'] = $this->request->param('id');
        if( $this->model->where($del_where)->delete() ){
            return $this->success('操作成功');
        }else{
            return $this->error('操作失败');
        }
    }
}
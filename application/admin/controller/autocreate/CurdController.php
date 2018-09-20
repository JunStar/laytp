<?php
/**
 * 一键生成Curd
 */
namespace app\admin\controller\autocreate;

use app\admin\validate\autocreate\import;
use controller\BasicAdminController;
use think\Db;
use think\Exception;

class CurdController extends BasicAdminController
{
    public function initialize(){
        parent::initialize();
        $this->model = model('AutocreateCurd');
    }

    //首页
    public function index(){
        if( $this->request->isAjax() ){
            $where = $this->build_params();
            $limit = $this->request->param('limit');
            $data = $this->model->where($where)->paginate($limit)->toArray();
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

    //根据表名获取字段列表
    public function get_fields_by_table_name(){
        $table = $this->request->param('table_name');
        if(!$table){
            $this->success('获取成功',[]);
        }
        $model = Db::table($table);
        $fields = $model->getTableFields();
        $pk = $model->getPk();
        $result = [];
        $comment = model('InformationSchema')->getFieldsComment($table)->toArray();
        $comment_map = arr_to_map($comment,'COLUMN_NAME');
        foreach($fields as $k=>$v){
            if( $v != $pk ){
                $result[$k]['field_name'] = $v;
                $result[$k]['field_comment'] = $comment_map[$v]['COLUMN_COMMENT'];
                $result[$k]['table_width'] = '自适应';
                $result[$k]['table_min_width'] = '使用全局配置';
            }
        }
        sort($result);
        $this->success('获取成功', $result);
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
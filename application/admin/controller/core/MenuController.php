<?php
/**
 * 菜单
 */
namespace app\admin\controller\core;

use controller\BasicAdmin;

class MenuController extends BasicAdmin
{
    public function index()
    {
        if( $this->request->isAjax() ){
            $where = $this->build_params();
            $limit = $this->request->param('limit');
            $data = model('Menu')->where($where)->order('id asc')->paginate($limit)->toArray();
            return layui_table_data($data);
        }
        return $this->fetch();
    }

    public function add()
    {
        if( $this->request->isAjax() && $this->request->isPost() ){
            $post = $this->request->post("row/a");
            if( model('Menu')->addData($post) ){
                return $this->success('操作成功');
            }else{
                return $this->error('操作失败');
            }
        }
        return $this->fetch();
    }

    public function edit(){
        $edit_where['id'] = $this->request->param('id');

        if( $this->request->isAjax() && $this->request->isPost() ){
            $post = $this->request->post("row/a");
            if( model('Menu')->where($edit_where)->update($post) ){
                return $this->success('操作成功');
            }else{
                return $this->error('操作失败');
            }
        }

        $assign = model('Menu')->where($edit_where)->find()->toArray();
        $this->assign($assign);
        return $this->fetch();
    }

    public function del(){
        $del_where['id'] = $this->request->param('id');
        if( model('Menu')->where($del_where)->delete() ){
            return $this->success('操作成功');
        }else{
            return $this->error('操作失败');
        }
    }

    //设置状态
    public function set_status(){
        $where['id'] = $this->request->param('id');
        $field = $this->request->param('field');
        $field_val = $this->request->param('field_val');
        $save[$field] = $field_val;
        if( model('Menu')->where($where)->update($save) ){
            return $this->success('操作成功');
        }else{
            return $this->error('操作失败');
        }
    }
}

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
            $data = model('Menu')->order('id asc')->paginate(1)->toArray();
            return layui_table_data($data);
        }
        return $this->fetch();
    }

    public function add()
    {
        if( $this->request->isAjax() && $this->request->isPost() ){
            $post = $this->request->post("row/a");
            if( model('Menu')->add($post) ){
                return $this->success('操作成功');
            }else{
                return $this->error('数据存入失败');
            }
        }
        return $this->fetch();
    }
}

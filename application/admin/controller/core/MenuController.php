<?php
/**
 * 菜单
 */
namespace app\admin\controller\core;

use controller\BasicAdmin;
use app\admin\model\MenuModel;

class MenuController extends BasicAdmin
{
    public function initialize()
    {
        $this->model = new MenuModel();
    }

    public function index()
    {
        return $this->fetch();
    }

    public function add()
    {
        if( $this->request->isAjax() && $this->request->isPost() ){
            return $this->success('操作成功');
            $post = $this->request->post("row/a");
            if( $this->model->add($post) ){
                return $this->success('操作成功');
            }else{
                return $this->error('数据存入失败');
            }
        }
        return $this->fetch();
    }
}

<?php

namespace app\admin\controller\admin;

use controller\Backend;
use think\Db;

/**
 * 管理员日志表
 */
class Log extends Backend
{

    /**
     * admin_log模型对象
     * @var app\admin\model\admin\Log
     */
    protected $model;
	protected $relation;

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\admin\Log();
    }

    //查看
    public function index(){
        if( $this->request->isAjax() ){
            $where = $this->build_params();
            $limit = $this->request->param('limit');
            $data = $this->model
                ->with('admin')
                ->where($where)->order('id desc')->paginate($limit)->toArray();
            return layui_table_page_data($data);
        }
        return $this->fetch();
    }

    //编辑
    public function detail(){
        $where['id'] = $this->request->param('id');
        $assign = $this->model->where($where)->find()->toArray();
        $assign['name'] = join(',', $this->relation['admin_id']['model']->where('id in ('. $assign['admin_id'] .')')->column($this->relation['admin_id']['show_field']));
        $this->assign($assign);
        return $this->fetch();
    }
}

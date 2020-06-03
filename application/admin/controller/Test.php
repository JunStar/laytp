<?php

namespace app\admin\controller;

use controller\Backend;

/**
 * 一键生成CURD测试表
 */
class Test extends Backend
{

    /**
     * test模型对象
     * @var app\admin\model\Test
     */
    protected $model;
	public $has_del=1;//是否拥有删除功能
	public $has_soft_del=1;//是否拥有软删除功能

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\Test();
        $assign['const_hobby'] = $this->model->getArrayConstList('hobby');
		$assign['const_sign'] = $this->model->getArrayConstList('sign');
		$this->assign($assign);
    }

    
    //查看
    public function index(){
        if( $this->request->isAjax() ){
            $where = $this->build_params();
            $select_page = $this->request->param('select_page');
            $limit = $select_page ? $this->request->param('pageSize') : $this->request->param('limit');
            $data = $this->model
                ->with(['category'])
                ->where($where)->order('id desc')->paginate($limit)->toArray();
            return layui_table_page_data($data);
        }
        return $this->fetch();
    }

    
    //回收站
    public function recycle(){
        if( $this->request->isAjax() ){
            $where = $this->build_params();
            $limit = $this->request->param('limit');
            $data = $this->model->onlyTrashed()
                ->with(['category'])
                ->where($where)->order('id desc')->paginate($limit)->toArray();
            return layui_table_page_data($data);
        }
        return $this->fetch();
    }
}

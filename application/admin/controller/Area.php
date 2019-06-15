<?php

namespace app\admin\controller;

use controller\Backend;

/**
 * 地区表
 */
class Area extends Backend
{

    /**
     * area模型对象
     * @var app\admin\model\Area
     */
    protected $model;
	public $has_del=1;//是否拥有删除功能
	public $has_soft_del=0;//是否拥有软删除功能

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\Area();
        
        

    }

    
    //查看
    public function index(){
        if( $this->request->isAjax() ){
            $where = $this->build_params();
            $select_page = $this->request->param('select_page');
                        $limit = $select_page ? $this->request->param('pageSize') : $this->request->param('limit');
            $data = $this->model
                ->with(['area'])
                ->where($where)->order('id desc')->paginate($limit)->toArray();
            return $select_page ? select_page_data($data) : layui_table_page_data($data);
        }
        return $this->fetch();
    }

    
    //回收站
    public function recycle(){
        if( $this->request->isAjax() ){
            $where = $this->build_params();
            $limit = $this->request->param('limit');
            $data = $this->model->onlyTrashed()
                ->with(['area'])
                ->where($where)->order('id desc')->paginate($limit)->toArray();
            return layui_table_page_data($data);
        }
        return $this->fetch();
    }
}

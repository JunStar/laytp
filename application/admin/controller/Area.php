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

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\Area();
        
        
    }

    
    //查看
    public function index(){
        if( $this->request->isAjax() ){
            $where = $this->build_params();
            $limit = $this->request->param('limit');
            $data = $this->model
                ->with(['parent'])
                ->where($where)->order('id desc')->paginate($limit)->toArray();
            $data['data'] = $this->prettifyList($data['data']);
            return layui_table_page_data($data);
        }
        return $this->fetch();
    }
}

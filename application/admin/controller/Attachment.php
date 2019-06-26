<?php

namespace app\admin\controller;

use controller\Backend;

/**
 * 附件管理
 */
class Attachment extends Backend
{

    /**
     * attachment模型对象
     * @var app\admin\model\Attachment
     */
    protected $model;
	public $has_del=1;//是否拥有删除功能
	public $has_soft_del=1;//是否拥有软删除功能

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\Attachment();
    }

    //弹框选择已经上传的文件
    public function select(){
        if( $this->request->isAjax() ){

            $where = $this->build_params();
            $limit = $this->request->param('limit');
            $data = $this->model->where($where)->order('id desc')->paginate($limit)->toArray();
            return layui_table_page_data($data);
        }
        $assign['id_val'] = $this->request->param('id_val');
        $assign['accept'] = $this->request->param('accept');
        $assign['single_multi'] = $this->request->param('single_multi');
        $this->assign($assign);
        return $this->fetch();
    }
}

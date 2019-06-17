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

    

    
}

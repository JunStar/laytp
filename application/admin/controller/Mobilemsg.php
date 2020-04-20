<?php

namespace app\admin\controller;

use controller\Backend;

/**
 * 手机短信管理
 */
class Mobilemsg extends Backend
{

    /**
     * mobilemsg模型对象
     * @var app\common\model\Mobilemsg
     */
    protected $model;
	public $has_del=1;//是否拥有删除功能
	public $has_soft_del=1;//是否拥有软删除功能

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\common\model\Mobilemsg();
        
    }

    

    
}

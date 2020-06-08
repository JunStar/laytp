<?php

namespace addons\aliyun_mobilemsg\admin\controller;

use controller\AddonsBackend;

/**
 * 手机短信管理
 */
class Mobilemsg extends AddonsBackend
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
        $this->addon = 'aliyun_mobilemsg';
        parent::initialize();
        $this->model = new \addons\aliyun_mobilemsg\admin\model\Mobilemsg();
        
    }

    

    
}

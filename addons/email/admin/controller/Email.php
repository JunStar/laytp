<?php

namespace addons\email\admin\controller;

use controller\AddonsBackend;

/**
 * 邮件管理
 */
class Email extends AddonsBackend
{

    /**
     * email模型对象
     * @var app\common\model\Email
     */
    protected $model;
	public $has_del=1;//是否拥有删除功能
	public $has_soft_del=1;//是否拥有软删除功能

    public function initialize()
    {
        $this->addon = 'email';
        parent::initialize();
        $this->model = new \addons\email\admin\model\Email();
        
    }

    

    
}

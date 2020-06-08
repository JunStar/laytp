<?php

namespace addons\email\admin\controller\email;

use controller\AddonsBackend;

/**
 * 邮件模板管理
 */
class Template extends AddonsBackend
{

    /**
     * email_template模型对象
     * @var app\common\model\email\Template
     */
    protected $model;
	public $has_del=1;//是否拥有删除功能
	public $has_soft_del=1;//是否拥有软删除功能

    public function initialize()
    {
        $this->addon = 'email';
        parent::initialize();
        $this->model = new \addons\email\admin\model\email\Template();
        
    }

    

    
}

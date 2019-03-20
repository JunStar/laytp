<?php

namespace app\admin\controller\auth;

use controller\Backend;

/**
 * 后台管理员表
 */
class User extends Backend
{

    /**
     * admin_user模型对象
     * @var app\admin\model\admin\User
     */
    protected $model;

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\auth\User();


    }
}

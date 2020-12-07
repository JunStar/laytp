<?php

namespace app\admin\controller;

use laytp\controller\Backend;

/**
 * 地区管理
 */
class Area extends Backend
{

    /**
     * area模型对象
     * @var \app\common\model\Area
     */
    protected $model;

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\common\model\Area();

    }


}

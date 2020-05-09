<?php
namespace addons\autocreate\index\controller;

use controller\Addons;

class Index extends Addons
{
    public function index()
    {
        $assign['test_assign'] = 'this is test assign_var';
        $this->assign($assign);
        return $this->fetch();
    }
}

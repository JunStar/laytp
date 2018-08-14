<?php
namespace app\admin\controller;

use controller\BasicAdmin;

class Index extends BasicAdmin
{
    public function index()
    {
        $assign['menus'] = [0,1];
        $this->assign($assign);
        return $this->fetch();
    }
}

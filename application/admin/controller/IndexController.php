<?php
namespace app\admin\controller;

use controller\BasicAdmin;

class IndexController extends BasicAdmin
{
    public function index()
    {
        return $this->fetch();
    }
}

<?php
namespace app\admin\controller;

use controller\BasicAdminController;

class IndexController extends BasicAdminController
{
    public function index(){
        return $this->fetch();
    }
}

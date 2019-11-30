<?php
namespace app\admin\controller;

use controller\Backend;

class Dashboard extends Backend
{
    public function index(){
        return $this->fetch();
    }
}

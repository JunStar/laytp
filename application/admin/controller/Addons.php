<?php
namespace app\admin\controller;

use controller\Backend;

class Addons extends Backend
{
    public function index(){
        return $this->fetch();
    }
}
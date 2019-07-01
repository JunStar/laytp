<?php
namespace app\admin\controller;

use controller\Backend;

class Index extends Backend
{
    public function index(){

        return $this->fetch();
    }
}

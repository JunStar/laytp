<?php
namespace app\admin\controller;

use controller\Backend;

class Sysconf extends Backend
{
    public function index(){
        return $this->fetch();
    }
}
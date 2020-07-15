<?php
namespace app\admin\controller;

use controller\Backend;

class Ltiframe extends Backend
{
    public function index(){
        return $this->fetch();
    }
}

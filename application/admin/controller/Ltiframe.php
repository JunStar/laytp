<?php
namespace app\admin\controller;

use controller\Newbackend;

class Ltiframe extends Backend
{
    public function index(){
        return $this->fetch();
    }
}

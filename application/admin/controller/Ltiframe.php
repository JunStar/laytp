<?php
namespace app\admin\controller;

use controller\Newbackend;

class Ltiframe extends Newbackend
{
    public function index(){
        return $this->fetch();
    }
}

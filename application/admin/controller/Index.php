<?php
namespace app\admin\controller;

use controller\Newbackend;

class Index extends Newbackend
{
    public function index(){
        return $this->fetch();
    }
}

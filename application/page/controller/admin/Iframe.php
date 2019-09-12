<?php
namespace app\page\controller\admin;

use controller\Newbackend;

class Iframe extends Newbackend
{
    public function index()
    {
        return $this->fetch();
    }

    public function test(){
        echo 'test';
    }
}

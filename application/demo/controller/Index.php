<?php
namespace app\demo\controller;

use controller\BasicAdmin;

class Index extends BasicAdmin
{
    public function index()
    {
        return $this->fetch();
    }
}

<?php
namespace app\page\controller\admin;

use think\Controller;

class Index extends Controller
{
    public function index()
    {
        return $this->fetch();
    }
}

<?php
namespace app\admin\controller;

use think\Controller;

class Index extends Controller
{
    public function index()
    {
        $menus = [0,1];
        return $this->fetch('', ['title' => '系统管理', 'menus' => $menus]);
    }
}

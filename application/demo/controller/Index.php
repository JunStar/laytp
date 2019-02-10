<?php
namespace app\demo\controller;

use think\Controller;

class Index extends Controller
{
    //首页
    public function index()
    {
        return $this->fetch();
    }

    //表格
    public function table()
    {
        return $this->fetch();
    }

    //表单
    public function form()
    {
        return $this->fetch();
    }
}

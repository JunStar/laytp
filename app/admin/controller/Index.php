<?php
namespace app\admin\controller;

use laytp\controller\Backend;

class Index extends Backend
{
    /**
     * 后台首页
     * @return string
     * @throws \Exception
     */
    public function index(){
        return redirect('/a/index.html');
    }
}

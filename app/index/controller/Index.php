<?php
namespace app\index\controller;

use laytp\BaseController;

class Index extends BaseController
{
    /**
     * 首页
     * @return \think\response\Redirect
     */
    public function index(){
        return redirect('/a/login.html');
    }
}

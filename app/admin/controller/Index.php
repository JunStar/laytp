<?php
namespace app\admin\controller;

use laytp\BaseController;

class Index extends BaseController
{
    /**
     * 首页
     *  默认的laytp使用http(s)://yourDomain访问时，直接进入后台
     * @return \think\response\Redirect
     */
    public function index(){
        return redirect('/admin/index.html');
    }
}

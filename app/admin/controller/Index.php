<?php
namespace app\admin\controller;

use laytp\controller\Backend;

class Index extends Backend
{
    /**
     * 后台首页
     *  此方法是为了用户在使用http(s)://yourDomain/admin访问时，能自动进入后台
     * @return string
     * @throws \Exception
     */
    public function index(){
        return redirect('/admin/index.html');
    }
}

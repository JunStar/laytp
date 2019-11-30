<?php
namespace app\admin\controller;

use think\Controller;

class Index extends Controller
{
    public function index(){
        return redirect('/admin/dashboard/index/?ref=35');
    }
}

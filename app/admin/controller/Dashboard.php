<?php
namespace app\admin\controller;

use laytp\controller\Backend;

class Dashboard extends Backend
{
    public function index(){
        return 'dashboard/index';
    }
}

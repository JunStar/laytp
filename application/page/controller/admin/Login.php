<?php
namespace app\page\controller\admin;

use think\Controller;

class Login extends Controller
{
    public function index()
    {
        return $this->fetch();
    }
}

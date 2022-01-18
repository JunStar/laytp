<?php
namespace app\controller;

use laytp\BaseController;

class Index extends BaseController
{
    public function index()
    {
        return redirect("/admin/index.html");
    }
}

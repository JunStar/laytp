<?php
namespace app\admin\controller\sysConf;

use controller\Backend;

class Role extends Backend
{
    public function index()
    {
        return $this->fetch();
    }
}
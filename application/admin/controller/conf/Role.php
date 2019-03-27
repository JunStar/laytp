<?php
namespace app\admin\controller\Conf;

use controller\Backend;

class Role extends Backend
{
    public function index()
    {
        return $this->fetch();
    }
}
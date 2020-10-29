<?php

namespace plugin\core\controller;

use laytp\controller\Backend;

class Dashboard extends Backend
{
    public function index()
    {
        return 'dashboard/index';
    }
}

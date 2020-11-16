<?php
namespace addons\autocreate\admin\controller;

use laytp\controller\AddonsBackend;

class Index extends AddonsBackend
{
    public function index()
    {
        return $this->success('success');
    }
}

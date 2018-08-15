<?php
/**
 * 菜单
 */
namespace app\admin\controller\core;

use controller\BasicAdmin;

class Menu extends BasicAdmin
{
    public function index()
    {
        return $this->fetch();
    }
}

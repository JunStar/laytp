<?php
/**
 * 后台菜单模型
 */
namespace app\admin\model\auth;

use model\Backend;

class Role extends Backend
{
    protected $name = 'admin_role';
    protected $autoWriteTimestamp = 'datetime';
}
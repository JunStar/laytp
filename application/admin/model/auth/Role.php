<?php
/**
 * 后台菜单模型
 */
namespace app\admin\model\auth;

use model\Base;

class Role extends Base
{
    protected $name = 'admin_role';
    protected $autoWriteTimestamp = 'datetime';
}
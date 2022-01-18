<?php
/**
 * 角色模型
 */

namespace app\model\admin;

use laytp\BaseModel;
use think\model\concern\SoftDelete;

class Role extends BaseModel
{
    use SoftDelete;

    protected $name = 'admin_role';
}
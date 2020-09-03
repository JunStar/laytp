<?php
/**
 * 角色模型
 */
namespace app\common\model\admin;

use laytp\BaseModel;
use think\model\concern\SoftDelete;

class Role extends BaseModel
{
    protected $name = 'admin_role';

    use SoftDelete;
    protected $defaultSoftDelete = 0;
}
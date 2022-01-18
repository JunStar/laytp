<?php
/**
 * 后台菜单模型
 */

namespace app\model\admin;

use laytp\BaseModel;
use think\model\concern\SoftDelete;

class Menu extends BaseModel
{
    use SoftDelete;

    protected $name = 'admin_menu';
}
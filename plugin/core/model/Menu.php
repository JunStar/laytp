<?php
/**
 * 后台菜单模型
 */

namespace plugin\core\model;

use laytp\BaseModel;
use think\model\concern\SoftDelete;

class Menu extends BaseModel
{
    use SoftDelete;
    protected $defaultSoftDelete = 0;

    protected $name = 'plugin_core_menu';
}
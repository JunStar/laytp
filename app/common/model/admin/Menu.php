<?php
/**
 * 后台菜单模型
 */
namespace app\common\model\admin;

use laytp\BaseModel;
use think\model\concern\SoftDelete;

class Menu extends BaseModel
{
    protected $name = 'admin_menu';

    use SoftDelete;
    protected $defaultSoftDelete = 0;

    protected $append = ['open'];

    public function getOpenAttr(){
        return true;
    }
}
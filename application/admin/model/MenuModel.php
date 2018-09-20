<?php
/**
 * 后台菜单模型
 */
namespace app\admin\model;

use model\BaseAdminModel;

class MenuModel extends BaseAdminModel
{
    public function addData($data){
        return $this->field(true)->insert($data);
    }
}
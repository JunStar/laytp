<?php
/**
 * 后台菜单模型
 */
namespace app\admin\model;

use model\Backend;

class Menu extends Backend
{
    public function addData($data){
        return $this->field(true)->insert($data);
    }
}
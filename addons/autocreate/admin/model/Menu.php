<?php
/**
 * 后台菜单模型
 */
namespace addons\autocreate\admin\model;

use model\Base;

class Menu extends Base
{
    // 表名
    protected $name = 'autocreate_menu';

    public function firstMenu(){
        return $this->belongsTo('app\admin\model\auth\Menu','first_menu_id','id');
    }

    public function secondMenu(){
        return $this->belongsTo('app\admin\model\auth\Menu','second_menu_id','id');
    }
}
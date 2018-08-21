<?php
/**
 * 后台菜单模型
 */
namespace app\admin\model;

use think\Model;

class MenuModel extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = 'sys_menu';

    public function addData($data){
        return $this->field(true)->insert($data);
    }
}
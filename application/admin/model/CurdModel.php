<?php
/**
 * 后台菜单模型
 */
namespace app\admin\model;

use think\Model;

class CurdModel extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = 'sys_curd';

    public function addData($data){
        return $this->field(true)->insert($data);
    }
}
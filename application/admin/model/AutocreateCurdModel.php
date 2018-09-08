<?php
/**
 * 后台菜单模型
 */
namespace app\admin\model;

use think\Model;

class AutocreateCurdModel extends Model
{
    public function addData($data){
        return $this->field(true)->insert($data);
    }

    public function editData($data,$where){
        return $this->field(true)->where($where)->update($data);
    }
}
<?php
namespace app\admin\model;

use think\Model;

class MenuModel extends Model
{
    public function add($data){
        return $this->field(true)->insert($data);
    }
}
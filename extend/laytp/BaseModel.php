<?php
/**
 * 模型基类
 */
namespace laytp;

use think\Model;

class BaseModel extends Model
{
    public $const=[];

    //获取数组常量的函数
    public function getArrayConstList($field_name){
        return array_key_exists($field_name,$this->const) ? $this->const[$field_name] : '';
    }
}
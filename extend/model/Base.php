<?php
/**
 * 模型基类
 * 写此基类的目的，是为了封装success和error两个方法供模型类使用，并可以扩展其他方法
 */
namespace model;

use think\Model;

class Base extends Model
{
    public $const=[];

    //获取数组常量的函数
    public function getArrayConstList($field_name){
        return array_key_exists($field_name,$this->const) ? $this->const[$field_name] : '';
    }

    public function success($msg,$data){
        $result['err_code'] = 0;
        $result['msg'] = $msg;
        $result['data'] = $data;
        return $result;
    }

    public function error($msg){
        $result['err_code'] = 1;
        $result['msg'] = $msg;
        return $result;
    }
}
<?php
/**
 * 后台模型基类
 * 写此基类的目的，是为了封装success和error两个方法供模型类使用，并可以扩展其他方法
 */
namespace model;

use think\Model;

class Backend extends Model
{
    public $const=[];

    //获取数组常量的函数
    public function getArrayConstList($field_name){
        return $this->const[$field_name];
    }

    public function success($msg,$data){
        $result['code'] = 1;
        $result['msg'] = $msg;
        $result['data'] = $data;
        return $result;
    }

    public function error($msg){
        $result['code'] = 0;
        $result['msg'] = $msg;
        return $result;
    }
}
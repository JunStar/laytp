<?php
/**
 * 后台菜单模型
 */
namespace model;

use think\Model;

class BaseAdmin extends Model
{
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